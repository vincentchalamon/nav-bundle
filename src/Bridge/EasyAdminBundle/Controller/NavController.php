<?php

/*
 * This file is part of the NavBundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace NavBundle\Bridge\EasyAdminBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use NavBundle\Bridge\EasyAdminBundle\Event\EasyAdminEvents as NavEasyAdminEvents;
use NavBundle\Bridge\EasyAdminBundle\Form\Filter\FilterRegistry;
use NavBundle\Bridge\EasyAdminBundle\Search\Paginator;
use NavBundle\Bridge\EasyAdminBundle\Search\RequestBuilder;
use NavBundle\Bridge\Pagerfanta\Adapter\NavAdapter;
use NavBundle\Bridge\Pagerfanta\NavPagerFanta;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\Util\UrlUtils;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class NavController extends EasyAdminController
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Paginator::class => Paginator::class,
            RequestBuilder::class => RequestBuilder::class,
            FilterRegistry::class => FilterRegistry::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function listAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll(
            $this->entity['class'],
            $this->request->query->getInt('page', 1),
            $this->entity['list']['max_results'],
            $this->request->query->get('sortField'),
            $this->request->query->get('sortDirection'),
            $this->entity['list']['nav_filter'] ?? [],
            $this->request->query->get('bookmarkKey')
        );

        $this->dispatch(EasyAdminEvents::POST_LIST, ['paginator' => $paginator]);

        $parameters = [
            'paginator' => $paginator,
            'fields' => $fields,
            'batch_form' => $this->createBatchForm($this->entity['name'])->createView(),
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        ];

        if ($this->request->isXmlHttpRequest()) {
            return $this->executeDynamicMethod('render<EntityName>XHR', ['list', $this->entity['templates']['xhr'], $parameters]);
        }

        return $this->executeDynamicMethod('render<EntityName>Template', ['list', $this->entity['templates']['list'], $parameters]);
    }

    /**
     * {@inheritdoc}
     */
    protected function searchAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_SEARCH);

        $query = trim($this->request->query->get('query'));
        // if the search query is empty, redirect to the 'list' action
        if ('' === $query) {
            $queryParameters = array_replace($this->request->query->all(), ['action' => 'list']);
            unset($queryParameters['query']);

            return $this->redirect($this->get('router')->generate('easyadmin', $queryParameters));
        }

        $searchableFields = $this->entity['search']['fields'];
        $paginator = $this->findBy(
            $this->entity['class'],
            $query,
            $searchableFields,
            $this->request->query->getInt('page', 1),
            $this->entity['list']['max_results'],
            $this->request->query->get('sortField'),
            $this->request->query->get('sortDirection'),
            $this->entity['list']['nav_filter'] ?? [],
            $this->request->query->get('bookmarkKey')
        );
        $fields = $this->entity['list']['fields'];

        $this->dispatch(EasyAdminEvents::POST_SEARCH, [
            'fields' => $fields,
            'paginator' => $paginator,
        ]);

        $parameters = [
            'paginator' => $paginator,
            'fields' => $fields,
            'batch_form' => $this->createBatchForm($this->entity['name'])->createView(),
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['search', $this->entity['templates']['list'], $parameters]);
    }

    /**
     * {@inheritdoc}
     */
    protected function findAll($entityClass, $page = 1, $maxPerPage = Paginator::MAX_ITEMS, $sortField = null, $sortDirection = null, $navFilter = null, $bookmarkKey = null): NavPagerfanta
    {
        $requestBuilder = $this->executeDynamicMethod('create<EntityName>ListRequestBuilder', [$entityClass, $navFilter]);

        $this->filterRequestBuilder($requestBuilder);

        $this->dispatch(NavEasyAdminEvents::POST_LIST_REQUEST_BUILDER, [
            'request_builder' => $requestBuilder,
        ]);

        return $this->get(Paginator::class)->createNavPaginator($requestBuilder, $bookmarkKey, $maxPerPage);
    }

    /**
     * {@inheritdoc}
     */
    protected function findBy($entityClass, $searchQuery, array $searchableFields, $page = 1, $maxPerPage = Paginator::MAX_ITEMS, $sortField = null, $sortDirection = null, $navFilter = null, $bookmarkKey = null): NavPagerfanta
    {
        $requestBuilder = $this->executeDynamicMethod('create<EntityName>SearchRequestBuilder', [$entityClass, $searchQuery, $searchableFields, $navFilter]);

        $this->filterRequestBuilder($requestBuilder);

        $this->dispatch(NavEasyAdminEvents::POST_SEARCH_REQUEST_BUILDER, [
            'request_builder' => $requestBuilder,
            'search_query' => $searchQuery,
            'searchable_fields' => $searchableFields,
        ]);

        return $this->get(Paginator::class)->createNavPaginator($requestBuilder, $bookmarkKey, $maxPerPage);
    }

    /**
     * {@inheritdoc}
     */
    protected function filterRequestBuilder(RequestBuilderInterface $requestBuilder): void
    {
        /** @var array|null $requestData */
        $requestData = $this->request->query->get('filters');
        if (!$requestData) {
            // Don't create the filters form if there is no filter applied
            return;
        }

        /** @var Form $filtersForm */
        $filtersForm = $this->createFiltersForm($this->entity['name']);
        $filtersForm->handleRequest($this->request);
        if (!$filtersForm->isSubmitted()) {
            return;
        }

        /** @var FilterRegistry $filterRegistry */
        $filterRegistry = $this->get(FilterRegistry::class);

        $appliedFilters = [];
        foreach ($filtersForm as $filterForm) {
            $name = $filterForm->getName();
            if (!isset($requestData[$name])) {
                // this filter is not applied
                continue;
            }

            // if the form filter is not valid then
            // we should not apply the filter
            if (!$filterForm->isValid()) {
                continue;
            }

            // resolve the filter type related to this form field
            $filterType = $filterRegistry->resolveType($filterForm);

            $metadata = $this->entity['list']['filters'][$name] ?? [];
            if (false !== $filterType->filter($requestBuilder, $filterForm, $metadata)) {
                $appliedFilters[] = $name;
            }
        }

        $easyadmin = $this->request->attributes->get('easyadmin');
        $easyadmin['filters']['applied'] = $appliedFilters;
        $this->request->attributes->set('easyadmin', $easyadmin);
    }

    protected function createListRequestBuilder(string $entityClass, array $navFilter = []): RequestBuilderInterface
    {
        return $this->get(RequestBuilder::class)->createListRequestBuilder($this->entity, $navFilter);
    }

    protected function createSearchRequestBuilder(string $entityClass, string $searchQuery, array $searchableFields, array $navFilter = []): RequestBuilderInterface
    {
        return $this->get(RequestBuilder::class)->createSearchRequestBuilder($this->entity, $searchQuery, $navFilter);
    }

    protected function renderXHR(string $actionName, string $templatePath, array $parameters = []): JsonResponse
    {
        // renderView MUST be called first, otherwise the bookmarkKey is not generated.
        $data = ['html' => $this->renderView($templatePath, $parameters)];

        $parts = parse_url($this->request->getUri());
        parse_str($parts['query'], $query);
        unset($query['bookmarkKey']);
        $parts['query'] = http_build_query($query);

        /** @var NavPagerFanta $paginator */
        $paginator = $parameters['paginator'];
        /** @var NavAdapter $adapter */
        $adapter = $paginator->getAdapter();
        // bookmarkKey must not be included in the http_build_query because of its special characters.
        if ($paginator->hasNextPage()) {
            $parts['query'] .= '&bookmarkKey='.$adapter->getBookmarkKey();
            $data['nextUrl'] = UrlUtils::build($parts);
        } else {
            $data['nextUrl'] = '#';
        }

        return new JsonResponse($data);
    }
}
