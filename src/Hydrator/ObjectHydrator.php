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

namespace NavBundle\Hydrator;

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Serializer\EntityNormalizer;
use NavBundle\Serializer\NavDecoder;
use NavBundle\Serializer\ObjectDenormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ObjectHydrator implements HydratorInterface
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrateAll($response, ClassMetadataInterface $classMetadata, array $context = [])
    {
        return $this->serializer->deserialize(serialize($response), $classMetadata->getName(), NavDecoder::FORMAT, $context + [
            ObjectDenormalizer::NAMESPACE => $classMetadata->getNamespace(),
            EntityNormalizer::ENABLE_MAX_DEPTH => true,
        ]);
    }
}
