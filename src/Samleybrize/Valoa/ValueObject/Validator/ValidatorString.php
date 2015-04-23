<?php

namespace Samleybrize\Valoa\ValueObject\Validator;

use Samleybrize\Valoa\ValueObject\ValueObjectException;

class ValidatorString implements ValidatorInterface
{
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
        if (array_key_exists("min-length", $tags)) {
            $this->minLength = is_numeric($tags["min-length"]) ? (int) $tags["min-length"] : null;
        }

        if (array_key_exists("max-length", $tags)) {
            $this->maxLength = is_numeric($tags["max-length"]) ? (int) $tags["max-length"] : null;
        }

        if (array_key_exists("regex", $tags)) {
            $this->regex = is_numeric($tags["regex"]) ? (int) $tags["regex"] : null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value, $strict = false)
    {
        if ($strict && !is_string($value)) {
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
            throw new ValueObjectException("Invalid value. Must match the regex '$this->_regex'");
        }

        $value = (string) $value;
        return true;
    }
}
