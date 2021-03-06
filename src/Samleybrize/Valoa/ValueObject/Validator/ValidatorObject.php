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

class ValidatorObject implements ValidatorInterface
{
    /**
     * Class name
     * @var string
     */
    private $classname;

    /**
     * Indicates if the classname is absolute
     * @var boolean
     */
    private $isAbsolute;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $tags = array())
    {
        if (!array_key_exists("classname", $tags)) {
            throw new ValueObjectException("Tag list must have a 'classname' tag");
        } elseif (!is_string($tags["classname"][0])) {
            throw new ValueObjectException("Tag 'classname' must be a string");
        }

        $this->classname    = $tags["classname"][0];
        $this->isAbsolute   = false !== strpos($this->classname, "\\");
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value)
    {
        if ($this->isAbsolute) {
            // absolute classname
            if (!is_a($value, $this->classname)) {
                // failed validation
                $givenType = ("object" == gettype($value)) ? get_class($value) : gettype($value);
                throw new ValueObjectException("Instance of [$this->classname] expected, [$givenType] given");
            }
        } else {
            // relative classname
            // we check the end of the classname of the given value
            if ("object" !== gettype($value)) {
                // failed validation
                $givenType = ("object" == gettype($value)) ? get_class($value) : gettype($value);
                throw new ValueObjectException("Instance of [$this->classname] expected, [$givenType] given");
            }

            $valueType  = "\\" . get_class($value);
            $classname  = "\\" . $this->classname;
            $compare    = substr($valueType, -strlen($classname));

            if ($compare !== $classname) {
                // failed validation
                $givenType = ("object" == gettype($value)) ? get_class($value) : gettype($value);
                throw new ValueObjectException("Instance of [$this->classname] expected, [$givenType] given");
            }
        }

        return true;
    }
}
