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

namespace NavBundle\Bridge\Pagerfanta\Adapter;

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class NavAdapter implements AdapterInterface
{
    private $requestBuilder;
    private $classMetadata;

    private $bookmarkKey;

    public function __construct(RequestBuilderInterface $requestBuilder, ClassMetadataInterface $classMetadata)
    {
        $this->requestBuilder = $requestBuilder;
        $this->classMetadata = $classMetadata;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getNbResults()
    {
        return $this->requestBuilder->count();
    }

    /**
     * Returns an slice of the results.
     *
     * @param string $bookmarkKey the bookmarkKey
     * @param int    $size        the size
     *
     * @return \Traversable the slice
     */
    public function getSlice($bookmarkKey, $size): \Traversable
    {
        $this->bookmarkKey = null;

        /** @var \ArrayIterator $iterator */
        $iterator = $this->requestBuilder
            ->copy()
            ->setBookmarkKey($bookmarkKey)
            ->setSize($size)
            ->getResult();

        $count = $iterator->count();
        if ($size === $count) {
            // There is potentially a next page
            $iterator->seek($count - 1);
            if ($last = $iterator->current()) {
                $this->bookmarkKey = $this->classMetadata->getKeyValue($last);
            }
            $iterator->rewind();
        }

        return $iterator;
    }

    public function getBookmarkKey(): ?string
    {
        return $this->bookmarkKey;
    }
}
