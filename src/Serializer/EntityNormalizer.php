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

namespace NavBundle\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\Collection\ExtraLazyCollection;
use NavBundle\Collection\LazyCollection;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\Exception\EntityNotFoundException;
use NavBundle\RegistryInterface;
use NavBundle\Util\ClassUtils;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityNormalizer extends AbstractObjectNormalizer
{
    private $registry;
    private $holderFactory;

    public function __construct(
        RegistryInterface $registry,
        LazyLoadingValueHolderFactory $holderFactory,
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = []
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyTypeExtractor, $classDiscriminatorResolver, $objectClassResolver, $defaultContext);

        $this->registry = $registry;
        $this->holderFactory = $holderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = []): LazyLoadingInterface
    {
        if (\is_string($data) && class_exists($type) && ($manager = $this->registry->getManagerForClass($type))) {
            return $this->holderFactory->createProxy($type, function (
                &$wrappedObject,
                LazyLoadingInterface $proxy,
                $method,
                array $parameters,
                &$initializer
            ) use ($data, $type, $manager): void {
                $initializer = null;

                $object = $manager->getRepository($type)->find($data);
                if (!$object) {
                    throw new EntityNotFoundException("Entity of class '$type' with identifier '$data' not found.");
                }

                $wrappedObject = $object;
            });
        }

        return $this->holderFactory->createProxy($type, function (
            &$wrappedObject,
            LazyLoadingInterface $proxy,
            $method,
            array $parameters,
            &$initializer
        ) use ($data, $type, $format, $context): bool {
            $initializer = null;
            $wrappedObject = parent::denormalize($data, $type, $format, $context);

            /** @var EntityManagerInterface $manager */
            $manager = $this->registry->getManagerForClass($type);
            /** @var ClassMetadata $classMetadata */
            $classMetadata = $manager->getClassMetadata($type);

            foreach ($classMetadata->getAssociationNames() as $associationName) {
                if ($classMetadata->isSingleValuedAssociation($associationName)) {
                    // Value should have been set from $data.
                    continue;
                }

                try {
                    $value = $classMetadata->reflFields[$associationName]->getValue($wrappedObject);
                } catch (\ErrorException $exception) {
                    /* @see https://github.com/Ocramius/ProxyManager/pull/299 */
                    $value = \call_user_func([$wrappedObject, 'get'.ucfirst($associationName)]);
                }

                switch ($classMetadata->getAssociationFetchMode($associationName)) {
                    case ClassMetadata::FETCH_EAGER:
                        $targetClass = $classMetadata->getAssociationTargetClass($associationName);
                        $value = $this->registry->getManagerForClass($targetClass)->getRepository($targetClass)->findBy([
                            $classMetadata->getAssociationMappedByTargetField($associationName) => $classMetadata->getIdentifierValue($wrappedObject),
                        ]);
                        break;
                    case ClassMetadata::FETCH_EXTRA_LAZY:
                        $value = new ExtraLazyCollection($this->registry, $value ?: new ArrayCollection(), $associationName, $wrappedObject);
                        break;
                    default:
                    case ClassMetadata::FETCH_LAZY:
                        $value = new LazyCollection($this->registry, $value ?: new ArrayCollection(), $associationName, $wrappedObject);
                        break;
                }
                try {
                    $classMetadata->reflFields[$associationName]->setValue($wrappedObject, $value);
                } catch (\ErrorException $exception) {
                    /* @see https://github.com/Ocramius/ProxyManager/pull/299 */
                    \call_user_func([$wrappedObject, 'set'.ucfirst($associationName)], $value);
                }
            }
            $manager->getUnitOfWork()->addToIdentityMap($wrappedObject);

            return true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return parent::supportsDenormalization($data, $type, $format)
            && NavDecoder::FORMAT === $format
            && null !== $this->registry->getManagerForClass($type);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return parent::supportsNormalization($data, $format)
            && NavDecoder::FORMAT === $format
            && null !== $this->registry->getManagerForClass(ClassUtils::getRealClass($data));
    }

    /**
     * {@inheritdoc}
     */
    protected function extractAttributes($object, $format = null, array $context = []): array
    {
        $className = ClassUtils::getRealClass($object);
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        $properties = array_merge($classMetadata->getFieldNames(), $classMetadata->getAssociationNames());

        return !empty($context['properties']) ? array_intersect($properties, $context['properties']) : $properties;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeValue($object, $attribute, $format = null, array $context = [])
    {
        $className = ClassUtils::getRealClass($object);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);

        try {
            return $classMetadata->reflFields[$attribute]->getValue($object);
        } catch (\ErrorException $exception) {
            /* @see https://github.com/Ocramius/ProxyManager/pull/299 */
            return \call_user_func([$object, 'get'.ucfirst($attribute)]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = []): void
    {
        $className = ClassUtils::getRealClass($object);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        try {
            $classMetadata->reflFields[$attribute]->setValue($object, $value);
        } catch (\ErrorException $exception) {
            /* @see https://github.com/Ocramius/ProxyManager/pull/299 */
            \call_user_func([$object, 'set'.ucfirst($attribute)], $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function isAllowedAttribute($classOrObject, $attribute, $format = null, array $context = []): bool
    {
        $className = ClassUtils::getRealClass($classOrObject);

        return parent::isAllowedAttribute($classOrObject, $attribute, $format, $context) && (
                $this->registry->getManagerForClass($className)->getClassMetadata($className)->hasField($attribute) ||
                $this->registry->getManagerForClass($className)->getClassMetadata($className)->hasAssociation($attribute)
            );
    }
}
