<?php

namespace Samleybrize\Valoa\ValueObject\Validator;

use Samleybrize\Valoa\ValueObject\ValueObjectException;

class ValidatorArray implements ValidatorInterface
{
    /**
     * @var \Samleybrize\Valoa\ValueObject\Validator\ValidatorInterface
     */
    private $elementValidator;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $tags = array())
    {
        if (!array_key_exists("validator", $tags) || !$tags["validator"] instanceof ValidatorInterface) {
            $interfaceName = __NAMESPACE__ . "\\ValidatorInterface";
            throw new ValueObjectException("Tag 'validator' must contain an instance of '$interfaceName'");
        }

        $this->elementValidator = $tags["validator"];
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value)
    {
        if (!is_array($value)) {
            $givenType = ("object" == gettype($value)) ? get_class($value) : gettype($value);
            throw new ValueObjectException("Array expected, [$givenType] given");
        }

        foreach ($value as $k => &$v) {
            try {
                $this->elementValidator->isValid($v);
            } catch (ValueObjectException $e) {
                throw new ValueObjectException("Invalid array: key '$k': " . $e->getMessage(), $e->getCode(), $e);
            }
        }

        return true;
    }
}
