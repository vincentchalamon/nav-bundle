<?php

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