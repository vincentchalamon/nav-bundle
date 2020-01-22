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

namespace Backup\NavBundle\RequestBuilder\Expr;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Expr
{
    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function or($field, $predicate)
    {
        return new OrExpr($field, $predicate);
    }

    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function and($field, $predicate)
    {
        return new AndExpr($field, $predicate);
    }

    /**
     * @param string $field the field
     *
     * @return ExprInterface
     */
    public function interval($field, $start = null, $end = null)
    {
        if (null === $start && null === $end) {
            // TODO: Throw typed \InvalidArgumentException.
        }

        return new Interval($field, $start, $end);
    }

    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function neq($field, $predicate)
    {
    }

    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function gt($field, $predicate)
    {
    }

    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function gte($field, $predicate)
    {
    }

    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function lt($field, $predicate)
    {
    }

    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function lte($field, $predicate)
    {
    }

    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function eq($field, $predicate)
    {
    }

    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function match($field, $predicate)
    {
    }

    /**
     * @param string          $field     the field
     * @param string|int|bool $predicate the value
     *
     * @return ExprInterface
     */
    public function unknown($field, $predicate)
    {
    }
}