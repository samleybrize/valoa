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

class ValidatorEnum implements ValidatorInterface
{
    /**
     * Min length
     * @var mixed
     */
    private $allowedValues;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $tags = array())
    {
        if (!array_key_exists("enum", $tags)) {
            throw new ValueObjectException("Tag list must have an 'enum' tag");
        } elseif (!is_array($tags["enum"][0])) {
            throw new ValueObjectException("Tag 'enum:0' must be an array");
        }

        $this->allowedValues = $tags["enum"][0];
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value, $strict = false)
    {
        if (!in_array($value, $this->allowedValues, true)) {
            // validation failed
            $givenType  = ("object" == gettype($value)) ? get_class($value) : gettype($value);
            $value      = is_scalar($value) ? "[$givenType] '$value'" : "[$givenType]";
            throw new ValueObjectException("Unexpected value: $value");
        }

        return true;
    }
}
