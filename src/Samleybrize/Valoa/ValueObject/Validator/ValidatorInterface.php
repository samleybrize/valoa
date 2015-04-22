<?php

namespace Samleybrize\Valoa\ValueObject\Validator;

interface ValidatorInterface
{
    /**
     * Constructor
     * @param array $tags [optional] PHPDoc tags of the property
     */
    public function __construct(array $tags = array());

    /**
     * Validates a value
     * @param mixed $value the value to validate.
     * Passed by reference and can be modified to accomodate to the target type (eg: you pass a string and an integer is expected, the value is then converted to integer).
     * @param boolean $strict [optional] if true, strict validation is performed
     * (eg: if an integer is required, only a true integer will pass the validation, and not a string representing an integer).
     * @return boolean
     * @throws \Samleybrize\Valoa\ValueObject\ValueObjectException
     */
    public function isValid(&$value, $strict = false);
}
