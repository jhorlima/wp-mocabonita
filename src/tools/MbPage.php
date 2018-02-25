<?php

namespace MocaBonita\tools;

use MocaBonita\controller\MbController;
use MocaBonita\MocaBonita;

/**
 * Main class of the MocaBonita Page
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbPage
{
    /**
     * Page name
     *
     * @var string
     */
    private $name;

    /**
     * Stores the capability of the action
     *
     * @var string
     */
    private $capability;

    /**
     * Page slug
     *
     * @var string
     */
    private $slug;

    /**
     * Page dashicon
     *
     * @var string
     */
    private $dashicon;

    /**
     * Page menu position
     *
     * @var int
     */
    private $menuPosition;

    /**
     * Page parent
     *
     * @var MbPage
     */
    private $parentPage;

    /**
     * Remove page from submenu when available
     *
     * @var bool
     */
    private $removePageSubmenu;

    /**
     * Subpages of this page
     *
     * @var MbPage[]
     */
    private $subPages = [];

    /**
     * Store if page is main menu
     *
     * @var bool
     */
    private $mainMenu;

    /**
     * Store if page is subMenu
     *
     * @var bool
     */
    private $subMenu;

    /**
     * Page Controller
     *
     * @var MbController|string
     */
    private $controller;

    /**
     * Check if it is necessary to hide the menu of this page
     *
     * @var bool
     */
    private $hideMenu;

    /**
     * Page actions
     *
     * @var MbAction[]
     */
    private $mbActions = [];

    /**
     * Page asset
     *
     * @var MbAsset
     */
    private $mbAsset;

    /**
     * MbPage construct
     *
     * @param MbPage $parentPage
     * @param bool   $mainMenu
     * @param int    $position
     *
     */
    public function __construct(MbPage $parentPage = null, $mainMenu = true, $position = 1)
    {
        $this->setName("Moça Bonita")
            ->setCapability("manage_options")
            ->setDashicon("dashicons-editor-code")
            ->setHideMenu(false)
            ->setMbAsset(new MbAsset())
            ->setParentPage($parentPage)
            ->setMainMenu($mainMenu)
            ->setSubMenu(!$mainMenu)
            ->setMenuPosition($position)
            ->addMbAction('index');
    }

    /**
     * Create a new MbPage
     *
     * @param string $name
     *
     * @return MbPage
     */
    public static function create($name)
    {
        $mbPage = new self();

        return $mbPage->setName($name);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return MbPage
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setSlug($name);

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
     * @return MbPage
     */
    public function setCapability($capability)
    {
        $this->capability = $capability;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @param bool   $sanitize
     *
     * @return MbPage
     */
    public function setSlug($slug, $sanitize = true)
    {
        $this->slug = $sanitize ? sanitize_title($slug) : $slug;

        return $this;
    }

    /**
     * Get dashicon
     *
     * @return string
     */
    public function getDashicon()
    {
        return $this->dashicon;
    }

    /**
     * Set dashicon
     *
     * @param string $dashicon
     *
     * @return MbPage
     */
    public function setDashicon($dashicon)
    {
        $this->dashicon = $dashicon;

        return $this;
    }

    /**
     * Get menu position
     *
     * @return int
     */
    public function getMenuPosition()
    {
        return $this->menuPosition;
    }

    /**
     * Set menu position
     *
     * @param int $menuPosition
     *
     * @return MbPage
     */
    public function setMenuPosition($menuPosition)
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    /**
     * Get parent page
     *
     * @throws MbException
     *
     * @return MbPage
     */
    public function getParentPage()
    {
        if (is_null($this->parentPage)) {
            throw new MbException("No parent pages found in {$this->getName()}");
        }

        return $this->parentPage;
    }

    /**
     * Set parent page
     *
     * @param MbPage $parentPage
     *
     * @return MbPage
     */
    public function setParentPage(MbPage $parentPage = null)
    {
        $this->parentPage = $parentPage;

        return $this;
    }

    /**
     * Is remove page submenu
     *
     * @return boolean
     */
    public function isRemovePageSubmenu()
    {
        return $this->removePageSubmenu;
    }

    /**
     * Set remove page submenu
     *
     * @param boolean $removePageSubmenu
     *
     * @return MbPage
     */
    public function setRemovePageSubmenu($removePageSubmenu = true)
    {
        $this->removePageSubmenu = $removePageSubmenu;

        return $this;
    }

    /**
     * Get sub pages
     *
     * @return MbPage[]
     */
    public function getSubPages()
    {
        return $this->subPages;
    }

    /**
     * Get sub page
     *
     * @param string $pageSlug
     *
     * @return MbPage|null
     */
    public function getSubPage($pageSlug)
    {
        return $this->subPages[$pageSlug];
    }

    /**
     * Set sub page
     *
     * @param MbPage $mbPage
     *
     * @return MbPage subpage
     */
    public function setSubPage(MbPage $mbPage)
    {
        $this->subPages[$mbPage->getSlug()] = $mbPage;

        $mbPage->setParentPage($this);

        return $mbPage;
    }

    /**
     * Add sub page
     *
     * @param string $name
     * @param string $slug
     *
     * @return MbPage subpage
     */
    public function addSubPage($name, $slug = null)
    {
        $subpage = self::create($name)->setSlug(is_null($slug) ? $name : $slug);

        $this->setSubPage($subpage);

        return $subpage;
    }

    /**
     * Is main menu
     *
     * @return boolean
     */
    public function isMainMenu()
    {
        return $this->mainMenu;
    }

    /**
     * Set main menu
     *
     * @param boolean $mainMenu
     *
     * @return MbPage
     */
    public function setMainMenu($mainMenu = true)
    {
        $this->mainMenu = $mainMenu;

        return $this;
    }

    /**
     * Is submenu
     *
     * @return boolean
     */
    public function isSubMenu()
    {
        return $this->subMenu;
    }

    /**
     * Set submenu
     *
     * @param boolean $subMenu
     *
     * @return MbPage
     */
    public function setSubMenu($subMenu = true)
    {
        $this->subMenu = $subMenu;

        return $this;
    }

    /**
     * Get page controller
     *
     * @throws MbException
     *
     * @return MbController
     */
    public function getController()
    {
        if ($this->controller instanceof MbController) {
            return $this->controller;
        }

        if (is_string($this->controller)) {
            $this->controller = MbController::create($this->controller);
        } else {
            throw new MbException("No Controller has been set for the page {$this->getName()}.");
        }

        return $this->controller;
    }

    /**
     * Set controller
     *
     * @param MbController|string $controller
     *
     * @return MbPage
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Is hide menu
     *
     * @return boolean
     */
    public function isHideMenu()
    {
        return $this->hideMenu;
    }

    /**
     * Set hide menu
     *
     * @param boolean $hideMenu
     *
     * @return MbPage
     */
    public function setHideMenu($hideMenu = true)
    {
        $this->hideMenu = $hideMenu;

        return $this;
    }

    /**
     * Get MbAction
     *
     * @param string $actionName
     *
     * @return MbAction|null
     */
    public function getMbAction($actionName)
    {
        return $this->mbActions[$actionName];
    }

    /**
     * Set MbAction
     *
     * @param MbAction $mbAction
     *
     * @return MbAction
     */
    public function setMbAction(MbAction $mbAction)
    {
        $this->mbActions[$mbAction->getName()] = $mbAction;

        return $mbAction;
    }

    /**
     * add new MbAction
     *
     * @param string                $actionName
     * @param string|callable|mixed $action
     *
     * @return MbAction
     *
     */
    public function addMbAction($actionName, $action = null)
    {
        $mbAction = new MbAction($this, $actionName);
        $mbAction->actionResolver($action);

        return $this->setMbAction($mbAction);
    }

    /**
     * Get MbAsset
     *
     * @return MbAsset
     */
    public function getMbAsset()
    {
        return $this->mbAsset;
    }

    /**
     * Set MbAsset
     *
     * @param MbAsset $mbAsset
     *
     * @return MbPage
     */
    public function setMbAsset(MbAsset $mbAsset)
    {
        $this->mbAsset = $mbAsset;

        return $this;
    }

    /**
     * Add Menu in Wordpress
     *
     * @return void
     * @throws MbException
     */
    public function addMenuWordpress()
    {
        if ($this->isHideMenu()) {

            add_submenu_page(
                null,
                $this->getName(),
                $this->getName(),
                $this->getCapability(),
                $this->getSlug(),
                [MocaBonita::getInstance(), 'sendContent']
            );

        } elseif ($this->isMainMenu()) {

            add_menu_page(
                $this->getName(),
                $this->getName(),
                $this->getCapability(),
                $this->getSlug(),
                [MocaBonita::getInstance(), 'sendContent'],
                $this->getDashicon(),
                $this->getMenuPosition()
            );

        } elseif ($this->isSubMenu()) {

            add_submenu_page(
                $this->getParentPage()->getSlug(),
                $this->getName(),
                $this->getName(),
                $this->getCapability(),
                $this->getSlug(),
                [MocaBonita::getInstance(), 'sendContent']
            );

            if ($this->getParentPage()->isRemovePageSubmenu()) {
                remove_submenu_page($this->getParentPage()->getSlug(), $this->getParentPage()->getSlug());
                $this->getParentPage()->setRemovePageSubmenu(false);
            }
        }

    }
}