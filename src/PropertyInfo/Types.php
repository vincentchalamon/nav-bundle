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

namespace NavBundle\PropertyInfo;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Types
{
    public const DATE = 'date';
    public const DATETIME = 'datetime';
    public const DATETIMEZ = 'datetimez';
    public const TIME = 'time';
    public const DATE_IMMUTABLE = 'date_immutable';
    public const DATETIME_IMMUTABLE = 'datetime_immutable';
    public const DATETIMEZ_IMMUTABLE = 'datetimetz_immutable';
    public const TIME_IMMUTABLE = 'time_immutable';
    public const ARRAY = 'array';
    public const SMALLINT = 'smallint';
    public const INT = 'int';
    public const INTEGER = 'integer';
    public const BIGINT = 'bigint';
    public const FLOAT = 'float';
    public const STRING = 'string';
    public const TEXT = 'text';
    public const GUID = 'guid';
    public const DECIMAL = 'decimal';
    public const BOOL = 'bool';
    public const BOOLEAN = 'boolean';
    public const BLOB = 'blob';
    public const BINARY = 'binary';
    public const OBJECT = 'object';
}
