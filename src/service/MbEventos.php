<?php

namespace MocaBonita\service;

use MocaBonita\MocaBonita;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbPaginas;
use MocaBonita\tools\MbRequisicoes;
use MocaBonita\tools\MbRespostas;
use MocaBonita\tools\MbSingleton;

/**
 * Classe de gerenciamento de eventos do moçabonita.
 *
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\service
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
abstract class MbEventos extends MbSingleton
{

    /**
     *Nome evento ao iniciar o wordpress
     */
    const START_WORDPRESS = "startWpDispatcher";

    /**
     * Nome do evento ao finalizar o wordpress
     *
     */
    const FINISH_WORDPRESS = "finishWpDispatcher";

    /**
     * Nome evento ao iniciar o plugin
     *
     */
    const BEFORE_PLUGIN = "beforePluginDispatcher";

    /**
     * Nome do evento depois de executar o plugin
     *
     */
    const AFTER_PLUGIN = "afterPluginDispatcher";

    /**
     * Nome do evento ao finalizar o plugin
     *
     */
    const FINISH_PLUGIN = "finishPluginDispatcher";

    /**
     * Nome do evento ao receber uma exception do Plugin
     *
     */
    const EXCEPTION_PLUGIN = "exceptionPluginDispatcher";

    /**
     * Nome do evento para ser executado antes da controller
     *
     */
    const BEFORE_CONTROLLER = "beforeControllerDispatcher";

    /**
     * Nome do evento para ser executado depois da controller
     *
     */
    const AFTER_CONTROLLER = "afterControllerDispatcher";

    /**
     * Nome do evento ao finalizar a controller, mesmo com excpetion
     *
     */
    const FINISH_CONTROLLER = "finishControllerDispatcher";

    /**
     * Nome do evento para ser executado ao lançar uma exception da controller
     *
     */
    const EXCEPTION_CONTROLLER = "exceptionControllerDispatcher";

    /**
     * Página do evento, se existir
     *
     * @var string
     */
    protected $pagina;

    /**
     * Váriavel que armazenda o request
     *
     * @var MbRequisicoes
     */
    protected $request;

    /**
     * Váriavel que armazenda a resposta
     *
     * @var MbRespostas
     */
    protected $response;

    /**
     * @return MbRequisicoes
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param MbRequisicoes $request
     *
     * @return MbEventos
     */
    public function setRequest(MbRequisicoes $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return MbRespostas
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param MbRespostas $response
     *
     * @return MbEventos
     */
    public function setResponse(MbRespostas $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return string
     */
    public function getPagina()
    {
        return $this->pagina;
    }

    /**
     * @param string $pagina
     * @return MbEventos
     */
    public function setPagina($pagina)
    {
        $this->pagina = $pagina;
        return $this;
    }

    /**
     * Redirecionar uma página
     *
     * @param string $url
     * @param array $params
     */
    protected final function redirect($url, array $params = [])
    {
        if (is_string($url)) {
            $url .= !empty($params) ? "?" . http_build_query($params) : "";
            $this->response->redirect($url);
        }
    }

    /**
     * Processar evento da página
     *
     * @param MocaBonita $mocaBonita
     * @param string $dispatch
     * @param \Exception $exception
     *
     */
    public function processarEvento(MocaBonita $mocaBonita, $dispatch, \Exception $exception = null)
    {
        $methodExists = method_exists($this, $dispatch);

        if (($this->getPagina() == $mocaBonita->getPage() && $methodExists) || (is_null($this->getPagina()) && $methodExists)) {
            $this->{$dispatch}($mocaBonita->getRequest(), $mocaBonita->getResponse(), $exception);
        }
    }

    /**
     * Processar eventos da página
     *
     * @param MocaBonita $mocaBonita
     * @param string $dispatch
     * @param \Exception $exception
     *
     * @throws MbException
     *
     * @return void
     */
    public static function processarEventos(MocaBonita $mocaBonita, $dispatch, \Exception $exception = null)
    {
        foreach ($mocaBonita->getEventos() as &$evento) {
            $evento->processarEvento($mocaBonita, $dispatch, $exception);
        }
    }
}
