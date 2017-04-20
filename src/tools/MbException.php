<?php

namespace MocaBonita\tools;

use Illuminate\Contracts\Support\Arrayable;
use Katzgrau\KLogger\Logger;
use MocaBonita\view\View;

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
     * @var bool
     */
    protected static $salvarLog;

    /**
     * @var string
     */
    protected static $logPath;

    /**
     * @var null|array|Arrayable
     */
    protected $dados;

    /**
     * @return array|string
     */
    public function getDados()
    {
        return $this->dados;
    }

    /**
     * @return array|null
     */
    public function getDadosArray()
    {
        if ($this->dados instanceof Arrayable) {
            $this->dados = $this->dados->toArray();
        }

        if (!is_array($this->dados)) {
            $this->dados = null;
        }
        return $this->dados;
    }

    /**
     * @param array|Arrayable|View $dados
     * @return MbException
     */
    public function setDados($dados)
    {
        $this->dados = $dados;
        return $this;
    }

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * @param string $msg
     * @param int $code
     * @param null|array|View|Arrayable $dados
     *
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct($msg, $code = 400, $dados = null)
    {
        parent::__construct($msg, $code);

        $this->setDados($dados);
    }

    /**
     * @return mixed
     */
    public static function getLogPath()
    {
        if(is_null(self::$salvarLog)){
            self::$salvarLog = MbDiretorios::PLUGIN_DIRETORIO . '/logs';
        }

        return self::$salvarLog;
    }

    /**
     * @param string $logPath
     */
    public static function setLogPath($logPath)
    {
        self::$salvarLog = $logPath;
    }

    /**
     * @return boolean
     */
    public static function isSalvarLog()
    {
        return (bool) self::$salvarLog;
    }

    /**
     * @param boolean $salvarLog
     */
    public static function setSalvarLog($salvarLog = true)
    {
        self::$salvarLog = (bool) $salvarLog;
    }

    /**
     * @param \Exception $e
     */
    public static function adminNotice(\Exception $e){
        MbWPAction::adicionarCallbackAction('admin_notices', function () use ($e){
            echo "<div class='notice notice-error'><p>{$e->getMessage()}</p></div>";
        });
        self::salvarLog($e);
    }

    /**
     * @param \Exception $e
     */
    public static function adminDebug(\Exception $e){
        MbWPAction::adicionarCallbackAction('admin_notices', function () use ($e){
            echo "<div class='notice notice-info'><p>{$e->getMessage()}</p></div>";
        });
        self::salvarLog($e);
    }

    protected static function salvarLog(\Exception $e){
        if(!self::isSalvarLog()){
            return false;
        }

        $logger = new Logger(self::getLogPath());
        $logger->debug($e->getMessage());

        return true;
    }
}