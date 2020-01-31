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

namespace NavBundle\RequestBuilder;

use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\Event\PostLoadEvent;
use NavBundle\Exception\FieldNotFoundException;
use NavBundle\Hydrator\CountHydrator;
use NavBundle\PropertyInfo\Types;
use Symfony\Component\PropertyInfo\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class RequestBuilder implements RequestBuilderInterface
{
    private $em;
    private $className;
    private $filters = [];
    private $offset;
    private $limit;

    public function __construct(EntityManagerInterface $em, string $className)
    {
        $this->em = $em;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function where($field, $predicate)
    {
        $this->filters = [];

        return $this->andWhere($field, $predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function andWhere($field, $predicate)
    {
        $this->filters[$field] = $predicate;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBookmarkKey($firstResult)
    {
        $this->offset = $firstResult;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBookmarkKey()
    {
        return $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    public function setSize($maxResults)
    {
        $this->limit = $maxResults;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function loadById($identifier): ?object
    {
        try {
            $response = $this->em->getConnection($this->className)->Read([
                'No' => $identifier,
            ]);
        } catch (\SoapFault $fault) {
            $this->em->getLogger()->critical($fault->getMessage());

            throw $fault;
        }

        if (empty($response)) {
            return null;
        }

        $object = $this->em->getHydrator()->hydrateAll($response, $this->em->getClassMetadata($this->className));
        $this->em->getEventManager()->dispatch(new PostLoadEvent($object, $this->em));

        return $object;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function getOneOrNullResult(string $hydrator = null): ?object
    {
        $this->setSize(1);

        foreach ($this->getResult($hydrator) as $object) {
            $this->em->getEventManager()->dispatch(new PostLoadEvent($object, $this->em));

            return $object;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function count()
    {
        return $this->getResult(CountHydrator::class);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function getResult(string $hydrator = null)
    {
        try {
            $response = $this->em->getConnection($this->className)->ReadMultiple($this->computeFilters());
        } catch (\SoapFault $fault) {
            $this->em->getLogger()->critical($fault->getMessage());

            throw $fault;
        }

        if (empty($response)) {
            return new \ArrayIterator();
        }

        $result = $this->em->getHydrator($hydrator)->hydrateAll($response, $this->em->getClassMetadata($this->className));

        if (is_a($hydrator, CountHydrator::class, true)) {
            return $result;
        }

        foreach ($result as $object) {
            $this->em->getEventManager()->dispatch(new PostLoadEvent($object, $this->em));
        }

        return $result;
    }

    private function computeFilters(): array
    {
        $criteria = [
            'filter' => [],
            'setSize' => $this->limit,
        ];

        if (null !== $this->offset) {
            $criteria['bookmarkKey'] = $this->offset;
        }

        $classMetadata = $this->em->getClassMetadata($this->className);
        foreach ($this->filters as $field => $value) {
            if ($classMetadata->hasField($field)) {
                $value = $this->formatValue($classMetadata->getTypeOfField($field), $value);
                $field = $classMetadata->getFieldColumnName($field);
            } elseif ($classMetadata->hasAssociation($field)) {
                throw new \InvalidArgumentException('Find by association is not supported yet.');
            } else {
                throw new FieldNotFoundException("Field name expected, '$field' is not a field nor an association.");
            }

            $criteria['filter'][] = [
                'Field' => $field,
                'Criteria' => $value,
            ];
        }

        return $criteria;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    private function formatValue($type, $value): string
    {
        switch ($type) {
            case Types::DATE:
            case Types::DATE_IMMUTABLE:
                return $value instanceof \DateTime ? $value->format('m-d-Y') : $value;
            case Types::DATETIME:
            case Types::DATETIMEZ:
            case Types::DATETIME_IMMUTABLE:
            case Types::DATETIMEZ_IMMUTABLE:
                return $value instanceof \DateTime ? $value->format('m-d-Y H:i:s') : $value;
            case Types::TIME:
            case Types::TIME_IMMUTABLE:
                return $value instanceof \DateTime ? $value->format('H:i:s') : $value;
            case Types::ARRAY:
                return is_array($value) ? implode('|', array_map([$this, 'formatValue'], $value)) : $value;
            default:
                return $value;
        }
    }
}
