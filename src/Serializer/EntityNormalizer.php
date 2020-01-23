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

use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\RegistryInterface;
use NavBundle\Util\ClassUtils;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * TODO: Implement proxy from ocramius/proxy-manager.
 */
final class EntityNormalizer extends AbstractObjectNormalizer
{
    private $registry;

    public function __construct(
        RegistryInterface $registry,
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = []
    )
    {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyTypeExtractor, $classDiscriminatorResolver, $objectClassResolver, $defaultContext);

        $this->registry = $registry;
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function denormalize($data, $type, $format = null, array $context = [])
//    {
//        /** @var ClassMetadata $classMetadata */
//        $classMetadata = $this->registry->getManagerForClass($type)->getClassMetadata($type);
//
//        $object = $context['object_to_populate'] ?? (new LazyLoadingGhostFactory())->createProxy($type, function (
//            GhostObjectInterface $ghostObject,
//            string $method,
//            array $parameters,
//            &$initializer,
//            array $properties
//        ) use ($data, $classMetadata, $type, $format) {
//            $initializer = null;
//
//            foreach ($data as $key => $value) {
//                try {
//                    $property = $classMetadata->retrieveField($key);
//                } catch (FieldNotFoundException $exception) {
//                    // Key does not match any property
//                    unset($data[$key]);
//                    continue;
//                }
//
//                $properties[$property] = $value;
//            }
//
//            return true;
//        }, ['skippedProperties' => [$classMetadata->getIdentifier(), $classMetadata->getKey()]]);
//
//        $classMetadata->reflFields[$classMetadata->getIdentifier()]->setValue($object, $data['No']);
//        $classMetadata->reflFields[$classMetadata->getKey()]->setValue($object, $data['Key']);
//
//        return $object;
//    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (is_string($data) && class_exists($type) && ($manager = $this->registry->getManagerForClass($type))) {
            return $manager->getRepository($type)->find($data);
        }

        return parent::denormalize($data, $type, $format, $context);
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
    public function supportsNormalization($data, $format = null)
    {
        return parent::supportsNormalization($data, $format)
            && NavDecoder::FORMAT === $format
            && null !== $this->registry->getManagerForClass(ClassUtils::getRealClass($data));
    }

    /**
     * {@inheritdoc}
     */
    protected function extractAttributes($object, $format = null, array $context = [])
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

        return $classMetadata->reflFields[$attribute]->getValue($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = []): void
    {
        $className = ClassUtils::getRealClass($object);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        $classMetadata->reflFields[$attribute]->setValue($object, $value);
    }

    protected function isAllowedAttribute($classOrObject, $attribute, $format = null, array $context = [])
    {
        $className = ClassUtils::getRealClass($classOrObject);

        return parent::isAllowedAttribute($classOrObject, $attribute, $format, $context) && (
                $this->registry->getManagerForClass($className)->getClassMetadata($className)->hasField($attribute) ||
                $this->registry->getManagerForClass($className)->getClassMetadata($className)->hasAssociation($attribute)
            );
    }
}
