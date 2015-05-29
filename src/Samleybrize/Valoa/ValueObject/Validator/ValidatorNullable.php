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

class ValidatorNullable implements ValidatorInterface
{
    /**
     * @var \Samleybrize\Valoa\ValueObject\Validator\ValidatorInterface
     */
    private $validator;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $tags = array())
    {
        if (!array_key_exists("validator", $tags) || !$tags["validator"] instanceof ValidatorInterface) {
            $interfaceName = __NAMESPACE__ . "\\ValidatorInterface";
            throw new ValueObjectException("Tag 'validator' must contain an instance of '$interfaceName'");
        }

        $this->validator = $tags["validator"];
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value)
    {
        if (null !== $value) {
            return $this->validator->isValid($value);
        }

        return true;
    }
}
