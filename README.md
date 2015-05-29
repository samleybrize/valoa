# Valoa - Easy value objects and entities

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
the validator with the `@validator` tag. If you don't want any validation on a property, use the `any` validator.

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

### Array types

to be written

### Validator list

to be written

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

## Installation

For a quick install with [Composer](https://getcomposer.org) use :

    $ composer require samleybrize/bugzorcist

## Requirements

- PHP 5.4+

## Author

This project is authored and maintained by Stephen Berquet.

## License

Valoa is licensed under the MIT License - see the `LICENSE` file for details.
