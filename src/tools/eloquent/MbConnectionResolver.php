<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\ConnectionResolverInterface;

/**
 * Class Resolver
 * @package MocaBonita\tools\Eloquent
 */
class MbConnectionResolver implements ConnectionResolverInterface
{

    /**
     * Instancia da classe.
     *
     * @var MbConnectionResolver
     */
    protected static $instance;

    /**
     * Obter instancia da aplicação.
     *
     * @return MbConnectionResolver
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @var string
     */
    protected $connectionName = "wpdb";

    /** @noinspection PhpUnusedParameterInspection
     *
     * Get a database connection instance.
     *
     * @param  string $name
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function connection($name = null)
    {
        return MbDatabaseManager::instance();
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->connectionName;
    }

    /**
     * Set the default connection name.
     *
     * @param  string $name
     *
     * @return void
     */
    public function setDefaultConnection($name)
    {
        $this->connectionName = $name;
    }
}