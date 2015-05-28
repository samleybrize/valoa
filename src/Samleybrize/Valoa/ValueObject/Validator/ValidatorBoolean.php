<?php

namespace Samleybrize\Valoa\ValueObject\Validator;

use Samleybrize\Valoa\ValueObject\ValueObjectException;

class ValidatorBoolean implements ValidatorInterface
{
    /**
     * Indicates if validation is strict
     * @var boolean
     */
    private $isStrict = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $tags = array())
    {
        if (array_key_exists("strict", $tags)) {
            $this->isStrict = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value)
    {
        if ($this->isStrict && !is_bool($value)) {
            // strict validation failed
            $givenType = ("object" == gettype($value)) ? get_class($value) : gettype($value);
            throw new ValueObjectException("Boolean expected, [$givenType] given");
        }

        $value = (bool) $value;
        return true;
    }
}
