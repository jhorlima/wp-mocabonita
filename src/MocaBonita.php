<?php

namespace MocaBonita;

use Illuminate\Support\Collection;
use MocaBonita\audit\MbAudit;
use MocaBonita\model\MbWpUser;
use MocaBonita\tools\eloquent\MbDatabase;
use MocaBonita\tools\eloquent\MbDatabaseQueryBuilder;
use MocaBonita\tools\MbPageStructure;
use MocaBonita\tools\MbPath;
use MocaBonita\tools\MbMigration;
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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Main class of the MocaBonita framework
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 * @version   3.3.3
 */
final class MocaBonita extends MbSingleton
{
    /**
     * Current version of MocaBonita.
     */
    const VERSION = "3.3.3";

    /**
     * List of MocaBonita Pages
     *
     * @var Collection
     */
    private $mbPages;

    /**
     * List of MocaBonita Events
     *
     * @var Collection
     */
    private $mbEvents;

    /**
     * List of MocaBonita Shortcodes
     *
     * @var Collection
     */
    private $mbShortCodes;

    /**
     * List of MocaBonita Assets
     *
     * @var Collection
     */
    private $mbAssets;

    /**
     * Check that the plugin will be audited
     *
     * @var boolean
     */
    private $audit;

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
     * Stores the current audit
     *
     * @var MbAudit
     */
    private $mbAudit;

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
     * @param bool $wordpress If it's true, then it'll return the wordpress' MbAsset. If it's false, then it'll return
     *                        the plugin's MbAsset.
     *
     * @return MbAsset
     */
    public function getMbAssets($wordpress = false)
    {
        return $wordpress ? $this->mbAssets->get('wordpress') : $this->mbAssets->get('plugin');
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
     * @param bool    $wordpress If it's true, then it'll set MbAssets to the Wordpress. If it's false, then it'll set
     *                           MbAssets to the plugin.
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function setMbAssets(MbAsset $mbAsset, $wordpress = false)
    {
        $this->mbAssets->put($wordpress ? 'wordpress' : 'plugin', $mbAsset);

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
     * @param string|null $dispatch If it's a string, then it'll return either an array of MbEvent or an empty array.
     *                              If it's null, then it'll return all stored MbEvent
     *
     * @return Collection
     */
    public function getMbEvents($dispatch = null)
    {
        return $this->mbEvents->get($dispatch, []);
    }

    /**
     * Set a MbEvent to a dispatcher type
     *
     * @param MbEvent      $mbEvent
     * @param string|array $dispatch name of dispatcher
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function setMbEvent(MbEvent $mbEvent, $dispatch)
    {
        if (is_array($dispatch)) {
            foreach ($dispatch as $event) {
                $this->setMbEvent($mbEvent, $event);
            }
        } else {
            if (!$this->mbEvents->has($dispatch)) {
                $this->mbEvents->put($dispatch, new Collection());
            }

            $this->mbEvents->get($dispatch)->push($mbEvent);
        }

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
     *
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
     *
     * @return MocaBonita current instance of MocaBonita
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return MbAudit
     */
    public function getMbAudit()
    {
        return $this->mbAudit;
    }

    /**
     * @param MbAudit $mbAudit
     *
     * @return MocaBonita
     */
    public function setMbAudit($mbAudit)
    {
        $this->mbAudit = $mbAudit;

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

        $mbAudit = new MbAudit();
        $mbRequest = MbRequest::capture();
        $mbResponse = MbResponse::create();

        $mbRequest->ajax();

        $mbRequest->setBlogAdmin(is_blog_admin());

        $mbAudit->setRequest($mbRequest);
        $mbResponse->setMbRequest($mbRequest);
        $mbResponse->setMbAudit($mbAudit);

        $this->setPage($mbRequest->query('page'));
        $this->setAction($mbRequest->query('action', 'index'));

        $this->setMbRequest($mbRequest);
        $this->setMbResponse($mbResponse);
        $this->setMbAudit($mbAudit);

        $this->mbAssets = new Collection([
            'plugin'    => new MbAsset(),
            'wordpress' => new MbAsset(),
        ]);

        $this->mbEvents = new Collection();
        $this->mbShortCodes = new Collection();
        $this->mbPages = new Collection();

        MbMigration::enableWpdbConnection();
    }

    /**
     * Enable session
     *
     * @param Session $session
     *
     * @return MocaBonita
     */
    public function enableSession($session = null)
    {
        $session = $session instanceof Session ?: new Session();
        $this->getMbRequest()->setSession($session);

        return $this;
    }

    /**
     * Enable flash session
     *
     * @param Session $session
     *
     * @return MocaBonita
     */
    public function enableFlash($session = null)
    {
        if (!$this->getMbRequest()->hasSession()) {
            $this->enableSession($session);
        }

        $this->getMbRequest()->session()->getFlashBag();

        return $this;
    }

    /**
     * Set the callback that has the plugin's structure
     *
     * @param        $pluginStructure \Closure Callback that will be called
     * @param bool   $audit           Set status audit
     *
     * @param string $tag
     *
     * @return void
     */
    public static function plugin(\Closure $pluginStructure, $audit = false, $tag = "plugins_loaded")
    {
        $mocaBonita = self::getInstance();
        $mocaBonita->audit = (bool) $audit;

        if ($mocaBonita->audit) {

            MbWPActionHook::addActionCallback('plugins_loaded', function () use ($mocaBonita) {
                try {
                    MbDatabase::enableQueryLog();
                    $mocaBonita->getMbAudit()->setMocaBonita($mocaBonita);
                    $mocaBonita->getMbAudit()->setUser(MbWpUser::getCurrentUser(true));
                } catch (\Exception $e) {
                    $mocaBonita->getMbResponse()->setContent($e);
                }
            });

            MbWPActionHook::addActionCallback('shutdown', function () use ($mocaBonita) {
                try {
                    $mocaBonita->getMbAudit()->run();
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                } finally {
                    MbDatabase::disableQueryLog();
                }
            });

        }

        MbWPActionHook::addActionCallback($tag, function () use ($pluginStructure, $mocaBonita) {
            try {
                call_user_func_array($pluginStructure, [$mocaBonita]);
                $mocaBonita->runPlugin();
            } catch (\Exception $e) {
                $mocaBonita->mbResponse->setContent($e);
            } finally {
                $mocaBonita->runHookCurrentAction();
                $mocaBonita->getMbAudit()->setResponseStatusCode($mocaBonita->mbResponse->status());
                $mocaBonita->getMbAudit()->setResponseHeader($mocaBonita->mbResponse->headers->all());
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

        register_activation_hook(MbPath::pBaseN(), function () use ($active, $mocaBonita) {
            try {
                MbMigration::enablePdoConnection();
                call_user_func_array($active, [$mocaBonita]);
            } catch (\Exception $e) {
                deactivate_plugins(basename(MbPath::pBaseN()));
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

        register_deactivation_hook(MbPath::pBaseN(), function () use ($deactive, $mocaBonita) {
            try {
                MbMigration::enablePdoConnection();
                call_user_func_array($deactive, [$mocaBonita]);
            } catch (\Exception $e) {
                MbException::registerError($e);
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
            MbMigration::enablePdoConnection();
            call_user_func_array($unistall, [$mocaBonita]);
        } else {
            wp_die("Você não pode executar este método fora do arquivo uninstall.php");
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
        //Call MbEvent from wordpress (START_WORDPRESS)
        MbEvent::callEvents($this, MbEvent::START_WORDPRESS, $this);

        if (!$this->getMbResponse()->isRedirection()) {

            //Call the MbAsset from WordPress
            $this->getMbAssets(true)->runAssets('*');

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
            $urlWihtouPagination = $this->mbRequest->urlQuery($paramsQuery);

            //Set url without pagination query to the Paginator Resolver
            Paginator::currentPathResolver(function () use ($urlWihtouPagination) {
                return $urlWihtouPagination;
            });

            //Set current pagination to the Paginator Resolver
            Paginator::currentPageResolver(function () use ($pagination) {
                return is_numeric($pagination) ? (int) $pagination : 1;
            });

            //Call the Shortcode from plugin
            foreach ($this->mbShortCodes as $shortcode) {
                $shortcode->runShortcode($this->getMbAssets(), $this->mbRequest, $this->mbResponse);
            }

            //Add wordpress administrative menu if needed
            if ($this->getMbRequest()->isBlogAdmin()) {
                MbWPActionHook::addAction('admin_menu', $this, 'addAdminMenuToWordpress');
            }

            if ($this->isMocabonitaPage()) {

                //Get current MbPage
                $mbPage = $this->getMbPage($this->page);

                //Set current MbPage to MbRequest
                $this->getMbRequest()->setMbPage($mbPage);

                try {

                    //Call MvEvent from page (BEFORE_PAGE)
                    MbEvent::callEvents($this, MbEvent::BEFORE_PAGE, $mbPage);

                    //Call the MbAsset from plugin
                    $this->getMbAssets()->runAssets('plugin');

                    //Call the MbAsset from page
                    $mbPage->getMbAsset()->runAssets($this->page);

                    //Run current page
                    $this->runCurrentPage($mbPage);

                    //Call MvEvent from page (AFTER_PAGE)
                    MbEvent::callEvents($this, MbEvent::AFTER_PAGE, $mbPage);
                } catch (\Exception $e) {
                    //Call MvEvent from page (EXCEPTION_PAGE)
                    MbEvent::callEvents($this, MbEvent::EXCEPTION_PAGE, $e);
                    throw $e;
                } finally {
                    //Call MvEvent from page (FINISH_PAGE)
                    MbEvent::callEvents($this, MbEvent::FINISH_PAGE, $mbPage);
                }
            }

        }

        //Call MbEvent from wordpress (FINISH_WORDPRESS)
        MbEvent::callEvents($this, MbEvent::FINISH_WORDPRESS, $this);

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
        //Get MbAction from current action
        $mbAction = $mbPage->getMbAction($this->action);

        //Check if MbAction is invalid
        if (is_null($mbAction)) {
            $mbAction = $mbPage->addMbAction($this->action);

            if (!$mbAction->functionExist()) {
                throw new MbException(
                    "The action {$this->action} was not instantiated in " . MbPage::class . " of the page {$this->page}!"
                );
            }
        }

        $requiredParams = $mbAction->getRequiredParams();

        //Set capability of page if the capability of MbAction is not defined
        if (is_null($mbAction->getCapability())) {
            $mbAction->setCapability($mbPage->getCapability());
        }

        //Check if MbAction requires login and if there is any user logged in
        if ($mbAction->isRequiresLogin() && !$this->mbRequest->isLogged()) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} requires wordpress login!"
            );
        } //Check if MbAction capability is allowed
        elseif ($mbAction->isRequiresLogin() && !current_user_can($mbAction->getCapability())) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} requires a user with more access permissions!"
            );
        } //Check if MbAction requires a MbRequest ajax
        elseif ($mbAction->isRequiresAjax() && !$this->mbRequest->isAjax()) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} needs to be requested in admin-ajax.php!"
            );
        } //Check if the method request defined in MbAction is allowed
        elseif (!is_null($mbAction->getRequiresMethod()) && $mbAction->getRequiresMethod() != $this->mbRequest->method()) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} must be called by request method {$mbAction->getRequiresMethod()}!"
            );
        } //Check if the method request defined in MbAction is allowed
        elseif (!empty($requiredParams) && !$this->mbRequest->hasQuery($requiredParams)) {
            throw new MbException(
                "The action {$this->action} of the page {$this->page} requires the parameters " . implode(', ', $requiredParams) . "!"
            );
        }

        ob_start();

        try {

            MbEvent::callEvents($this, MbEvent::BEFORE_ACTION, $mbAction);

            //Set page parameter to View
            $mbView = new MbView();

            $mbView->setView('index', $this->page, $this->action);

            ob_start();
            $actionResponse = $this->runAction($mbAction, $mbView, [
                $this->mbRequest,
                $this->mbResponse,
                $mbView,
            ]);
            $buffer = (ob_get_contents());
            ob_end_clean();


            if($this->mbResponse->isBuffer()){
                if($this->getMbRequest()->isBlogAdmin() || $this->getMbRequest()->isAjax() || $this->getMbRequest()->isShortcode()) {
                    throw new MbException("the buffer is only available for admin-post.php");
                } else {
                    $actionResponse = $buffer;
                }
            }

            MbEvent::callEvents($this, MbEvent::AFTER_ACTION, $mbAction);

        } catch (\Exception $e) {
            MbEvent::callEvents($this, MbEvent::EXCEPTION_ACTION, $e);
            $actionResponse = $e;
        } finally {
            MbEvent::callEvents($this, MbEvent::FINISH_ACTION, $mbAction);
        }

        if(!$this->mbResponse->isRedirection()) {

            if (is_null($actionResponse) && !$this->getMbRequest()->isAjax()) {
                $actionResponse = $mbAction->getMbPage()->getController()->getMbView();
            }

            $this->mbResponse->setContent($actionResponse);
        }
    }

    /**
     * @param MbAction $mbAction
     *
     * @param MbView   $mbView
     * @param array    $callbackParameters
     *
     * @return mixed
     * @throws MbException
     */
    public function runAction(MbAction $mbAction, MbView $mbView, array $callbackParameters = [])
    {
        $this->mbRequest->setMbAction($mbAction);

        //Check if has data to return
        if (!is_null($mbAction->getData())) {
            return $mbAction->getData();
        } else {

            //Check if has callback to return
            if ($mbAction->getCallback() instanceof \Closure) {

                return call_user_func_array($mbAction->getCallback(), $callbackParameters);

                //Check if has function to return
            } elseif ($mbAction->functionExist()) {

                //Set MbRequest and MbResponse to current controller of MbAction
                $mbAction->getMbPage()
                    ->getController()
                    ->setMbRequest($this->mbRequest)
                    ->setMbResponse($this->mbResponse);

                //Set the MbView e actionResolver to Controller
                $mbAction->getMbPage()
                    ->getController()
                    ->actionResolver($mbAction);

                if (is_null($mbAction->getMbPage()->getController()->getMbView())) {
                    $mbAction->getMbPage()->getController()->setMbView($mbView);
                }

                $mbAction->getMbPage()
                    ->getController()
                    ->getMbView()
                    ->setMbRequest($this->mbRequest)
                    ->setMbResponse($this->mbResponse);

                return call_user_func_array(
                    [$mbAction->getMbPage()->getController(), $mbAction->getFunction(),],
                    $callbackParameters
                );

            } else {
                throw new MbException(
                    "The action {$this->action} of the page {$this->page} does not have a public method in the controller. " .
                    "Please create or make public the method {$mbAction->getFunction()}!"
                );
            }
        }
    }

    /**
     * Add Wordpress Hook for current action if needed
     *
     * @return boolean
     */
    private function runHookCurrentAction()
    {
        //Check if needed add the hook
        if (!$this->getMbRequest()->isMocaBonitaPage() || $this->getMbRequest()->isBlogAdmin()) {
            return false;
        }

        //Check if a user is logged in Wordpress
        if ($this->mbRequest->isLogged()) {
            //Check if the current request is ajax
            if ($this->mbRequest->isAjax()) {
                //add hook admin_ajax
                $actionHook = "wp_ajax_{$this->getAction()}";
            } else {
                //add hook admin_post
                $actionHook = "admin_post_{$this->getAction()}";
            }

        } else {
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
        MbWPActionHook::addAction($actionHook, $this, 'sendContent');

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
     * Check if the current page is a Mocabonita page
     *
     * @return bool
     */
    protected function isMocabonitaPage()
    {
        if (is_null($this->page)) {
            $this->getMbRequest()->setMocaBonitaPage(false);

            return false;
        }

        if (is_null($this->getMbRequest()->isMocaBonitaPage())) {
            $this->getMbRequest()->setMocaBonitaPage($this->mbPages->has($this->page));
        }

        return $this->getMbRequest()->isMocaBonitaPage();
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
        if (!$this->mbPages->has($slugPage)) {
            throw new MbException("The page {$slugPage} has not been added to MocaBonita's list of pages!");
        }

        return $this->mbPages->get($slugPage);
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
        if (!$this->mbShortCodes->has($shortcodeName)) {
            throw new MbException("The shortcode {$shortcodeName} has not been added to the MocaBonita shortcode list!");
        }

        return $this->mbShortCodes->get($shortcodeName);
    }

    /**
     * Add page structure
     *
     * @param                 $pageName
     * @param MbPageStructure $mbPageStructure
     */
    public function addMbPageStructure($pageName, MbPageStructure $mbPageStructure)
    {
        $mbPageStructure->setMbPage(MbPage::create($pageName));
        $mbPageStructure->enablePage();
        $mbPageStructure->execute($this->getMbRequest(), $this->getMbResponse(), $this);
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
        $mbPage->setSubMenu(false);

        $mbPage->setMainMenu(true);

        $this->mbPages->put($mbPage->getSlug(), $mbPage);

        foreach ($mbPage->getSubPages() as $subPage) {
            $this->addSubMbPage($subPage);
            $subPage->setParentPage($mbPage);
        }

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
        $mbPage->setMainMenu(false);

        $mbPage->setSubMenu(true);

        $this->mbPages->put($mbPage->getSlug(), $mbPage);

        return $this;
    }

    /**
     * Add a MbShortCode to MocaBonita
     *
     * @param string                $name
     * @param MbPage                $mbPage
     * @param string|callable|mixed $method
     *
     * @return MbShortCode
     */
    public function addMbShortcode($name, MbPage $mbPage, $method)
    {
        $shortcode = new MbShortCode($name, $mbPage, $method);

        $this->mbShortCodes->put($name, $shortcode);

        return $shortcode;
    }

    /**
     * Add admin menu to Wordpress
     *
     * @return void
     */
    public function addAdminMenuToWordpress()
    {
        foreach ($this->mbPages as $page) {
            $page->addMenuWordpress();
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