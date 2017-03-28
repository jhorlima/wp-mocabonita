<?php

namespace MocaBonita\tools;

use Illuminate\Database\Capsule\Manager;
use MocaBonita\tools\eloquent\MbConnectionResolver;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class Migration
 * @package MocaBonita\tools
 */
class MbCapsule extends Manager
{
    /**
     * Obter instancia da aplicação.
     *
     * @return void
     */
    public static function pdo()
    {
       if(is_null(self::$instance)){
           global $wpdb;

           $capsule = new self();
           $capsule->addConnection([
               'driver'    => 'mysql',
               'host'      => DB_HOST,
               'database'  => DB_NAME,
               'username'  => DB_USER,
               'password'  => DB_PASSWORD,
               'charset'   => DB_CHARSET,
               'collation' => DB_COLLATE ?: $wpdb->collate,
           ]);
           $capsule->setAsGlobal();
           $capsule->bootEloquent();
       }
    }

    /**
     * Adicionar o wpdb como resolver
     *
     */
    public static function wpdb(){
        Eloquent::setConnectionResolver(MbConnectionResolver::getInstance());
    }
}