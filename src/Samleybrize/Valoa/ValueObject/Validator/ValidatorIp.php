<?php

/*
 * This file is part of Valoa.
 *
 * (c) Stephen Berquet <stephen.berquet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Samleybrize\Valoa\ValueObject\Validator;

use Samleybrize\Valoa\ValueObject\ValueObjectException;

class ValidatorIp implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $tags = array())
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value)
    {
        if (false === filter_var($value, FILTER_VALIDATE_IP)) {
            // validation failed
            $givenType  = ("object" == gettype($value)) ? get_class($value) : gettype($value);
            $value      = is_scalar($value) ? "[$givenType] '$value'" : "[$givenType]";
            throw new ValueObjectException("$value is not a valid IP address");
        }

        return true;
    }
}
