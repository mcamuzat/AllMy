<?php

namespace Allmy\Reactor;

use Allmy\Reactor\StreamSelectLoop;

class Factory
{
    public static function create()
    {
        return new StreamSelectReactor();
    }
}
