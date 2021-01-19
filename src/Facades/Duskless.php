<?php
namespace McCaulay\Duskless\Facades;

use Illuminate\Support\Facades\Facade;

class Duskless extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'duskless';
    }
}