<?php

namespace MocaBonita\tools;

use Katzgrau\KLogger\Logger;

/**
 * Classe de Exceção do Moça Bonita.
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\Tools
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class MbException extends \Exception
{
    /**
     * @var string
     */
    protected static $logPath;

    /**
     * @return mixed
     */
    public static function getLogPath()
    {
        if(is_null(self::$logPath)){
            self::$logPath = MbDiretorios::PLUGIN_DIRETORIO . '/logs';
        }

        return self::$logPath;
    }

    /**
     * @param string $logPath
     */
    public static function setLogPath($logPath)
    {
        self::$logPath = $logPath;
    }

    /**
     * @param \Exception $e
     */
    public static function adminNotice(\Exception $e){
        MbWPAction::adicionarCallbackAction('admin_notices', function () use ($e){
            echo "<div class='notice notice-error'><p>{$e->getMessage()}</p></div>";
        });
        MbWPAction::adicionarCallbackAction('shutdown', function () use ($e){
            $logger = new Logger(self::getLogPath());
            $logger->error($e->getMessage());
        });
    }

    /**
     * @param \Exception $e
     */
    public static function adminDebug(\Exception $e){
        MbWPAction::adicionarCallbackAction('admin_notices', function () use ($e){
            echo "<div class='notice notice-info'><p>{$e->getMessage()}</p></div>";
        });
        MbWPAction::adicionarCallbackAction('shutdown', function () use ($e){
            $logger = new Logger(self::getLogPath());
            $logger->debug($e->getMessage());
        });
    }
}