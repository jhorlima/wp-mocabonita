<?php

namespace MocaBonita\tools;

use MocaBonita\MocaBonita;

/**
 * Main class of the MocaBonita Event
 *
 * @author Jhordan Lima <jhorlima@icloud.com>
 * @category WordPress
 * @package \MocaBonita\tools
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 * @version 3.1.0
 */
abstract class MbEvent extends MbSingleton
{
    /**
     * Event name that will start after wordpress initialize
     */
    const START_WORDPRESS = "startWpDispatcher";

    /**
     * Event name that will start before WordPress finishes
     */
    const FINISH_WORDPRESS = "finishWpDispatcher";

    /**
     * Event name that will start before running the page
     */
    const BEFORE_PAGE = "beforePageDispatcher";

    /**
     * Event name that will start after running the page
     */
    const AFTER_PAGE = "afterPageDispatcher";

    /**
     * Event name that will start before finishes the page, even with the exception
     */
    const FINISH_PAGE = "finishPageDispatcher";

    /**
     * Event name that will start after the page launches an exception
     */
    const EXCEPTION_PAGE = "exceptionPageDispatcher";

    /**
     * Event name that will start before running the shortcode
     */
    const BEFORE_SHORTCODE = "beforeShortcodeDispatcher";

    /**
     * Event name that will start after running the shortcode
     */
    const AFTER_SHORTCODE = "afterShortcodeDispatcher";

    /**
     * Event name that will start before running the action
     */
    const BEFORE_ACTION = "beforeActionDispatcher";

    /**
     * Event name that will start after running the action
     */
    const AFTER_ACTION = "afterActionDispatcher";

    /**
     * Event name that will start before finishes the action, even with the exception
     */
    const FINISH_ACTION = "finishActionDispatcher";

    /**
     * Event name that will start after the action launches an exception
     */
    const EXCEPTION_ACTION = "exceptionActionDispatcher";

    /**
     * Call page events
     *
     * @param MocaBonita $mocaBonita
     * @param string $dispatch
     * @param object $complement
     *
     * @throws MbException
     *
     * @return void
     */
    public static function callEvents(MocaBonita $mocaBonita, $dispatch, $complement = null)
    {
        foreach ($mocaBonita->getMbEvents($dispatch) as &$evento) {
            $evento->{$dispatch}($mocaBonita->getMbRequest(), $mocaBonita->getMbResponse(), $complement);
        }
    }

    /**
     * Event that will start after wordpress initialize
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MocaBonita $mocaBonita
     */
    public function startWpDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MocaBonita $mocaBonita)
    {
        //
    }

    /**
     * Event that will start before WordPress finishes
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MocaBonita $mocaBonita
     */
    public function finishWpDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MocaBonita $mocaBonita)
    {
        //
    }

    /**
     * Event that will start before running the page
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MbPage $mbPage
     */
    public function beforePageDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MbPage $mbPage)
    {
        //
    }

    /**
     * Event that will start after running the page
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MbPage $mbPage
     */
    public function afterPageDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MbPage $mbPage)
    {
        //
    }

    /**
     * Event that will start before finishes the page, even with the exception
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MbPage $mbPage
     */
    public function finishPageDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MbPage $mbPage)
    {
        //
    }

    /**
     * Event that will start after the page launches an exception
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param \Exception $exception
     */
    public function exceptionPageDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, \Exception $exception)
    {
        //
    }

    /**
     * Event that will start before running the shortcode
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MbShortCode $mbShortCode
     */
    public function beforeShortcodeDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MbShortCode $mbShortCode)
    {
        //
    }

    /**
     * Event that will start after running the shortcode
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MbShortCode $mbShortCode
     */
    public function afterShortcodeDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MbShortCode $mbShortCode)
    {
        //
    }

    /**
     * Event that will start before running the action
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MbAction $mbAction
     */
    public function beforeActionDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MbAction $mbAction)
    {
        //
    }

    /**
     * Event that will start after running the action
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MbAction $acaombAction
     */
    public function afterActionDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MbAction $acaombAction)
    {
        //
    }

    /**
     * Event that will start before finishes the action, even with the exception
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MbAction $mbAction
     */
    public function finishActionDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MbAction $mbAction)
    {
        //
    }

    /**
     * Event that will start after the action launches an exception
     *
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param \Exception $exception
     */
    public function exceptionActionDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, \Exception $exception)
    {
        //
    }
}