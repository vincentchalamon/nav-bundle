<?php

declare(strict_types=1);

namespace NavBundle\Bridge\Pagerfanta;

use NavBundle\Bridge\Pagerfanta\Adapter\NavAdapter;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
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
    public function getCurrentPageResults()
    {
        return $this->getAdapter()->getSlice($this->bookmarkKey, $this->getMaxPerPage());
    }

    /**
     * {@inheritdoc}
     */
    public function hasNextPage()
    {
        return null !== $this->getAdapter()->getBookmarkKey();
    }

    public function setBookmarkKey(?string $bookmarkKey): void
    {
        $this->bookmarkKey = $bookmarkKey;
    }
}