<?php

namespace MocaBonita\tools;

use MocaBonita\model\MbSessionModel;
use Symfony\Component\HttpFoundation\Session\Session as Base;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Class Session
 * @package MocaBonita\tools
 */
class MbSession extends Base
{

    /**
     * Instancia da classe.
     *
     * @var MbSession
     */
    protected static $instance;

    /**
     * Obter instancia da aplicação.
     *
     * @return MbSession
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            $model   = new MbSessionModel();
            $storage = new NativeSessionStorage();

            $pdoHandle = new PdoSessionHandler(
                MbCapsule::connection()->getPdo(),
                [
                    'db_table'  => $model->getTable(),
                    'db_id_col' => $model->getPrimaryKey(),
                ]
            );

            if(!MbCapsule::schema()->hasTable($model->getTable())){
                $pdoHandle->createTable();
            }

            $storage->setSaveHandler($pdoHandle);

            static::$instance = new static($storage);
        }

        return static::$instance;
    }
}