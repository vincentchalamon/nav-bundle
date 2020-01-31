<?php

declare(strict_types=1);

namespace NavBundle\Bridge\Pagerfanta\Adapter;

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class NavAdapter implements AdapterInterface
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
     */
    public function getNbResults()
    {
        return $this->requestBuilder->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($bookmarkKey, $size)
    {
        $this->bookmarkKey = null;

        /** @var \ArrayIterator $iterator */
        $iterator = (clone $this->requestBuilder)
            ->setBookmarkKey($bookmarkKey)
            ->setSize($size)
            ->getResult();

        $count = iterator_count($iterator);
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