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

namespace NavBundle\Bridge\Pagerfanta;

use NavBundle\Bridge\Pagerfanta\Adapter\NavAdapter;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavPagerFanta extends Pagerfanta
{
    private $bookmarkKey;

    public function __construct(AdapterInterface $adapter)
    {
        if (!$adapter instanceof NavAdapter) {
            throw new \InvalidArgumentException(__CLASS__.' only accepts '.NavAdapter::class.' argument');
        }

        parent::__construct($adapter);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPageResults(): \Traversable
    {
        return $this->getAdapter()->getSlice($this->bookmarkKey, $this->getMaxPerPage());
    }

    /**
     * {@inheritdoc}
     */
    public function hasNextPage(): bool
    {
        /** @var NavAdapter $adapter */
        $adapter = $this->getAdapter();

        return null !== $adapter->getBookmarkKey();
    }

    /**
     * @codeCoverageIgnore
     */
    public function setBookmarkKey(?string $bookmarkKey): void
    {
        $this->bookmarkKey = $bookmarkKey;
    }
}
