<?php

namespace MocaBonita;

use MocaBonita\tools\eloquent\MbDatabaseQueryBuilder;
use MocaBonita\tools\MbPath;
use MocaBonita\tools\MbCapsule;
use MocaBonita\tools\MbResponse;
use MocaBonita\tools\MbRequest;
use MocaBonita\tools\MbEvent;
use MocaBonita\tools\MbAction;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbShortCode;
use MocaBonita\tools\MbAsset;
use MocaBonita\tools\MbPage;
use MocaBonita\tools\MbSingleton;
use MocaBonita\tools\MbWPActionHook;
use MocaBonita\view\MbView;
use Illuminate\Pagination\Paginator;

/**
 * Main class of the MocaBonita framework
 *
 * @author Jhordan Lima <jhorlima@icloud.com>
 * @category WordPress
 * @package \MocaBonita
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 * @version 3.1.0
 */
final class MocaBonita extends MbSingleton
{
    /**
     * Current version of MocaBonita.
     *
     */
    const VERSION = "3.1.0";

    /**
     * List of MocaBonita Pages
     *
     * @var MbPage[]
     */
    private $mbPages = [];

    /**
     * List of MocaBonita Events
     *
     * @var array[]
     */
    private $mbEvents = [];

    /**
     * List of MocaBonita Shortcodes
     *
     * @var MbShortCode[]
     */
    private $mbShortCodes = [];

    /**
     * List of MocaBonita Assets
     *
     * @var MbAsset[]
     */
    private $mbAssets;

    /**
     * Checks if the current page is a page of MocaBonita
     *
     * @var boolean
     */
    private $mocabonitaPage;

    /**
     * Checks if the plugin is being developed
     *
     * @var boolean
     */
    private $development;

    /**
     * Checks if the current page is one of the blog's admin pages
     *
     * @var boolean
     */
    private $blogAdmin;

    /**
     * Stores the current MbRequest of the request
     *
     * @var MbRequest
     */
    private $mbRequest;

    /**
     * Stores the current MbResponse of the response
     *
     * @var MbResponse
     */
    private $mbResponse;

    /**
     * Stores the current name of the wordpress page
     *
     * @var string
     */
    private $page;

    /**
     * Stores the current name of the wordpress action
     *
     * @var string
     */
    private $action;

    /**
     * Get current version of MocaBonita
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Get either MbAsset from the plugin or from Wordpress
     *
     * @param bool $wordpress If it's true, then it'll return the wordpress' MbAsset. If it's false, then it'll return the plugin's MbAsset.
     *
     * @return MbAsset
     */
    public function getMbAssets($wordpress = false)
    {
        return $wordpress ? $this->mbAssets['wordpress'] : $this->mbAssets['plugin'];
    }

    /**
     * Get the plugin's MbAsset
     *
     * @return MbAsset
     */
    public function getAssetsPlugin()
    {
        return $this->getMbAssets();
    }

    /**
     * Get the Wordpress' MbAsset
     *
     * @return MbAsset
     */
    public function getAssetsWordpress()
    {
        return $this->getMbAssets(true);
    }

    /**
     * Set either MbAsset to the plugin or to the Wordpress
     *
     * @param MbAsset $mbAsset
     * @param bool $wordpress If it's true, then it'll set MbAssets to the Wordpress. If it's false, then it'll set MbAssets to the plugin.
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function setMbAssets(MbAsset $mbAsset, $wordpress = false)
    {
        $this->mbAssets[$wordpress ? 'wordpress' : 'plugin'] = $mbAsset;
        return $this;
    }

    /**
     * Set the MbAsset to the plugin
     *
     * @param MbAsset $pluginMbAsset
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function setAssetsPlugin(MbAsset $pluginMbAsset)
    {
        return $this->setMbAssets($pluginMbAsset);
    }

    /**
     * Set the MbAsset to the Wordpress
     *
     * @param MbAsset $wordpressMbAsset
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function setAssetsWordpress(MbAsset $wordpressMbAsset)
    {
        return $this->setMbAssets($wordpressMbAsset, true);
    }

    /**
     * Get either a MbEvent from a dispatcher type or the MbEvent list
     *
     * @param string|null $dispatch If it's a string, then it'll return either an array of MbEvent or an empty array. If it's null, then it'll return all stored MbEvent
     *
     * @return array|MbEvent[]
     */
    public function getMbEvents($dispatch = null)
    {
        if (is_null($dispatch)) {
            return $this->mbEvents;
        } elseif (isset($this->mbEvents[$dispatch])) {
            return $this->mbEvents[$dispatch];
        } else {
            return [];
        }
    }

    /**
     * Set a MbEvent to a dispatcher type
     *
     * @param MbEvent $mbEvent
     * @param string|array $dispatch name of dispatcher
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function setMbEvent(MbEvent $mbEvent, $dispatch)
    {
        if (is_array($dispatch)) {
            foreach ($dispatch as $item) {
                $this->setMbEvent($mbEvent, $item);
            }
        } else {
            if (!isset($this->mbEvents[$dispatch])) {
                $this->mbEvents[$dispatch] = [];
            }
            $this->mbEvents[$dispatch][] = $mbEvent;
        }
        return $this;
    }

    /**
     * Checks if the current page is one of the blog's admin pages
     *
     * @return boolean
     */
    public function isBlogAdmin()
    {
        return $this->blogAdmin;
    }

    /**
     * Set if the current page is one of the blog's admin pages
     *
     * @param boolean $blogAdmin
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function setBlogAdmin($blogAdmin)
    {
        $this->blogAdmin = $blogAdmin;
        return $this;
    }

    /**
     * Get the current name of the wordpress page
     *
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set the current name of the wordpress page
     *
     * @param string $page
     * @return MocaBonita current instance of MocaBonita
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Get the current name of the wordpress action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the current name of the wordpress action
     *
     * @param string $action
     * @return MocaBonita current instance of MocaBonita
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Function that's called when MocaBonita is started.
     *
     * @return void
     */
    protected function init()
    {
        if (!defined('ABSPATH')) {
            die('MocaBonita must be loaded along with Wordpress!' . PHP_EOL);
        }

        $timezone = get_option('timezone_string');

        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }

        $this->setMbRequest(MbRequest::capture());
        $this->setMbResponse(MbResponse::create());
        $this->getMbResponse()->setRequest($this->mbRequest);
        $this->setPage($this->mbRequest->query('page'));
        $this->setAction($this->mbRequest->query('action'));
        $this->setBlogAdmin(is_blog_admin());

        $this->mbAssets = [
            'plugin'    => new MbAsset(),
            'wordpress' => new MbAsset(),
        ];

        $this->mbEvents = [];

        MbCapsule::wpdb();
    }

    /**
     * Set the callback that has the plugin's structure
     *
     * @param $pluginStructure \Closure Callback that will be called
     * @param bool $development Set status development of the plugin
     *
     * @return void
     */
    public static function plugin(\Closure $pluginStructure, $development = false)
    {
        $mocaBonita = self::getInstance();
        $mocaBonita->development = (bool) $development;

        if ($development) {
            $mocaBonita->desableCaches();
        }

        MbWPActionHook::adicionarCallbackAction('plugins_loaded', function () use ($pluginStructure, $mocaBonita) {
            try {
                $pluginStructure($mocaBonita);
                $mocaBonita->runPlugin();
            } catch (\Exception $e) {
                $mocaBonita->mbResponse->setContent($e);
            } finally {
                $mocaBonita->runHookCurrentAction();
                $mocaBonita->mbResponse->sendHeaders();
            }
        });
    }

    /**
     * Set the callback that will be called when the plugin is activated
     *
     * @param $active \Closure Callback that will be called
     *
     * @return void
     */
    public static function active(\Closure $active)
    {
        $mocaBonita = self::getInstance();

        register_activation_hook(MbPath::PLUGIN_BASENAME, function () use ($active, $mocaBonita) {
            try {
                self::checkApplication();
                MbCapsule::pdo();
                $active($mocaBonita);
            } catch (\Exception $e) {
                deactivate_plugins(basename(MbPath::PLUGIN_BASENAME));
                wp_die($e->getMessage());
            }
        });
    }

    /**
     * Set the callback that will be called when the plugin is deactivated
     *
     * @param $deactive \Closure Callback that will be called
     *
     * @return void
     */
    public static function deactive(\Closure $deactive)
    {
        $mocaBonita = self::getInstance();

        register_deactivation_hook(MbPath::PLUGIN_BASENAME, function () use ($deactive, $mocaBonita) {
            try {
                MbCapsule::pdo();
                $deactive($mocaBonita);
            } catch (\Exception $e) {
                MbException::setSalvarLog(true);
                MbException::adminNotice($e);
                wp_die($e->getMessage());
            }
        });
    }

    /**
     * Set the callback that will be called when the plugin is uninstalling
     *
     * @param $unistall \Closure Callback that will be called
     *
     * @return void
     */
    public static function uninstall(\Closure $unistall)
    {
        if (defined('WP_UNINSTALL_PLUGIN')) {
            $mocaBonita = self::getInstance();
            MbCapsule::pdo();
            $unistall($mocaBonita);
        } else {
            wp_die("Você não pode executar este método fora do arquivo uninstall.php");
        }
    }

    /**
     * Check the Mocabonita requirements to activate the plugin
     *
     * @return void
     */
    private static function checkApplication()
    {
        $exception = null;

        if (version_compare(PHP_VERSION, '5.6', '<') || version_compare(get_bloginfo('version'), '4.5', '<')) {
            $exception = new \Exception(
                "Your PHP or WP is outdated and some MocaBonita features may not work!"
            );
        } elseif (!is_writable(MbPath::PLUGIN_DIRETORIO)) {
            $exception = new \Exception(
                "MocaBonita does not have write permission in the plugin directory!"
            );
        }

        if($exception instanceof \Exception){
            MbException::adminNotice($exception);

            MbWPActionHook::adicionarCallbackAction('init', function () {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                deactivate_plugins(MbPath::PLUGIN_BASENAME);
            });
        }
    }

    /**
     * Initialize the processing of the plugin and its resources.
     *
     * @throws \Exception
     *
     * @return void
     */
    private function runPlugin()
    {
        //Call the MbAsset from WordPress
        $this->getMbAssets(true)->processarAssets('*');

        //Call the Shortcode from plugin
        foreach ($this->mbShortCodes as $shortcode) {
            $shortcode->processarShorcode($this->getMbAssets(), $this->mbRequest, $this->mbResponse);
        }

        //Add wordpress administrative menu if needed
        if ($this->isBlogAdmin()) {
            MbWPActionHook::adicionarAction('admin_menu', $this, 'processarMenu');
        }

        //Call MbEvent from wordpress (START_WORDPRESS)
        MbEvent::processarEventos($this, MbEvent::START_WORDPRESS);

        if ($this->isMocabonitaPage()) {

            //Get current MbPage
            $mbPage = $this->getMbPage($this->page);

            //Set current MbPage to MbRequest
            $this->getMbRequest()->setPagina($mbPage);

            try {

                //Call MvEvent from page (BEFORE_PAGE)
                MbEvent::processarEventos($this, MbEvent::BEFORE_PAGE, $mbPage);

                //Get all query params from url
                $paramsQuery = $this->mbRequest->query();

                //Check if there is a pagination attribute
                if (isset($paramsQuery[MbDatabaseQueryBuilder::getPagination()])) {
                    $pagination = $paramsQuery[MbDatabaseQueryBuilder::getPagination()];
                    unset($paramsQuery[MbDatabaseQueryBuilder::getPagination()]);
                } else {
                    $pagination = 1;
                }

                //Get url without pagination query
                $urlWihtouPagination = $this->mbRequest->fullUrlWithNewQuery($paramsQuery);

                //Set url without pagination query to the Paginator Resolver
                Paginator::currentPathResolver(function () use ($urlWihtouPagination) {
                    return $urlWihtouPagination;
                });

                //Set current pagination to the Paginator Resolver
                Paginator::currentPageResolver(function () use ($pagination) {
                    return is_numeric($pagination) ? (int)$pagination : 1;
                });

                //Call the MbAsset from plugin
                $this->getMbAssets()->processarAssets('plugin');

                //Call the MbAsset from page
                $mbPage->getAssets()->processarAssets($this->page);

                //Run current page
                $this->runCurrentPage($mbPage);

                //Call MvEvent from page (AFTER_PAGE)
                MbEvent::processarEventos($this, MbEvent::AFTER_PAGE, $mbPage);
            } catch (\Exception $e) {
                //Call MvEvent from page (EXCEPTION_PAGE)
                MbEvent::processarEventos($this, MbEvent::EXCEPTION_PAGE, $e);
                throw $e;
            } finally {
                //Call MvEvent from page (FINISH_PAGE)
                MbEvent::processarEventos($this, MbEvent::FINISH_PAGE, $mbPage);
            }
        }
        //Call MbEvent from wordpress (FINISH_WORDPRESS)
        MbEvent::processarEventos($this, MbEvent::FINISH_WORDPRESS);
    }

    /**
     * Execute current page resources
     *
     * @param MbPage $mbPage
     *
     * @throws MbException
     */
    private function runCurrentPage(MbPage $mbPage)
    {
        //Get controller name of MbPage
        $controllerName = get_class($mbPage->getController());

        //Get MbAction from current action
        $mbAction = $mbPage->getAcao($this->action);

        //Check if MbAction is invalid
        if (is_null($mbAction)) {
            throw new MbException(
                "The action {$this->action} was not instantiated in " . MbPage::class . " of the page {$this->page}!"
            );
        }
        //Set capability of page if the capability of MbAction is not defined
        elseif (is_null($mbAction->getCapability())) {
            $mbAction->setCapability($mbPage->getCapability());
        }

        //Check if MbAction requires login and if there is any user logged in
        if ($mbAction->isLogin() && !$this->mbRequest->isLogin()) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} requires wordpress login!"
            );
        }
        //Check if MbAction capability is allowed
        elseif ($mbAction->isLogin() && !current_user_can($mbAction->getCapability())) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} requires a user with more access permissions!"
            );
        }
        //Check if MbAction requires a MbRequest ajax
        elseif ($mbAction->isAjax() && !$this->mbRequest->isAjax()) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} needs to be requested in admin-ajax.php!"
            );
        }
        //Check if the method request defined in MbAction is allowed
        elseif ($mbAction->getRequisicao() != $this->mbRequest->method() && !is_null($mbAction->getRequisicao())) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} must be called by request method {$mbAction->getRequisicao()}!"
            );
        }
        //Check if the method the MbAction exist in Controller
        elseif (!$mbAction->metodoValido()) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} does not have a public method in the controller {$controllerName}. " .
                "Please create or make public the method {$mbAction->getMetodo()}!"
            );
        }

        //Set current MbAction to MbRequest
        $this->getMbRequest()->setAcao($mbAction);

        //Set page parameter to View
        $mbView = new MbView();

        $mbView->setRequest($this->mbRequest)
            ->setResponse($this->mbResponse)
            ->setView('index', $this->page, $this->action);

        //Set the MbView to Controller
        $mbAction->getPagina()
            ->getController()
            ->setView($mbView);

        //Set MbRequest and MbResponse to current controller of MbAction
        $mbAction->getPagina()
            ->getController()
            ->setRequest($this->mbRequest)
            ->setResponse($this->mbResponse);

        ob_start();

        try {
            MbEvent::processarEventos($this, MbEvent::BEFORE_ACTION, $mbAction);

            //Execute method of controller
            $actionResponse = $mbAction->getPagina()
                ->getController()
                ->{$mbAction->getMetodo()}($this->mbRequest, $this->mbResponse);
            MbEvent::processarEventos($this, MbEvent::AFTER_ACTION, $mbAction);

        } catch (\Exception $e) {
            MbEvent::processarEventos($this, MbEvent::EXCEPTION_ACTION, $e);
            $actionResponse = $e;
        } finally {
            MbEvent::processarEventos($this, MbEvent::FINISH_ACTION, $mbAction);
            $controllerPrint = ob_get_contents();
        }

        ob_end_clean();

        if ($controllerPrint != "") {
            error_log($controllerPrint);
        }

        if (is_null($actionResponse) && !$this->mbRequest->isAjax()) {
            $actionResponse = $mbAction->getPagina()->getController()->getView();
        }

        $this->mbResponse->setContent($actionResponse);
    }

    /**
     * Add Wordpress Hook for current action if needed
     *
     * @return boolean
     */
    private function runHookCurrentAction()
    {
        //Check if needed add the hook
        if (!$this->isMocabonitaPage() || $this->isBlogAdmin()) {
            return false;
        }

        //Check if a user is logged in Wordpress
        if ($this->mbRequest->isLogin()) {
            //Check if the current request is ajax
            if ($this->mbRequest->isAjax()) {
                //add hook admin_ajax
                $actionHook = "wp_ajax_{$this->getAction()}";
            } else {
                //add hook admin_post
                $actionHook = "admin_post_{$this->getAction()}";
            }

        }
        else {
            //Check if the current request is ajax
            if ($this->mbRequest->isAjax()) {
                //add hook nopriv_ajax
                $actionHook = "wp_ajax_nopriv_{$this->getAction()}";
            } else {
                //add hook nopriv_post
                $actionHook = "admin_post_nopriv_{$this->getAction()}";
            }
        }

        //Register WordpressHook
        MbWPActionHook::adicionarAction($actionHook, $this, 'sendContent');
        return true;
    }

    /**
     * Send the content generated by the plugin
     *
     * @return void
     */
    public function sendContent()
    {
        $this->mbResponse->sendContent();
    }

    /**
     * Disable any type of page cache during access in development mode
     *
     * @return void
     */
    private function desableCaches()
    {
        $this->mbResponse
            ->header("Cache-Control", "no-cache, no-store, must-revalidate")
            ->header("Pragma", "no-cache")
            ->header("Expires", "0");
    }

    /**
     * Check if the current page is a Mocabonita page
     *
     * @return bool
     */
    public function isMocabonitaPage()
    {
        if (is_null($this->page)) {
            return false;
        }

        if (is_null($this->mocabonitaPage)) {
            $this->mocabonitaPage = in_array($this->page, array_keys($this->mbPages));
        }

        if ($this->mocabonitaPage && is_null($this->action)) {
            $query = http_build_query([
                'page'   => $this->page,
                'action' => 'index',
            ]);
            $url = admin_url($this->mbRequest->getPageNow()) . "?" . $query;
            $this->mbResponse->redirect($url);
        }

        return $this->mocabonitaPage;
    }

    /**
     * Get MbPage of slug
     *
     * @param string $slugPage slug of the MbPage
     *
     * @throws MbException
     *
     * @return MbPage
     */
    public function getMbPage($slugPage)
    {
        if (!isset($this->mbPages[$slugPage])) {
            throw new MbException("The page {$slugPage} has not been added to MocaBonita's list of pages!");
        }

        return $this->mbPages[$slugPage];
    }

    /**
     * Get MbShortcode of name
     *
     * @param string $shortcodeName Name of shortcode
     *
     * @throws MbException
     *
     * @return MbShortCode
     */
    public function getMbShortcode($shortcodeName)
    {
        if (!isset($this->mbShortCodes[$shortcodeName])) {
            throw new MbException("The shortcode {$shortcodeName} has not been added to the MocaBonita shortcode list!");
        }

        return $this->mbShortCodes[$shortcodeName];
    }

    /**
     * Add a MbPage to MocaBonita as main menu
     *
     * @param MbPage $mbPage
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function addMbPage(MbPage $mbPage)
    {
        $mbPage->setSubmenu(false);

        $mbPage->setMenuPrincipal(true);

        $this->mbPages[$mbPage->getSlug()] = $mbPage;

        foreach ($mbPage->getSubPaginas() as $subPagina) {
            $subPagina->setPaginaParente($mbPage);
            $this->addSubMbPage($subPagina);
        }

        $mbPage->setMocaBonita($this);

        return $this;
    }

    /**
     * Add a MbPage to MocaBonita as submenu
     *
     * @param MbPage $mbPage
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function addSubMbPage(MbPage $mbPage)
    {
        $mbPage->setMenuPrincipal(false);

        $mbPage->setSubmenu(true);

        $this->mbPages[$mbPage->getSlug()] = $mbPage;

        $mbPage->setMocaBonita($this);

        return $this;
    }

    /**
     * Add a MbShortCode to MocaBonita
     *
     * @param string $name
     * @param MbPage $mbPage
     * @param string $method
     * @param MbAsset $mbAsset
     *
     * @return MbShortCode
     */
    public function addMbShortcode($name, MbPage $mbPage, $method, MbAsset $mbAsset = null)
    {
        $mbAction = new MbAction($mbPage, $method);

        $mbAction->setShortcode(true)->setComplemento('Shortcode');

        $this->mbShortCodes[$name] = new MbShortCode($name, $mbAction, is_null($mbAsset) ? new MbAsset() : $mbAsset);

        return $this->mbShortCodes[$name];
    }

    /**
     * Add admin menu to Wordpress
     *
     * @return void
     */
    public function addAdminMenuToWordpress()
    {
        foreach ($this->mbPages as $pagina) {
            $pagina->adicionarMenuWordpress();
        }
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
     * Set MbRequest to MocaBonita
     *
     * @param MbRequest $mbRequest
     *
     * @return MocaBonita current instance of MocaBonita
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
     * Set MbResponse to MocaBonita
     *
     * @param MbResponse $mbResponse
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function setMbResponse(MbResponse $mbResponse)
    {
        $this->mbResponse = $mbResponse;

        return $this;
    }

}