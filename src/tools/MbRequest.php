<?php

namespace MocaBonita\tools;

use Illuminate\Http\Request;
use MocaBonita\tools\eloquent\MbDatabaseQueryBuilder;

/**
 * Main class of the MocaBonita Request
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
class MbRequest extends Request
{

    /**
     * Stores information if it is on a Wordpress administrative page
     *
     * @var boolean
     */
    protected $admin;

    /**
     * Stores information if it is on a Wordpress ajax page
     *
     * @var boolean
     */
    protected $ajax;

    /**
     * Stores information if someone is logged in
     *
     * @var boolean
     */
    protected $logged;

    /**
     * Stores information if it is in a shortcode page of Wordpress
     *
     * @var boolean
     */
    protected $shortcode = false;

    /**
     * Store information if you are on the Wordpress login page
     *
     * @var boolean
     */
    protected $loginPage;

    /**
     * Stores the current wordpress page
     *
     * @var string
     */
    protected $pageNow;

    /**
     * Get current MbPage, if it's available
     *
     * @var MbPage
     */
    protected $mbPage;

    /**
     * Get current MbAction, if it's available
     *
     * @var MbAction
     */
    protected $mbAction;

    /**
     * Is admin
     *
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
     * Is Ajax
     *
     * @return boolean
     */
    public function isAjax()
    {
        return $this->ajax();
    }

    /**
     * is logged
     *
     * @return boolean
     */
    public function isLogged()
    {
        if (is_null($this->logged)) {
            $this->logged = (bool)is_user_logged_in();
        }
        return $this->logged;
    }

    /**
     * Is shortcode
     *
     * @return bool
     */
    public final function isShortcode()
    {
        return $this->shortcode;
    }

    /**
     * Set shortcode
     *
     * @param bool $isShortcode
     */
    public final function setShortcode($isShortcode)
    {
        $this->shortcode = $isShortcode;
    }

    /**
     * Is login page
     *
     * @return boolean
     */
    public function isLoginPage()
    {
        if (is_null($this->loginPage)) {
            $this->loginPage = (bool)in_array($this->getPageNow(), ['wp-login.php', 'wp-register.php']);
        }
        return $this->loginPage;
    }

    /**
     * Get page now
     *
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
     * is ajax request
     *
     * @return boolean
     */
    public function ajax()
    {
        if (is_null($this->ajax)) {
            $this->ajax = (bool)(defined('DOING_AJAX') && DOING_AJAX);
        }
        return (bool)($this->ajax || parent::ajax());
    }

    /**
     * Get the full URL for the request with the added a new query string parameters.
     *
     * @param  array $query
     *
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
    public function fullUrlWithNewAction($action, $query = null)
    {
        $query = is_array($query) ? $query : $this->query();
        $query['action'] = $action;
        return $this->url() . '?' . http_build_query($query);
    }

    /**
     * Get the full URL with new pagination for the requests.
     *
     * @param string $pagination
     * @param array $query
     *
     * @return string
     */
    public function fullUrlWithNewPagination($pagination, $query = null)
    {
        $query = is_array($query) ? $query : $this->query();
        $query[MbDatabaseQueryBuilder::getPagination()] = $pagination;
        return $this->url() . '?' . http_build_query($query);
    }

    /**
     * Is current action
     *
     * @param string $action
     * @return bool
     */
    public function isCurrentAction($action)
    {
        return (bool)$this->query('action') == $action;
    }

    /**
     * Is current page
     *
     * @param string $page
     * @return bool
     */
    public function isCurrentPage($page)
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
     * Get MbPage
     *
     * @return MbPage
     */
    public function getMbPage()
    {
        return $this->mbPage;
    }

    /**
     * Set MbPage
     *
     * @param MbPage $mbPage
     */
    public function setMbPage(MbPage $mbPage)
    {
        $this->mbPage = $mbPage;
    }

    /**
     * Get MbAction
     *
     * @return MbAction
     */
    public function getMbAction()
    {
        return $this->mbAction;
    }

    /**
     * Set MbAction
     *
     * @param MbAction $mbAction
     */
    public function setMbAction(MbAction $mbAction)
    {
        $this->mbAction = $mbAction;
    }
}