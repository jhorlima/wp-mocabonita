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
abstract class MbEvent extends MbSingleton
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
     * @var MbRequest
     */
    protected $request;

    /**
     * Váriavel que armazenda a resposta
     *
     * @var MbResponse
     */
    protected $response;

    /**
     * @return MbRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param MbRequest $request
     *
     * @return MbEvent
     */
    public function setRequest(MbRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return MbResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param MbResponse $response
     *
     * @return MbEvent
     */
    public function setResponse(MbResponse $response)
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
        foreach ($mocaBonita->getMbEvents($dispatch) as &$evento) {
            $evento->{$dispatch}($mocaBonita->getMbRequest(), $mocaBonita->getMbResponse(), $exceptionOrPage);
        }
    }

    /**
     * Evento para ser executado antes do wordpress processar o plugin (Executado sempre quando o plugin for ativado)
     *
     * @param MbRequest $request
     * @param MbResponse $response
     */
    public function startWpDispatcher(MbRequest $request, MbResponse $response)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar o plugin (Executado sempre quando o plugin for ativado)
     *
     * @param MbRequest $request
     * @param MbResponse $response
     */
    public function finishWpDispatcher(MbRequest $request, MbResponse $response)
    {
        //
    }

    /**
     * Evento para ser executado antes do wordpress processar a página (Somente em páginas da página)
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param MbPage $paginas
     */
    public function beforePageDispatcher(MbRequest $request, MbResponse $response, MbPage $paginas)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a página, caso não tenha exceptions na página
     * Somente em páginas do plugin
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param MbPage $paginas
     */
    public function afterPageDispatcher(MbRequest $request, MbResponse $response, MbPage $paginas)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a página
     * Somente em páginas do plugin
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param MbPage $paginas
     */
    public function finishPageDispatcher(MbRequest $request, MbResponse $response, MbPage $paginas)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a página e seja lançada uma exception
     * Somente em páginas do plugin
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param \Exception $exception
     */
    public function exceptionPageDispatcher(MbRequest $request, MbResponse $response, \Exception $exception)
    {
        //
    }

    /**
     * Evento para ser executado antes do wordpress processar o shortcode
     * Somente em shortcodes
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param MbShortCode $shortCode
     */
    public function beforeShortcodeDispatcher(MbRequest $request, MbResponse $response, MbShortCode $shortCode)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar o shortcode
     * Somente em shortcodes
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param MbShortCode $shortCode
     */
    public function afterShortcodeDispatcher(MbRequest $request, MbResponse $response, MbShortCode $shortCode)
    {
        //
    }

    /**
     * Evento para ser executado antes do wordpress processar a action da página
     * Somente em páginas do plugin
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param MbAction $acao
     */
    public function beforeActionDispatcher(MbRequest $request, MbResponse $response, MbAction $acao)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a action da página, caso não tenha exceptions na action
     * Somente em páginas do plugin
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param MbAction $acao
     */
    public function afterActionDispatcher(MbRequest $request, MbResponse $response, MbAction $acao)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a action
     * Somente em páginas do plugin
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param MbAction $acao
     */
    public function finishActionDispatcher(MbRequest $request, MbResponse $response, MbAction $acao)
    {
        //
    }

    /**
     * Evento para ser executado depois do wordpress processar a action e seja lançada uma exception
     * Somente em páginas do plugin
     *
     * @param MbRequest $request
     * @param MbResponse $response
     * @param \Exception $exception
     */
    public function exceptionActionDispatcher(MbRequest $request, MbResponse $response, \Exception $exception)
    {
        //
    }
}
