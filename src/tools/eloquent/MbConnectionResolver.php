<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\ConnectionResolverInterface;
use MocaBonita\tools\MbSingleton;

/**
 * Main class of the MocaBonita WpdbConnectionResolver
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools\eloquent
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbConnectionResolver extends MbSingleton implements ConnectionResolverInterface
{
    /**
     * Connection Name
     *
     * @var string
     */
    protected $connectionName = "wpdb";

    /**
     * Get a database connection instance.
     *
     * @param  string $name
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function connection($name = null)
    {
        return MbDatabaseManager::getInstance();
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