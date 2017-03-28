<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Support\Facades\Facade;
/**
 * @see \Illuminate\Database\DatabaseManager
 * @see \Illuminate\Database\Connection
 */
class MbDatabase extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return MbDatabaseManager
     */
    protected static function getFacadeAccessor()
    {
        return MbDatabaseManager::instance();
    }
}