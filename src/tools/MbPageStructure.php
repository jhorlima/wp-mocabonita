<?php

namespace MocaBonita\tools;

use MocaBonita\MocaBonita;

/**
 * Main class of the MocaBonita Page Structure
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools
 *
 * @copyright Jhordan Lima 2018
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
abstract class MbPageStructure extends MbSingleton
{

    /**
     * @var MocaBonita
     */
    protected $mocaBonita;

    /**
     * @var MbRequest
     */
    protected $mbRequest;

    /**
     * @var MbResponse
     */
    protected $mbResponse;

    /**
     * @var MbPage
     */
    protected $mbPage;

    /**
     * @var bool
     */
    protected $enablePage;

    /**
     * @return MocaBonita
     */
    public function getMocaBonita()
    {
        return $this->mocaBonita;
    }

    /**
     * @param MocaBonita $mocaBonita
     *
     * @return MbPageStructure
     */
    public function setMocaBonita(MocaBonita $mocaBonita)
    {
        $this->mocaBonita = $mocaBonita;

        return $this;
    }

    /**
     * @return MbRequest
     */
    public function getMbRequest()
    {
        return $this->mbRequest;
    }

    /**
     * @param MbRequest $mbRequest
     *
     * @return MbPageStructure
     */
    public function setMbRequest(MbRequest $mbRequest)
    {
        $this->mbRequest = $mbRequest;

        return $this;
    }

    /**
     * @return MbResponse
     */
    public function getMbResponse()
    {
        return $this->mbResponse;
    }

    /**
     * @param MbResponse $mbResponse
     *
     * @return MbPageStructure
     */
    public function setMbResponse(MbResponse $mbResponse)
    {
        $this->mbResponse = $mbResponse;

        return $this;
    }

    /**
     * @return MbPage
     */
    public function getMbPage()
    {
        return $this->mbPage;
    }

    /**
     * @param MbPage $mbPage
     *
     * @return MbPageStructure
     */
    public function setMbPage(MbPage $mbPage)
    {
        $this->mbPage = $mbPage;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnablePage()
    {
        return $this->enablePage;
    }

    /**
     * @return MbPageStructure
     */
    public function enablePage()
    {
        $this->enablePage = true;

        return $this;
    }

    /**
     * @return MbPageStructure
     */
    public function disablePage()
    {
        $this->enablePage = false;

        return $this;
    }

    /**
     * @param MbPage $mbPage
     *
     * @return void
     */
    abstract public function structure(MbPage $mbPage);

    /**
     * @param MbRequest  $mbRequest
     * @param MbResponse $mbResponse
     * @param MocaBonita $mocaBonita
     */
    final public function execute(MbRequest $mbRequest, MbResponse $mbResponse, MocaBonita $mocaBonita)
    {
        $this->setMbRequest($mbRequest);
        $this->setMbResponse($mbResponse);
        $this->setMocaBonita($mocaBonita);

        $this->structure($this->getMbPage());

        if ($this->isEnablePage()) {
            $this->getMocaBonita()->addMbPage($this->getMbPage());
        }
    }

    /**
     * Add a MbShortCode to MocaBonita
     *
     * @param string                $shortcode
     * @param string|callable|mixed $action
     *
     * @return MbShortCode
     */
    public function shortCode($shortcode, $action)
    {
        return $this->getMocaBonita()->addMbShortcode($shortcode, $this->getMbPage(), $action);
    }
}