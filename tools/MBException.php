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
class MBException extends \Exception
{
    public static function adminNotice(\Exception $e){
        WPAction::adicionarCallbackAction('admin_notices', function () use ($e){
            echo "<div class='notice notice-error'><p>{$e->getMessage()}</p></div>";
        });
        WPAction::adicionarCallbackAction('shutdown', function () use ($e){
            echo "<div class='notice notice-error'><p>{$e->getMessage()}</p></div>";
            $logger = new Logger(Diretorios::PLUGIN_DIRETORIO . '/logs');
            $logger->error($e->getMessage());
        });
    }
}