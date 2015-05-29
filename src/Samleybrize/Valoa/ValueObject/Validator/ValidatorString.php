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

class ValidatorString implements ValidatorInterface
{
    /**
     * Indicates if validation is strict
     * @var boolean
     */
    private $isStrict = false;

    /**
     * Min length
     * @var int
     */
    private $minLength;

    /**
     * Max length
     * @var int
     */
    private $maxLength;

    /**
     * Validation regex
     * @var string
     */
    private $regex;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $tags = array())
    {
        if (array_key_exists("strict", $tags)) {
            $this->isStrict = true;
        }

        if (array_key_exists("minLength", $tags)) {
            $this->minLength = is_numeric($tags["minLength"][0]) ? (int) $tags["minLength"][0] : null;
        }

        if (array_key_exists("maxLength", $tags)) {
            $this->maxLength = is_numeric($tags["maxLength"][0]) ? (int) $tags["maxLength"][0] : null;
        }

        if (array_key_exists("regex", $tags) && is_string($tags["regex"][0])) {
            $this->regex = $tags["regex"][0];
            $this->regex = str_replace("#", "\\#", $this->regex);
            $this->regex = "#{$this->regex}#";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value)
    {
        if ($this->isStrict && !is_string($value)) {
            // strict validation failed
            $givenType = ("object" == gettype($value)) ? get_class($value) : gettype($value);
            throw new ValueObjectException("String expected, [$givenType] given");
        } elseif (!is_scalar($value)) {
            // soft validation failed
            $givenType = ("object" == gettype($value)) ? get_class($value) : gettype($value);
            throw new ValueObjectException("Scalar expected, [$givenType] given");
        }

        $length = strlen($value);

        if (null !== $this->minLength && $length < $this->minLength) {
            // min length validation failed
            throw new ValueObjectException("String value must contain at least $this->minLength characters, it contains $length");
        } elseif (null !== $this->maxLength && $length > $this->maxLength) {
            // max length validation failed
            throw new ValueObjectException("String value must contain at most $this->maxLength characters, it contains $length");
        } elseif (null !== $this->regex && !preg_match($this->regex, $value)) {
            // regex validation failed
            throw new ValueObjectException("Invalid value. Must match the regex '$this->regex'");
        }

        $value = (string) $value;
        return true;
    }
}
