<?php

namespace MocaBonita\tools;

use Illuminate\Http\Request;
use MocaBonita\tools\eloquent\MbDatabaseQueryBuilder;

/**
 * Gerenciamento de requisições do moça bonita
 *
 * @author Jhordan Lima <jhordan>
 * @category WordPress
 * @package moca_bonita\tools
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class MbRequest extends Request
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
     * Obter objeto da página atual
     *
     * @var MbPage
     */
    protected $pagina;

    /**
     * Obter objeto da ação atual
     *
     * @var MbAction
     */
    protected $acao;

    /**
     * @return boolean
     */
    public function isAdmin()
    {
        if (is_null($this->admin)) {
            $this->admin = (bool)is_admin();
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
        if (is_null($this->login)) {
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
        if (is_null($this->pageLogin)) {
            $this->pageLogin = (bool)in_array($this->getPageNow(), ['wp-login.php', 'wp-register.php']);
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
    public function getPageNow()
    {
        if (is_null($this->pageNow)) {
            $this->pageNow = $GLOBALS['pagenow'];
        }
        return $this->pageNow;
    }

    /**
     * @param string $pageNow
     * @return MbRequest
     */
    public function setPageNow($pageNow)
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
        if (is_null($this->ajax)) {
            $this->ajax = (bool)(defined('DOING_AJAX') && DOING_AJAX);
        }
        return (bool)($this->ajax || $this->isXmlHttpRequest());
    }

    /**
     * Get the full URL for the request with the added a new query string parameters.
     *
     * @param  array $query
     * @return string
     */
    public function fullUrlWithNewQuery(array $query)
    {
        return $this->url() . '?' . http_build_query($query);
    }

    /**
     * Get the full URL with new action for the requests.
     *
     * @param string $action
     * @param array $query
     *
     * @return string
     */
    public function fullUrlWithNewAction($action, array $query = [])
    {
        return $this->url() . '?' . http_build_query([
                    'page' => $this->query('page'),
                    'action' => $action
                ] + $query);
    }

    /**
     * Get the full URL with new action for the requests.
     *
     * @param string $pagination
     * @param array $query
     *
     * @return string
     */
    public function fullUrlWithNewPagination($pagination)
    {
        return $this->url() . '?' . http_build_query($this->query() + [
            MbDatabaseQueryBuilder::getPagination() => $pagination
        ]);
    }

    /**
     * @param string $action
     * @return bool
     */
    public function isAction($action)
    {
        return (bool)$this->query('action') == $action;
    }

    /**
     * @param string $page
     * @return bool
     */
    public function isPage($page)
    {
        return (bool)$this->query('page') == $page;
    }

    /**
     * Retrieve an inputSource item from the request.
     *
     * @param  string $key
     * @param  string|array|null $default
     * @return string|array
     */
    public function inputSource($key = null, $default = null)
    {
        return data_get($this->getInputSource()->all(), $key, $default);
    }

    /**
     * @return MbPage
     */
    public function getPagina()
    {
        return $this->pagina;
    }

    /**
     * @param MbPage $pagina
     */
    public function setPagina(MbPage $pagina)
    {
        $this->pagina = $pagina;
    }

    /**
     * @return MbAction
     */
    public function getAcao()
    {
        return $this->acao;
    }

    /**
     * @param MbAction $acao
     */
    public function setAcao(MbAction $acao)
    {
        $this->acao = $acao;
    }
}