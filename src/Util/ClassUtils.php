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

namespace NavBundle\Util;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassUtils
{
    /**
     * Get the real class of an object or a class name.
     *
     * @param string|object $className
     *
     * @return string
     */
    public static function getRealClass($className)
    {
        if (\is_object($className)) {
            $className = \get_class($className);
        }

        $positionCg = strrpos($className, '\\__CG__\\');
        $positionPm = strrpos($className, '\\__PM__\\');
        if ((false === $positionCg) && (false === $positionPm)) {
            return $className;
        }

        // @codeCoverageIgnoreStart
        if (false !== $positionCg) {
            return substr($className, $positionCg + 8);
        }
        // @codeCoverageIgnoreEnd

        $className = ltrim($className, '\\');

        return substr(
            $className,
            8 + $positionPm,
            strrpos($className, '\\') - ($positionPm + 8)
        );
    }
}
