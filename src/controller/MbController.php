<?php

namespace MocaBonita\controller;

use MocaBonita\tools\MbAction;
use MocaBonita\tools\MbPage;
use MocaBonita\tools\MbResponse;
use MocaBonita\tools\MbRequest;
use MocaBonita\tools\MbException;
use MocaBonita\view\MbView;

/**
 * Main class of the MocaBonita Controller
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\controller
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 **/
abstract class MbController
{

    /**
     * Stores the current MbView of the action
     *
     * @var MbView
     */
    protected $mbView;

    /**
     * Stores the current MbRequest of the request
     *
     * @var MbRequest
     */
    protected $mbRequest;

    /**
     * Stores the current MbResponse of the response
     *
     * @var MbResponse
     */
    protected $mbResponse;

    /**
     * Example of index action
     *
     * If the return is null, then it will call MbView from this controller and then render it (Common request)
     * If the return is null, then it will print a message saying "No valid content has been submitted!" (Ajax Request)
     *
     * If the return is string, then it will print the string (Common request)
     * If the return is string, then it will add the string in the key "content" on the data (Ajax Request)
     *
     * If the return is MbView, then it will render it (Common request)
     * If the return is MbView, then it will print a message saying "No valid content has been submitted!" (Ajax
     * Request)
     *
     * If the return is mixed, then it will call a var_dump of the returned value (Common request)
     * If the return is mixed, then it will print a message saying "No valid content has been submitted!" (Ajax
     * Request)
     *
     * @param MbRequest  $mbRequest
     * @param MbResponse $mbResponse
     *
     * @return null|string|MbView|mixed
     */
    public function indexAction(MbRequest $mbRequest, MbResponse $mbResponse)
    {
        return $this->mbView;
    }

    /**
     * Get MbRequest
     *
     * @return MbRequest
     */
    public function getMbRequest()
    {
        return $this->mbRequest;
    }

    /**
     * Set MbRequest to Controller
     *
     * @param MbRequest $mbRequest
     *
     * @return MbController
     */
    public function setMbRequest(MbRequest $mbRequest)
    {
        $this->mbRequest = $mbRequest;

        return $this;
    }

    /**
     * Get MbResponse
     *
     * @return MbResponse
     */
    public function getMbResponse()
    {
        return $this->mbResponse;
    }

    /**
     * Set MbResponse to Controller
     *
     * @param MbResponse $mbResponse
     *
     * @return MbController
     */
    public function setMbResponse(MbResponse $mbResponse)
    {
        $this->mbResponse = $mbResponse;

        return $this;
    }

    /**
     * Get MbView
     *
     * @return MbView
     */
    public final function getMbView()
    {
        return $this->mbView;
    }

    /**
     * Set MbView to Controller
     *
     * @param MbView $mbView
     *
     * @return MbController
     */
    public final function setMbView(MbView $mbView)
    {
        $this->mbView = $this->viewResolver($mbView);

        return $this;
    }

    /**
     * resolver view of controller
     *
     * @param MbView $mbView
     *
     * @return MbView
     */
    public function viewResolver(MbView $mbView)
    {
        return $mbView;
    }

    /**
     * Controller Factory
     *
     * @param string $className
     *
     * @throws MbException When the controller is not an instance of MbController
     *
     * @return MbController
     */
    public static function create($className)
    {
        $controller = new $className();

        if (!$controller instanceof MbController) {
            throw new MbException("The Controller {$className} is not an instance of MbController!");
        }

        return $controller;
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     *
     * @throws MbException
     */
    public function __call($method, $parameters)
    {
        $className = static::class;
        throw new MbException("Method [{$method}] does not exist in {$className}.");
    }

    /**
     * resolver action of controller
     *
     * @param MbAction $mbAction
     *
     * @return void
     */
    public function actionResolver(MbAction $mbAction)
    {
        //
    }
}
