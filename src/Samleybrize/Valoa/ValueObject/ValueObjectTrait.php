<?php

/*
 * This file is part of Valoa.
 *
 * (c) Stephen Berquet <stephen.berquet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Samleybrize\Valoa\ValueObject;

use Samleybrize\Valoa\AnnotationParser;
use Samleybrize\Valoa\ValueObject\Validator\ValidatorArray;
use Samleybrize\Valoa\ValueObject\Validator\ValidatorInterface;
use Samleybrize\Valoa\ValueObject\Validator\ValidatorNullable;

trait ValueObjectTrait
{
    /**
     * Type aliases
     * @var array
     */
    private static $valueObjectTypeMapper = array(
        "String"    => "string",
        "long"      => "integer",
        "int"       => "integer",
        "number"    => "float",
        "double"    => "float",
        "bool"      => "boolean",
        "mixed"     => "any",
        "Array"     => "array",
    );

    /**
     * Primitive type list
     * @var array
     */
    private static $valueObjectPrimitives = array(
        "string",
        "integer",
        "float",
        "boolean",
        "array",
        "any",
    );

    /**
     * Validators list
     * @var \Samleybrize\Valoa\ValueObject\Validator\ValidatorInterface[]
     */
    private static $valueObjectValidators;

    /**
     * Loads properties validators
     * @throws \Samleybrize\Valoa\ValueObject\ValueObjectException
     */
    private function loadValueObjectValidators()
    {
        if (null !== self::$valueObjectValidators) {
            return;
        }

        self::$valueObjectValidators    = array();
        $class                          = new \ReflectionClass($this);
        $docParser                      = new AnnotationParser();
        $immutable                      = false;

        // handle immutable class
        $c = $class;

        do {
            $classTags = $docParser->parse($c->getDocComment());

            if (array_key_exists("immutable", $classTags)) {
                $immutable = $this->loadValueObjectValidator(array(
                    "validator" => array("immutable")
                ));

                break;
            }

            $c = $c->getParentClass();
        } while ($c);

        // properties
        do {
            $propertyList = $class->getProperties();

            foreach ($propertyList as $property) {
                // skip static properties
                $propertyName = $property->getName();

                if ($property->isStatic() || array_key_exists($propertyName, self::$valueObjectValidators)) {
                    continue;
                }

                // create validator
                $tags = $docParser->parse($property->getDocComment());

                if (array_key_exists("exclude", $tags)) {
                    continue;
                }

                self::$valueObjectValidators[$propertyName] = !$immutable ? $this->loadValueObjectValidator($tags) : $immutable;
            }

            $class = $class->getParentClass();
        } while ($class);
    }

    /**
     * Loads a property validator
     * @param array $tags doc comment tags
     * @throws \Samleybrize\Valoa\ValueObject\ValueObjectException
     */
    private function loadValueObjectValidator(array $tags)
    {
        $var            = array_key_exists("var", $tags) ? $tags["var"][0] : "any";
        $validator      = array_key_exists("validator", $tags) ? $tags["validator"][0] : null;
        $isArray        = false;

        // handle immutable property
        if (array_key_exists("immutable", $tags)) {
            return $this->loadValueObjectValidator(array(
                "validator" => array("immutable")
            ));
        }

        // handle nullable property
        if (array_key_exists("nullable", $tags)) {
            unset($tags["nullable"]);
            $validatorObject    = $this->loadValueObjectValidator($tags);
            $validatorObject    = new ValidatorNullable(array("validator" => $validatorObject));
            return $validatorObject;
        }

        // handle array types
        if ("[]" == substr($var, -2)) {
            $isArray    = true;
            $var        = substr($var, 0, -2);
        } elseif ("array" == $var || "Array" == $var) {
            $isArray    = true;
            $var        = "any";
        }

        if ($isArray) {
            $tags["var"][0]     = $var;
            $validatorObject    = $this->loadValueObjectValidator($tags);
            $validatorObject    = new ValidatorArray(array("validator" => $validatorObject));
            return $validatorObject;
        }

        // determine required data type
        if (empty($validator)) {
            $validator = $var;
        }

        if ($validator && array_key_exists($validator, self::$valueObjectTypeMapper)) {
            $validator = self::$valueObjectTypeMapper[$validator];
        }

        if ($var && array_key_exists($var, self::$valueObjectTypeMapper)) {
            $var = self::$valueObjectTypeMapper[$var];
        }

        if (!in_array($var, self::$valueObjectPrimitives) && $validator === $var) {
            $tags["classname"]  = array($var);
            $validator          = "object";
        }

        // instanciate validator
        $classname      = __NAMESPACE__ . "\\Validator\\Validator" . ucfirst($validator);
        $builtinExists  = class_exists($classname);
        $customExists   = class_exists($validator);

        if (!$builtinExists && !$customExists) {
            throw new ValueObjectException("'$validator' does not name a validator");
        }

        $validatorObject = $builtinExists ? new $classname($tags) : new $validator($tags);

        if (!$validatorObject instanceof ValidatorInterface) {
            $class      = get_class($validatorObject);
            $interface  = __NAMESPACE__ . "\\Validator\\ValidatorInterface";
            throw new ValueObjectException("Class '$class' does not implements '$interface'");
        }

        return $validatorObject;
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return array_key_exists($name, self::$valueObjectValidators);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        self::loadValueObjectValidators();

        if (!array_key_exists($name, self::$valueObjectValidators)) {
            // undefined property
            throw new ValueObjectException("Undefined property: " . __CLASS__ . "::\$$name");
        } elseif ($this->$name instanceof ValueObjectLazyLoaderInterface) {
            // lazy load the value
            $this->__set($name, $this->$name->load());
        }

        return $this->$name;
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        self::loadValueObjectValidators();

        if (array_key_exists($name, self::$valueObjectValidators) && !($value instanceof ValueObjectLazyLoaderInterface)) {
            // validate value
            self::$valueObjectValidators[$name]->isValid($value);
        }

        $this->$name = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        // lazy loaders can cause troubles on cloned objects so we resolve all of them
        foreach (self::$valueObjectValidators as $name => $value) {
            $this->__get($name);
        }
    }
}
