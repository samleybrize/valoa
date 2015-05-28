<?php

namespace Samleybrize\Valoa\ValueObject\Validator;

use Samleybrize\Valoa\ValueObject\ValueObjectException;

class ValidatorImmutable implements ValidatorInterface
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
        throw new ValueObjectException("Immutable property");
    }
}
