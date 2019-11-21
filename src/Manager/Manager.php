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
use NavBundle\Repository\RepositoryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Manager implements ManagerInterface
{
    private $classMetadata;
    private $repositories;
    private $wsdl;
    private $soapOptions;

    public function __construct(
        ClassMetadataInterface $classMetadata,
        \Traversable $repositories,
        string $wsdl,
        array $soapOptions
    ) {
        $this->classMetadata = $classMetadata;
        $this->repositories = iterator_to_array($repositories);
        $this->wsdl = $wsdl;
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
    public function getRepository(string $class): RepositoryInterface
    {
        if (!$this->hasClass($class)) {
            throw new EntityNotFoundException();
        }

        $classMetadataInfo = $this->classMetadata->getClassMetadataInfo($class);
        $repositoryClass = $classMetadataInfo->getRepositoryClass();
        if (!isset($this->repositories[$repositoryClass])) {
            $this->repositories[$repositoryClass] = new $repositoryClass(
                $this,
                new Client($this->wsdl.$classMetadataInfo->getNamespace(), new NTLMConfig($this->soapOptions))
            );
        }

        return $this->repositories[$repositoryClass];
    }

    /**
     * {@inheritdoc}
     */
    public function find(\SoapClient $client, string $no)
    {
        return $client->Read([
            'No' => $no,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(\SoapClient $client)
    {
        return $this->findBy($client);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(\SoapClient $client, array $criteria = [], int $size = 0)
    {
        return $client->ReadMultiple([
            'filter' => $criteria,
            'size' => $size,
        ]);
    }
}
