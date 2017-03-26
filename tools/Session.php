<?php

namespace MocaBonita\tools;

use Symfony\Component\HttpFoundation\Session\Session as Base;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Class Session
 * @package MocaBonita\tools
 */
class Session extends Base
{

    /**
     * Instancia da classe.
     *
     * @var Session
     */
    protected static $instance;

    /**
     * Obter instancia da aplicação.
     *
     * @return Session
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            $storage = new NativeSessionStorage();
            $storage->setSaveHandler(new PdoSessionHandler(MysqlAdapter::getInstance()->getConnection()));
            static::$instance = new static($storage);
        }

        return static::$instance;
    }
}