<?php

namespace MocaBonita\tools;

use MocaBonita\model\MbSessaoModel;
use Symfony\Component\HttpFoundation\Session\Session as Base;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Class Session
 * @package MocaBonita\tools
 */
class MbSessao extends Base
{

    /**
     * Instancia da classe.
     *
     * @var MbSessao
     */
    protected static $instance;

    /**
     * Obter instancia da aplicação.
     *
     * @return MbSessao
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            $model   = new MbSessaoModel();
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