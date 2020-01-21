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
interface ExprInterface
{
    /**
     * Renders an expression.
     *
     * @return string
     */
    public function __toString();
}
