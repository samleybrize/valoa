<?php

namespace Samleybrize\Valoa\ValueObject;

use Samleybrize\Valoa\AnnotationParser;

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
            $type       = array_key_exists("var", $tags) ? $tags["var"][0] : "any";
            $type       = array_key_exists("type", $tags) ? $tags["type"][0] : $type;

            if ($type && array_key_exists($type, self::$valueObjectTypeMapper)) {
                $type = self::$valueObjectTypeMapper[$type];
            }

            // instanciate validator
            $classname = __NAMESPACE__ . "\\Validator\\Validator" . ucfirst($type);

            if (!class_exists($classname)) {
                throw new ValueObjectException("'$type' does not name a validator");
            }

            $propertyName                               = $property->getName();
            self::$valueObjectValidators[$propertyName] = new $classname($tags);
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
