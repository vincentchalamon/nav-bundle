<?php

declare(strict_types=1);

namespace NavBundle\Bridge\EasyAdminBundle\Search;

use NavBundle\Bridge\Pagerfanta\Adapter\NavAdapter;
use NavBundle\Bridge\Pagerfanta\NavPagerFanta;
use NavBundle\RegistryInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Pagerfanta\Pagerfanta;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class Paginator
{
    public const MAX_ITEMS = 15;

    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Creates a paginator for the given request builder.
     *
     * @param RequestBuilderInterface $requestBuilder
     * @param string                  $bookmarkKey
     * @param int                     $size
     *
     * @return Pagerfanta
     */
    public function createNavPaginator(RequestBuilderInterface $requestBuilder, string $bookmarkKey = null, int $size = self::MAX_ITEMS)
    {
        $className = $requestBuilder->getClassName();

        $paginator = new NavPagerFanta(new NavAdapter(
            $requestBuilder,
            $this->registry->getManagerForClass($className)->getClassMetadata($className)
        ));
        $paginator->setMaxPerPage($size);
        $paginator->setBookmarkKey($bookmarkKey);

        return $paginator;
    }
}
