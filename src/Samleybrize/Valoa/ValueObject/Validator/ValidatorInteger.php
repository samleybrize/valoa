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

class ValidatorInteger implements ValidatorInterface
{
    /**
     * Indicates if validation is strict
     * @var boolean
     */
    private $isStrict = false;

    /**
     * Min value
     * @var int
     */
    private $min;

    /**
     * Max value
     * @var int
     */
    private $max;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $tags = array())
    {
        if (array_key_exists("strict", $tags)) {
            $this->isStrict = true;
        }

        if (array_key_exists("min", $tags)) {
            $this->min = is_numeric($tags["min"][0]) ? (int) $tags["min"][0] : null;
        }

        if (array_key_exists("max", $tags)) {
            $this->max = is_numeric($tags["max"][0]) ? (int) $tags["max"][0] : null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value)
    {
        if ($this->isStrict && !is_int($value)) {
            // strict validation failed
            $givenType = ("object" == gettype($value)) ? get_class($value) : gettype($value);
            throw new ValueObjectException("Integer expected, [$givenType] given");
        } elseif (!is_scalar($value)) {
            // soft validation failed
            $givenType = ("object" == gettype($value)) ? get_class($value) : gettype($value);
            throw new ValueObjectException("Scalar expected, [$givenType] given");
        } elseif (null !== $this->min && $value < $this->min) {
            // min value validation failed
            throw new ValueObjectException("Value must be greater or equal to '$this->min', '$value' given");
        } elseif (null !== $this->max && $value > $this->max) {
            // max value validation failed
            throw new ValueObjectException("Value must be lower or equal to '$this->max', '$value' given");
        }

        $value = (int) $value;
        return true;
    }
}
