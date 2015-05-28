<?php

namespace Samleybrize\Valoa\ValueObject;

use Samleybrize\Valoa\AnnotationParser;
use Samleybrize\Valoa\ValueObject\Validator\ValidatorInterface;

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
        $propertyList                   = $class->getProperties();
        $docParser                      = new AnnotationParser();

        // properties
        foreach ($propertyList as $property) {
            // skip static properties
            if ($property->isStatic()) {
                continue;
            }

            // determine required data type
            $tags       = $docParser->parse($property->getDocComment());
            $var        = array_key_exists("var", $tags) ? $tags["var"][0] : "any";
            $validator  = array_key_exists("validator", $tags) ? $tags["validator"][0] : null;

            if (empty($validator)) {
                $validator = $var;
            }

            if ($validator && array_key_exists($validator, self::$valueObjectTypeMapper)) {
                $validator = self::$valueObjectTypeMapper[$validator];
            }

            if ($var && array_key_exists($var, self::$valueObjectTypeMapper)) {
                $var = self::$valueObjectTypeMapper[$var];
            }

            if (!in_array($var, self::$valueObjectPrimitives)) {
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

            $propertyName                               = $property->getName();
            $validatorObject                            = $builtinExists ? new $classname($tags) : new $validator($tags);

            if (!$validatorObject instanceof ValidatorInterface) {
                $class      = get_class($validatorObject);
                $interface  = __NAMESPACE__ . "\\Validator\\ValidatorInterface";
                throw new ValueObjectException("Class '$class' does not implements '$interface'");
            }

            self::$valueObjectValidators[$propertyName] = $validatorObject;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (!array_key_exists($name, self::$valueObjectValidators)) {
            // undefined property
            throw new ValueObjectException("Undefined property: " . __CLASS__ . "::\$$name");
        } elseif ($this->$name instanceof LazyLoaderInterface) {
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

        if (array_key_exists($name, self::$valueObjectValidators) && !($value instanceof LazyLoaderInterface)) {
            // validate value
            self::$valueObjectValidators[$name]->isValid($value);
        }

        $this->$name = $value;
    }
}
