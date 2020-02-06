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

namespace NavBundle\Bridge\EasyAdminBundle\Search;

use NavBundle\Bridge\Pagerfanta\Adapter\NavAdapter;
use NavBundle\Bridge\Pagerfanta\NavPagerFanta;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\RegistryInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Pagerfanta\Pagerfanta;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
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
     * @param string $bookmarkKey
     *
     * @return Pagerfanta
     */
    public function createNavPaginator(RequestBuilderInterface $requestBuilder, string $bookmarkKey = null, int $size = self::MAX_ITEMS)
    {
        $className = $requestBuilder->getClassName();
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass($className);

        $paginator = new NavPagerFanta(new NavAdapter(
            $requestBuilder,
            $em->getClassMetadata($className)
        ));
        $paginator->setMaxPerPage($size);
        $paginator->setBookmarkKey($bookmarkKey);

        return $paginator;
    }
}
