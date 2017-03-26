<?php

namespace MocaBonita\tools;

use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class MigrationOutput
 * @package MocaBonita\tools
 */
class MigrationOutput extends BufferedOutput
{
    /**
     * Instancia da classe.
     *
     * @var MigrationOutput
     */
    protected static $instance;

    /**
     * Obter instancia da aplicação.
     *
     * @return MigrationOutput
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}