<?php

namespace MocaBonita\tools;

/**
 * Main class of the MocaBonita ActionPage
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
class MbAction
{
    /**
     * Current MbPage
     *
     * @var MbPage
     */
    private $mbPage;

    /**
     * Action name
     *
     * @var string
     */
    private $name;

    /**
     * Check if action needs login
     *
     * @var bool
     */
    private $requiresLogin;

    /**
     * Check if action needs ajax
     *
     * @var bool
     */
    private $requiresAjax;

    /**
     * Requisition method required
     *
     * @var string
     */
    private $requiresMethod;

    /**
     * Controller function name
     *
     * @var string
     */
    private $functionName;

    /**
     * Function complement name
     *
     * @var string
     */
    private $functionComplement;

    /**
     * Check if action is a shortcode
     *
     * @var bool
     */
    private $shortcode;

    /**
     * Stores the capability of the action
     *
     * @var string
     */
    private $capability;

    /**
     * Get MbPage
     *
     * @return MbPage
     *
     * @throws MBException
     */
    public function getMbPage()
    {
        if (is_null($this->mbPage)){
            throw new MbException("Nenhuma página foi definida para essa ação!");
        }

        return $this->mbPage;
    }

    /**
     * Set MbPage
     *
     * @param MbPage $mbPage
     *
     * @return MbAction
     */
    public function setMbPage(MbPage $mbPage)
    {
        $this->mbPage = $mbPage;
        return $this;
    }

    /**
     * Get action name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set action name
     *
     * @param string $name
     * @return MbAction
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Is requires login
     *
     * @return boolean
     */
    public function isRequiresLogin()
    {
        return $this->requiresLogin;
    }

    /**
     * Set requires login
     *
     * @param boolean $requiresLogin
     *
     * @return MbAction
     */
    public function setRequiresLogin($requiresLogin = true)
    {
        $this->requiresLogin = $requiresLogin;
        return $this;
    }

    /**
     * Is requires ajax
     *
     * @return boolean
     */
    public function isRequiresAjax()
    {
        return $this->requiresAjax;
    }

    /**
     * Set requires ajax
     *
     * @param boolean $requiresAjax
     * @return MbAction
     */
    public function setRequiresAjax($requiresAjax = true)
    {
        $this->requiresAjax = $requiresAjax;
        return $this;
    }

    /**
     * Get requires method
     *
     * @return string
     */
    public function getRequiresMethod()
    {
        return $this->requiresMethod;
    }

    /**
     * Set requires method, if it's null, then will be agreed all method
     *
     * @param string|null $requiresMethod
     *
     * @return MbAction
     */
    public function setRequiresMethod($requiresMethod = "GET")
    {
        $this->requiresMethod = $requiresMethod;
        return $this;
    }

    /**
     * Get function name
     *
     * @return string
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }

    /**
     * Set function name
     *
     * @param string $functionName
     * @return MbAction
     */
    public function setFunctionName($functionName)
    {
        $this->functionName = $functionName;
        return $this;
    }

    /**
     * Get function complement
     *
     * @return string
     */
    public function getFunctionComplement()
    {
        return !is_null($this->functionComplement) ? $this->functionComplement : "";
    }

    /**
     * Set function complement, if it's null, then will be removed the complement of the function
     *
     * @param string|null $functionComplement
     *
     * @return MbAction
     */
    public function setFunctionComplement($functionComplement = null)
    {
        $this->functionComplement = $functionComplement;
        return $this;
    }

    /**
     * Get function name with complement
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->getFunctionName() . $this->getFunctionComplement();
    }

    /**
     * Is shortcode
     *
     * @return boolean
     */
    public function isShortcode()
    {
        return $this->shortcode;
    }

    /**
     * Set shortcode
     *
     * @param boolean $shortcode
     * @return MbAction
     */
    public function setShortcode($shortcode = true)
    {
        $this->shortcode = $shortcode;
        return $this;
    }

    /**
     * Get capability
     *
     * @return string
     */
    public function getCapability()
    {
        return $this->capability;
    }

    /**
     * Set capability
     *
     * @param string $capability
     *
     * @return MbAction
     */
    public function setCapability($capability = "read")
    {
        $this->capability = $capability;
        return $this;
    }

    /**
     * Action construct.
     *
     * @param MbPage $mbPage
     * @param string $actionName
     * @param bool $requiresLogin
     * @param bool $requiresAjax
     * @param string $requiresMethod
     */
    public function __construct(MbPage $mbPage, $actionName, $requiresLogin = true, $requiresAjax = false, $requiresMethod = null)
    {
        $this->setMbPage($mbPage)
            ->setName($actionName)
            ->setFunctionName($actionName)
            ->setRequiresLogin($requiresLogin)
            ->setRequiresAjax($requiresAjax)
            ->setRequiresMethod($requiresMethod)
            ->setFunctionComplement('Action')
            ->setShortcode(false)
            ->setCapability(null);
    }

    /**
     * Check if the function exist
     *
     * @return boolean
     */
    public function functionExist()
    {
        return method_exists($this->mbPage->getController(), $this->getFunction());
    }
}