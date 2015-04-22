<?php

namespace Samleybrize\Valoa\ValueObject;

interface LazyLoaderInterface
{
    /**
     * Loads and return the value
     * @return mixed
     */
    public function load();
}
