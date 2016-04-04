# Valoa - Easy value objects and entities

## Installation

For a quick install with [Composer](https://getcomposer.org) use :

    $ composer require samleybrize/valoa

## Requirements

- PHP 5.4+

## Usage

Traditionnaly, when you build value objects (or entities), you define properties as private and then you create a getter (and maybe a setter) for each of them.
Valoa handles get and set operations without the need to create any method. All you need is to use the `Samleybrize\Valoa\ValueObject\ValueObjectTrait` trait.

```php
use Samleybrize\Valoa\ValueObject\ValueObjectTrait;

class EntityTest
{
    use ValueObjectTrait;

    /**
     * @var int
     */
    private $var1;

    /**
     * @var string
     */
    private $var2;
}

$test       = new EntityTest();
$test->var1 = 23;
$test->var2 = "message";

echo $test->var2;
```

Additionally, you can define constraints to your properties. On the example above, `$var1` only accepts integer values, as specified by the `@var` tag.

### Validation strictness

By default, validation is non-strict. For some validators, non-strict validation allows you to give a value of a different type that will be converted.
For an integer property, you can set a string `123azerty` that will be converted to the integer `123`.
Strict validation is enabled with the `@strict` tag. If strict validation is enabled on an integer property, only integers will be accepted.

```php
use Samleybrize\Valoa\ValueObject\ValueObjectTrait;

class EntityTest
{
    use ValueObjectTrait;

    /**
     * @var int
     * @strict
     */
    private $var1;

    /**
     * @var int
     */
    private $var2;
}

$test       = new EntityTest();
$test->var2 = "23text"; // will be converted to the integer "23"
$test->var1 = "23text"; // will raise an exception
```

### Define the validator used

By default, the `@var` tag define which validator will be used. If for somme reason you need to put some funky value into that tag, you have to specify
the validator with the `@validator` tag. If there is a `@validator` tag, the `@var` tag is always ignored.

If you don't want any validation on a property, use the `any` validator.

```php
use Samleybrize\Valoa\ValueObject\ValueObjectTrait;

class EntityTest
{
    use ValueObjectTrait;

    /**
     * @var some text
     * @validator string
     */
    private $var1;

    /**
     * @var some text
     * @validator any
     */
    private $var2;
}

$test       = new EntityTest();
$test->var1 = "text"; // validate as string
$test->var2 = "whatever you want"; // will not be validated, any value may be setted
```

### Immutable property

A property can be made immutable with the `@immutable` tag. Immutable properties can't be setted from outside.

```php
use Samleybrize\Valoa\ValueObject\ValueObjectTrait;

class EntityTest
{
    use ValueObjectTrait;

    /**
     * @var string
     * @immutable
     */
    private $var1;
}

$test       = new EntityTest();
$test->var1 = "text"; // will raise an exception
```

You can make all properties immutable at once by setting the `@immutable` tag on the class.

```php
/**
 * @immutable
 */
class EntityTest
{
    use ValueObjectTrait;

    /**
     * @var string
     */
    private $var1;

    /**
     * @var boolean
     */
    private $var2;
}

$test       = new EntityTest();
$test->var1 = "text"; // will raise an exception
$test->var2 = true; // will raise an exception
```

### Nullable property

A property can accept a `null` value with the `@nullable` tag.

```php
class EntityTest
{
    use ValueObjectTrait;

    /**
     * @var string
     */
    private $var1;

    /**
     * @var string
     * @nullable
     */
    private $var2;
}

$test       = new EntityTest();
$test->var1 = "text"; // ok
$test->var1 = null; // will raise an exception

$test->var2 = "text"; // ok
$test->var2 = null; // ok
```

### Array types

Array types can be specified in several ways. The simplest method is to add `[]` to the tag. This way you can also validate multidimensional arrays,
by adding as many `[]` as you want.

```php
/**
 * Array of string
 * @var string[]
 */
private $var1;

/**
 * Array of DateTime objects
 * @var \DateTime[]
 */
private $var2;

/**
 * Array of array of string
 * @var string[][]
 */
private $var1;
```

The other way of specifying array types is `@var array` and `@validator` setted to the underlying type. If there is no `@validator` tag, the validator `any`
is used by default. This method does not allow to validate multidimensional arrays.

```php
/**
 * Array of string
 * @var array
 * @validator string
 */
private $var1;

/**
 * Array of DateTime objects
 * @var array
 * @validator \DateTime
 */
private $var2;
```

### Enum validator

The enum validator allows you to define a list of accepted values with the `@enum` tag. Each allowed value should be scalar.

```php
/**
 * This var accept integer 1, integer 5 and string "text".
 * @var ...
 * @validator enum
 * @enum [1, 5, "text"]
 */
private $var;
```

### Class constants validator

The class constants validator define a set of class constants as allowed values. The `@classname` tag specify the full class name that contains
allowed class constants. By default, all class constants in that class are allowed. You can restrict allowed values with the followin options :

- The `@contain` tag is a filter on the class constants names
- The `@beginWith` tag is a filter on the beginning of class constants names
- The `@endWith` tag is a filter on the end of class constants names

```php
class Test
{
    const TEST_NAME1   = 1;
    const TEST_NAME2_P = 2;

    const X_AZERTY3    = 3;
    const X_AZERTY4_P  = 3;

    /**
     * Allow all class constants of the class 'Test'
     * @var int
     * @validator ClassConstants
     * @classname Test
     */
    private $var1;

    /**
     * Allow TEST_NAME1 and TEST_NAME2_P
     * @var int
     * @validator ClassConstants
     * @classname Test2
     * @beginWith TEST_
     */
    private $var1;

    /**
     * Allow TEST_NAME2_P and X_NAME4_P
     * @var int
     * @validator ClassConstants
     * @classname Test2
     * @endWith _P
     */
    private $var1;

    /**
     * Allow TEST_NAME1 and TEST_NAME2_P
     * @var int
     * @validator ClassConstants
     * @classname Test2
     * @contain NAME
     */
    private $var1;
}
```

### Validator list

| Validator         | Options     | Description                             | Non strict       |
|-------------------|-------------|-----------------------------------------|------------------|
| Any               |             | Allow any value                         |                  |
| Boolean           |             | Allow boolean values                    | Any scalar value |
| ClassConstants    |             | Allow constants values of a given class | Any scalar value |
|                   | `beginWith` | Filter constant names                   |                  |
|                   | `endWith`   | Filter constant names                   |                  |
|                   | `contain`   | Filter constant names                   |                  |
| Email             |             | Allow email adresses                    |                  |
| Enum              |             | Allow a predefined list of values       | Any scalar value |
| Float             |             | Allow decimal values                    | Any scalar value |
|                   | `min`       | Min valid value                         |                  |
|                   | `max`       | Max valid value                         |                  |
| Integer           |             | Allow integer values                    | Any scalar value |
|                   | `min`       | Min valid value                         |                  |
|                   | `max`       | Max valid value                         |                  |
| Ip                |             | Allow IP addresses                      |                  |
| String            |             | Allow strings                           | Any scalar value |
|                   | `minLength` | Min valid string length                 |                  |
|                   | `maxLength` | Max valid string length                 |                  |
|                   | `regex`     | Validation regex (ex: ^[0-9]+$)         |                  |

### Write your own validator

Custom validators can be created by implementing the `Samleybrize\Valoa\ValueObject\Validator\ValidatorInterface` interface.

```php
namespace Your\Namespace;

use Samleybrize\Valoa\ValueObject\Validator\ValidatorInterface;

class CustomValidator implements ValidatorInterface
{
    // $tags contains all doc comment tags
    public function __construct(array $tags = array())
    {
        // ...
    }

    public function isValid(&$value)
    {
        // ...
    }
}
```

To use it, you have to specify the full class name on the `@validator` tag.

```php
use Samleybrize\Valoa\ValueObject\ValueObjectTrait;

class EntityTest
{
    use ValueObjectTrait;

    /**
     * @var string
     * @validator \Your\Namespace\CustomValidator
     */
    private $var1;
}
```

### Lazy loaders

Sometimes you want to retrieve some data on demand. All properties accept an instance of the `Samleybrize\Valoa\ValueObject\ValueObjectLazyLoaderInterface` interface
regardless of its associated validator. When you first try to get its value, the lazy loader is used to retrieve the effective value
and validates it using the validator. Next times, the lazy loader is no longer used.

```php
use Samleybrize\Valoa\ValueObject\ValueObjectLazyLoaderInterface;
use Samleybrize\Valoa\ValueObject\ValueObjectTrait;

class Test
{
    /**
     * @var string
     */
    private $var;
}

class LazyLoaderString implements ValueObjectLazyLoaderInterface
{
    public function load()
    {
        // retrieve the value from a database for example
        return "text";
    }
}

class LazyLoaderInteger implements ValueObjectLazyLoaderInterface
{
    public function load()
    {
        // retrieve the value from a database for example
        return 12;
    }
}

$test       = new EntityTest();

$test->var  = new LazyLoaderString();
echo $test->var; // outputs "text"

$test->var  = new LazyLoaderInteger();
echo $test->var; // raise an exception
```

## Known limitations

Array types can't be directly modified from outside. Instead, you have two workarounds :

- Retrieve and modify the array externally then set the whole array
- Create a method that modify the array from inside

```php
// will not work
$object->array[] = 8;

// workaround 1
$array         = $object->array;
$array[]       = 8;
$object->array = $array;

// workaround 2
$object->addToArray(8);
```

## Author

This project is authored and maintained by Stephen Berquet.

## License

Valoa is licensed under the MIT License - see the `LICENSE` file for details.
