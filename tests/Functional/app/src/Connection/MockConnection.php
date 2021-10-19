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

namespace NavBundle\App\Connection;

use NavBundle\Connection\Connection;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class MockConnection extends Connection
{
    private const RECORD = false;
    private const MOCK_DIRECTORY = __DIR__.'/../../mock';

    public function __construct(string $wsdl, array $options = null)
    {
        parent::__construct($wsdl, ['cache_dir' => self::MOCK_DIRECTORY.'/wsdl'] + $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchWSDL(string $wsdl, bool $force = false): string
    {
        $filename = sprintf('%s/wsdl/%s.wsdl', self::MOCK_DIRECTORY, $this->getNamespace());

        if (!self::RECORD) {
            return $filename;
        }

        if (!is_dir(pathinfo($filename, \PATHINFO_DIRNAME))) {
            mkdir(pathinfo($filename, \PATHINFO_DIRNAME), 0777, true);
        }

        $wsdl = parent::fetchWSDL($wsdl, true);
        copy($wsdl, $filename);

        return $wsdl;
    }

    /**
     * {@inheritdoc}
     */
    public function ReadMultiple(array $criteria): object
    {
        $filename = sprintf('%s/responses/%s/%s.xml', self::MOCK_DIRECTORY, $this->getNamespace(), sha1(__FUNCTION__.serialize($criteria)));

        if (self::RECORD) {
            if (!is_dir(pathinfo($filename, \PATHINFO_DIRNAME))) {
                mkdir(pathinfo($filename, \PATHINFO_DIRNAME), 0777, true);
            }

            $response = parent::ReadMultiple($criteria);
            file_put_contents($filename, $this->__getLastResponse());

            return $response;
        }

        $response = ((object) (array) simplexml_load_file($filename)->xpath('/Soap:Envelope/Soap:Body')[0])->ReadMultiple_Result;
        $response = (object) get_object_vars($response);
        $response->ReadMultiple_Result = (object) get_object_vars($response->ReadMultiple_Result);

        if (!\is_array($response->ReadMultiple_Result->{$this->getNamespace()})) {
            $response->ReadMultiple_Result->{$this->getNamespace()} = (object) (array) $response->ReadMultiple_Result->{$this->getNamespace()};
            foreach (get_object_vars($response->ReadMultiple_Result->{$this->getNamespace()}) as $property => $value) {
                if (\is_string($value) && preg_match('/^(true|false)$/i', $value)) {
                    $response->ReadMultiple_Result->{$this->getNamespace()}->{$property} = filter_var($value, \FILTER_VALIDATE_BOOLEAN);
                }
            }

            return $response;
        }

        foreach ($response->ReadMultiple_Result->{$this->getNamespace()} as $key => $value) {
            $response->ReadMultiple_Result->{$this->getNamespace()}[$key] = (object) get_object_vars($value);
            foreach (get_object_vars($value) as $property => $datum) {
                if (\is_string($datum) && preg_match('/^(true|false)$/i', $datum)) {
                    $response->ReadMultiple_Result->{$this->getNamespace()}[$key]->{$property} = filter_var($datum, \FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function Read(array $criteria): object
    {
        return $this->getResponse(__FUNCTION__, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function Create(array $criteria): object
    {
        return $this->getResponse(__FUNCTION__, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function Update(array $criteria): object
    {
        return $this->getResponse(__FUNCTION__, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function Delete(array $criteria): object
    {
        return $this->getResponse(__FUNCTION__, $criteria);
    }

    private function getResponse(string $method, array $criteria): object
    {
        $filename = sprintf('%s/responses/%s/%s.xml', self::MOCK_DIRECTORY, $this->getNamespace(), sha1($method.serialize($criteria)));

        if (self::RECORD) {
            if (!is_dir(pathinfo($filename, \PATHINFO_DIRNAME))) {
                mkdir(pathinfo($filename, \PATHINFO_DIRNAME), 0777, true);
            }

            $response = parent::$method($criteria);
            file_put_contents($filename, $this->__getLastResponse());

            return $response;
        }

        $response = ((object) (array) simplexml_load_file($filename)->xpath('/Soap:Envelope/Soap:Body')[0])->{$method.'_Result'};
        $response = (object) get_object_vars($response);
        $response->{$this->getNamespace()} = (object) get_object_vars($response->{$this->getNamespace()});

        foreach (get_object_vars($response->{$this->getNamespace()}) as $property => $value) {
            if (\is_string($value) && preg_match('/^(true|false)$/i', $value)) {
                $response->{$this->getNamespace()}->{$property} = filter_var($value, \FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $response;
    }

    private function getNamespace(): string
    {
        return preg_replace('/^.*\/(.*)$/', '$1', $this->wsdl);
    }
}
