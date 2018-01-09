<?php

namespace MocaBonita\tools;

use Illuminate\Database\Capsule\Manager;
use MocaBonita\tools\eloquent\MbConnectionResolver;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Main class of the MocaBonita Migration
 *
 * How to use: https://laravel.com/docs/5.0/migrations
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools
 * @copyright Jhordan Lima 2017
 *
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbMigration extends Manager
{
    /**
     * Enable PDO for eloquent
     *
     * @return void
     */
    public static function enablePdoConnection()
    {
        if (is_null(self::$instance)) {
            global $wpdb;

            $capsule = new self();
            $capsule->addConnection([
                'driver'    => 'mysql',
                'host'      => DB_HOST,
                'database'  => DB_NAME,
                'username'  => DB_USER,
                'password'  => DB_PASSWORD,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ]);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        }
    }

    /**
     * Enable WPDB for eloquent
     *
     * @return void
     */
    public static function enableWpdbConnection()
    {
        Eloquent::setConnectionResolver(MbConnectionResolver::getInstance());
    }
}