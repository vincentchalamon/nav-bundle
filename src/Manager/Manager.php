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

namespace NavBundle\Manager;

use matejsvajger\NTLMSoap\Client;
use matejsvajger\NTLMSoap\Common\NTLMConfig;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Exception\EntityNotFoundException;
use NavBundle\Exception\EntityNotManagedException;
use NavBundle\Repository\RepositoryInterface;
use NavBundle\Serializer\ObjectDecoder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class Manager implements ManagerInterface
{
    private $classMetadata;
    private $serializer;
    private $propertyAccessor;
    private $repositories;
    private $clients;
    private $wsdl;
    private $options;
    private $soapOptions;
    private $cachedObjects = [];

    public function __construct(
        ClassMetadataInterface $classMetadata,
        SerializerInterface $serializer,
        PropertyAccessorInterface $propertyAccessor,
        \Traversable $repositories,
        \Traversable $clients,
        string $wsdl,
        array $options,
        array $soapOptions
    ) {
        $this->classMetadata = $classMetadata;
        $this->serializer = $serializer;
        $this->propertyAccessor = $propertyAccessor;
        $this->repositories = iterator_to_array($repositories);
        $this->clients = iterator_to_array($clients);
        $this->wsdl = $wsdl;
        $this->options = $options;
        $this->soapOptions = $soapOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function hasClass(string $class): bool
    {
        try {
            $this->classMetadata->getClassMetadataInfo($class);

            return true;
        } catch (EntityNotFoundException $exception) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata(): ClassMetadataInterface
    {
        return $this->classMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient(string $class): Client
    {
        if (!isset($this->clients[$class])) {
            $this->clients[$class] = new Client(
                $this->wsdl.$this->classMetadata->getClassMetadataInfo($class)->getNamespace(),
                new NTLMConfig($this->options),
                $this->soapOptions
            );
        }

        return $this->clients[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(string $class): RepositoryInterface
    {
        if (!$this->hasClass($class)) {
            throw new EntityNotFoundException();
        }

        $repositoryClass = $this->classMetadata->getClassMetadataInfo($class)->getRepositoryClass();
        if (!isset($this->repositories[$repositoryClass])) {
            $this->repositories[$repositoryClass] = new $repositoryClass($this, $class);
        }

        return $this->repositories[$repositoryClass];
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(object &$entity): void
    {
        $class = \get_class($entity);
        $classMetadataInfo = $this->classMetadata->getClassMetadataInfo($class);
        $id = $this->propertyAccessor->getValue($entity, $classMetadataInfo->getIdentifier());
        if (!$id || !isset($this->cachedObjects[$class][$id])) {
            throw new EntityNotManagedException();
        }

        unset($this->cachedObjects[$class][$id]);
        $entity = $this->find($class, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $class, string $id)
    {
        if (!isset($this->cachedObjects[$class][$id])) {
            $this->cachedObjects[$class][$id] = $this->serializer->deserialize($this->getClient($class)->Read([
                'No' => $id,
            ]), $class, ObjectDecoder::FORMAT);
        }

        return $this->cachedObjects[$class][$id];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(string $class)
    {
        return $this->findBy($class);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(string $class, array $criteria = [], int $size = 0)
    {
        // todo Store deserialized objects in cache
        return $this->serializer->deserialize($this->getClient($class)->ReadMultiple([
            'filter' => $criteria,
            'size' => $size,
        ]), $class.'[]', ObjectDecoder::FORMAT);
    }
}
