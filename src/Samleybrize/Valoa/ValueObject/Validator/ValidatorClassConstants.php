<?php

namespace Samleybrize\Valoa\ValueObject\Validator;

use Samleybrize\Valoa\ValueObject\ValueObjectException;

class ValidatorClassConstants extends ValidatorEnum
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $tags = array())
    {
        if (!array_key_exists("classname", $tags)) {
            throw new ValueObjectException("Tag list must have a 'classname' tag");
        } elseif (!is_string($tags["classname"][0])) {
            throw new ValueObjectException("Tag 'classname' must be a string");
        } elseif (!class_exists($tags["classname"][0])) {
            throw new ValueObjectException("Class '{$tags["classname"][0]}' does not exists");
        }

        // retieve criterias
        $beginWith  = null;
        $endWith    = null;
        $contain    = null;

        if (array_key_exists("beginWith", $tags) && is_scalar($tags["beginWith"][0])) {
            $beginWith = (string) $tags["beginWith"][0];
        }

        if (array_key_exists("endWith", $tags) && is_scalar($tags["endWith"][0])) {
            $beginWith = (string) $tags["endWith"][0];
        }

        if (array_key_exists("contain", $tags) && is_scalar($tags["contain"][0])) {
            $contain = (string) $tags["contain"][0];
        }

        // retrieve constant list that meet criterias
        $reflection = new \ReflectionClass($tags["classname"][0]);
        $constants  = $reflection->getConstants();
        $enum       = array();

        foreach ($constants as $constantName => $constantValue) {
            if ($beginWith && 0 !== strpos($constantName, $beginWith)) {
                continue;
            } elseif ($contain && false === strpos($constantName, $contain)) {
                continue;
            } elseif ($endWith && false === strpos($constantName, $endWith, strlen($constantName) - strlen($endWith))) {
                continue;
            }

            $enum[] = $constantValue;
        }

        // call parent constructor
        $parentTags = array(
            "enum" => array($enum)
        );
        parent::__construct($parentTags);
    }
}
