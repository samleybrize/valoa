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

interface ValueObjectLazyLoaderInterface
{
    /**
     * Loads and return the value
     * @return mixed
     */
    public function load();
}
