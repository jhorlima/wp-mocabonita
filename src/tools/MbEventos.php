<?php

namespace MocaBonita\tools;

use MocaBonita\MocaBonita;

/**
 * Classe de gerenciamento de eventos do moçabonita.
 *
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\service
 * @copyright Copyright (c) 2017
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
     * Nome evento ao iniciar a página
     *
     */
    const BEFORE_PAGE = "beforePageDispatcher";

    /**
     * Nome do evento depois de executar a página
     *
     */
    const AFTER_PAGE = "afterPageDispatcher";

    /**
     * Nome do evento ao finalizar a página
     *
     */
    const FINISH_PAGE = "finishPageDispatcher";

    /**
     * Nome do evento ao receber uma exception do página
     *
     */
    const EXCEPTION_PAGE = "exceptionPageDispatcher";

    /**
     * Nome evento ao iniciar o shortcode
     *
     */
    const BEFORE_SHORTCODE = "beforeShortcodeDispatcher";

    /**
     * Nome do evento depois de executar o shortcode
     *
     */
    const AFTER_SHORTCODE = "afterShortcodeDispatcher";

    /**
     * Nome do evento para ser executado antes da action
     *
     */
    const BEFORE_ACTION = "beforeActionDispatcher";

    /**
     * Nome do evento para ser executado depois da action
     *
     */
    const AFTER_ACTION = "afterActionDispatcher";

    /**
     * Nome do evento ao finalizar a action, mesmo com excpetion
     *
     */
    const FINISH_ACTION = "finishActionDispatcher";

    /**
     * Nome do evento para ser executado ao lançar uma exception da action
     *
     */
    const EXCEPTION_ACTION = "exceptionActionDispatcher";

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
     * Processar eventos da página
     *
     * @param MocaBonita $mocaBonita
     * @param string $dispatch
     * @param object $exceptionOrPage
     *
     * @throws MbException
     *
     * @return void
     */
    public static function processarEventos(MocaBonita $mocaBonita, $dispatch, $exceptionOrPage = null)
    {
        foreach ($mocaBonita->getEventos($dispatch) as &$evento) {
            $evento->{$dispatch}($mocaBonita->getRequest(), $mocaBonita->getResponse(), $exceptionOrPage);
        }
    }

    /**
     * Evento para ser executado antes do wordpress processar o plugin (Executado sempre quando o plugin for ativado)
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     */
    public function startWpDispatcher(MbRequisicoes $request, MbRespostas $response)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar o plugin (Executado sempre quando o plugin for ativado)
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     */
    public function finishWpDispatcher(MbRequisicoes $request, MbRespostas $response)
    {
        //
    }

    /**
     * Evento para ser executado antes do wordpress processar a página (Somente em páginas da página)
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param MbAcoes $acao
     */
    public function beforePageDispatcher(MbRequisicoes $request, MbRespostas $response, MbAcoes $acao)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a página, caso não tenha exceptions na página
     * Somente em páginas do plugin
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param MbAcoes $acao
     */
    public function afterPageDispatcher(MbRequisicoes $request, MbRespostas $response, MbAcoes $acao)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a página
     * Somente em páginas do plugin
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param MbAcoes $acao
     */
    public function finishPageDispatcher(MbRequisicoes $request, MbRespostas $response, MbAcoes $acao)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a página e seja lançada uma exception
     * Somente em páginas do plugin
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param \Exception $exception
     */
    public function exceptionPageDispatcher(MbRequisicoes $request, MbRespostas $response, \Exception $exception)
    {
        //
    }

    /**
     * Evento para ser executado antes do wordpress processar o shortcode
     * Somente em shortcodes
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param MbShortCode $shortCode
     */
    public function beforeShortcodeDispatcher(MbRequisicoes $request, MbRespostas $response, MbShortCode $shortCode)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar o shortcode
     * Somente em shortcodes
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param MbShortCode $shortCode
     */
    public function afterShortcodeDispatcher(MbRequisicoes $request, MbRespostas $response, MbShortCode $shortCode)
    {
        //
    }

    /**
     * Evento para ser executado antes do wordpress processar a action da página
     * Somente em páginas do plugin
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param MbAcoes $acao
     */
    public function beforeActionDispatcher(MbRequisicoes $request, MbRespostas $response, MbAcoes $acao)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a action da página, caso não tenha exceptions na action
     * Somente em páginas do plugin
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param MbAcoes $acao
     */
    public function afterActionDispatcher(MbRequisicoes $request, MbRespostas $response, MbAcoes $acao)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a action
     * Somente em páginas do plugin
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param MbAcoes $acao
     */
    public function finishActionDispatcher(MbRequisicoes $request, MbRespostas $response, MbAcoes $acao)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a action e seja lançada uma exception
     * Somente em páginas do plugin
     *
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     * @param \Exception $exception
     */
    public function exceptionActionDispatcher(MbRequisicoes $request, MbRespostas $response, \Exception $exception)
    {
        //
    }
}
