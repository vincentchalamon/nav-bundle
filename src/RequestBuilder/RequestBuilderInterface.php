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

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface RequestBuilderInterface
{
    /**
     * Specifies one or more restrictions to the request result.
     * Replaces any previously specified restrictions, if any.
     *
     * @see https://docs.microsoft.com/en-us/previous-versions/dynamicsnav-2016/hh879066(v=nav.90)?redirectedfrom=MSDN
     *
     * <code>
     *     $em->createRequestBuilder(User::class)
     *        ->where('username', 'username');
     * </code>
     *
     * @param string $field     the restriction field
     * @param string $predicate the restriction predicate
     *
     * @return self
     */
    public function where($field, $predicate);

    /**
     * Adds one or more restrictions to the request results, forming a logical
     * conjunction with any previously specified restrictions.
     *
     * @see https://docs.microsoft.com/en-us/previous-versions/dynamicsnav-2016/hh879066(v=nav.90)?redirectedfrom=MSDN
     *
     * <code>
     *     $em->createRequestBuilder(User::class)
     *        ->where('username', 'username')
     *        ->andWhere('position', '>=1');
     * </code>
     *
     * @param string $field     the restriction field
     * @param string $predicate the request predicate
     *
     * @return self
     *
     * @see where()
     */
    public function andWhere($field, $predicate);

    /**
     * Sets the id of the last result read (the "bookmarkKey").
     *
     * <code>
     *     $em->createRequestBuilder(User::class)
     *        ->where('username', 'username')
     *        ->setFirstResult('id-of-last-result-read');
     * </code>
     *
     * @param string|null $firstResult the id of the last result read
     *
     * @return self
     */
    public function setFirstResult($firstResult);

    /**
     * Gets the id of the last result read (the "bookmarkKey").
     * Returns NULL if {@link setFirstResult} was not applied to this RequestBuilder.
     *
     * @return string|null the id of the last result read
     */
    public function getFirstResult();

    /**
     * Sets the maximum number of results to retrieve (the "setSize").
     *
     * <code>
     *     $em->createRequestBuilder(User::class)
     *        ->where('username', 'username')
     *        ->setMaxResults(10);
     * </code>
     *
     * @param int|null $maxResults the maximum number of results to retrieve
     *
     * @return self
     */
    public function setMaxResults($maxResults);

    /**
     * Gets the maximum number of results the request object was set to retrieve (the "setSize").
     * Returns NULL if {@link setMaxResults} was not applied to this RequestBuilder.
     *
     * @return int|null maximum number of results
     */
    public function getMaxResults();

    /**
     * Find an object by its identifier.
     *
     * <code>
     *     $em->createRequestBuilder(User::class)
     *        ->find(1);
     * </code>
     *
     * @param mixed $identifier the identifier
     *
     * @return object|null the request result
     */
    public function loadById($identifier);

    /**
     * Executes the request and returns the single result.
     *
     * <code>
     *     $em->createRequestBuilder(User::class)
     *        ->where('username', 'username')
     *        ->getOneOrNullResult();
     * </code>
     *
     * @return object|null the request result
     */
    public function getOneOrNullResult();

    /**
     * Executes the request and returns the result.
     *
     * <code>
     *     $em->createRequestBuilder(User::class)
     *        ->where('username', 'username')
     *        ->getResult();
     * </code>
     *
     * @return iterable the request result
     */
    public function getResult();
}
