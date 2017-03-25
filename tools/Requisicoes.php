<?php

namespace MocaBonita\tools;

use Illuminate\Http\Request;

/**
 * Gerenciamento de requisições do moça bonita
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package moca_bonita\tools
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class Requisicoes extends Request
{

    /**
     * Contém a informação se está em uma página administrativa do Wordpress
     *
     * @var boolean
     */
    protected $admin;

    /**
     * Contém a informação se está em uma página ajax do Wordpress
     *
     * @var boolean
     */
    protected $ajax;

    /**
     * Contém a informação se alguém está logado
     *
     * @var boolean
     */
    protected $login;

    /**
     * Contém a informação se está em uma página shortcode do Wordpress
     *
     * @var boolean
     */
    protected $shortcode = false;

    /**
     * Contém a informação se está na página de login do Wordpress
     *
     * @var boolean
     */
    protected $pageLogin;

    /**
     * Contém a página atual do wordpress
     *
     * @var string
     */
    protected $pageNow;

    /**
     * @return boolean
     */
    public function isAdmin()
    {
        if(is_null($this->admin)){
            $this->admin = (bool) is_admin();
        }
        return (bool)$this->admin;
    }

    /**
     * Verificar se a requisição é ajax
     *
     * @return true|false true se a requisição for ajax, false se a requisição não for ajax
     */
    public function isAjax()
    {
        return $this->ajax();
    }

    /**
     * @return boolean
     */
    public function isLogin()
    {
        if(is_null($this->login)){
            $this->login = (bool)is_user_logged_in();
        }
        return $this->login;
    }

    /**
     * @return bool
     */
    public final function isShortcode()
    {
        return $this->shortcode;
    }

    /**
     * @param bool $isShortcode
     */
    public final function setShortcode($isShortcode)
    {
        $this->shortcode = $isShortcode;
    }

    /**
     * @return boolean
     */
    public function isPageLogin()
    {
        if(is_null($this->pageLogin)){
            $this->pageLogin = (bool) in_array($this->getPageNow(), ['wp-login.php', 'wp-register.php']);
        }
        return $this->pageLogin;
    }

    /**
     * @param boolean $pageLogin
     */
    public function setPageLogin($pageLogin)
    {
        $this->pageLogin = $pageLogin;
    }

    /**
     * @return string
     */
    public function getPageNow(): string
    {
        if (is_null($this->pageNow)){
            $this->pageNow = $GLOBALS['pagenow'];
        }
        return $this->pageNow;
    }

    /**
     * @param string $pageNow
     * @return Requisicoes
     */
    public function setPageNow(string $pageNow): Requisicoes
    {
        $this->pageNow = $pageNow;
        return $this;
    }

    /**
     * Verificar se a requisição é ajax
     *
     * @return true|false true se a requisição for ajax, false se a requisição não for ajax
     */
    public function ajax()
    {
        if(is_null($this->ajax)){
            $this->ajax = (bool)(defined('DOING_AJAX') && DOING_AJAX);
        }
        return (bool) ($this->ajax || $this->isXmlHttpRequest());
    }

}