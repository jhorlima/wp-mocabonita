<?php

namespace MocaBonita\tools;

define('mb_plg_name'  , explode('/',  plugin_basename(__FILE__))[0]);
define('mb_plg_base'  , mb_plg_name . "/index.php");
define('mb_plg_path'  , WP_PLUGIN_DIR . "/" . mb_plg_name);
define('mb_plg_url'   , WP_PLUGIN_URL . "/" . mb_plg_name);
define('mb_plg_view'  , mb_plg_path . '/view/');
define('mb_plg_js'    , mb_plg_url  . '/public/js/');
define('mb_plg_css'   , mb_plg_url  . '/public/css/');
define('mb_plg_images', mb_plg_url  . '/public/images/');
define('mb_plg_fonts' , mb_plg_url  . '/public/fonts/');
define('mb_plg_bower' , mb_plg_url  . '/public/bower_components/');


/**
 *
 * Main class of the MocaBonita Path
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
class MbPath {

    /**
     * Constant that stores the URL of the plugin
     *
     * @var string
     *
     * @deprecate
     */
    const PLUGIN_URL = mb_plg_url;

    /**
     * Constant that stores the base name of the plugin
     *
     * @var string
     *
     * @deprecate
     */
    const PLUGIN_BASENAME = mb_plg_base;

    /**
     * Constant that stores the plugin name
     *
     * @var string
     *
     * @deprecate
     */
    const PLUGIN_NAME = mb_plg_name;

    /**
     * Constant that stores the plugin directory
     *
     * @var string
     *
     * @deprecate
     */
    const PLUGIN_DIRECTORY = mb_plg_path;

    /**
     * Constant that stores the plugin's view directory
     *
     * @var string
     *
     * @deprecate
     */
    const PLUGIN_VIEW_DIR = mb_plg_view;

    /**
     * Constant that stores the plugin's javascript directory
     *
     * @var string
     *
     * @deprecate
     */
    const PLUGIN_JS_DIR = mb_plg_js;

    /**
     * Constant that stores the css directory of the plugin
     *
     * @var string
     *
     * @deprecate
     */
    const PLUGIN_CSS_DIR = mb_plg_css;

    /**
     * Constant that stores directory images of the plugin
     *
     * @var string
     *
     * @deprecate
     */
    const PLUGIN_IMAGES_DIR = mb_plg_images;

    /**
     * Constant storing directory bower_components of plugin
     *
     * @var string
     *
     * @deprecate
     */
    const PLUGIN_BOWER_DIR = mb_plg_bower;

    /**
     * Get plugin directory
     *
     * @param $directory
     *
     * @return string
     */
    public static function pDir($directory = "")
    {
        return self::PLUGIN_DIRECTORY . $directory;
    }

    /**
     * Get plugin view directory
     *
     * @param $directory
     *
     * @return string
     */
    public static function pViewDir($directory = "")
    {
        return self::PLUGIN_VIEW_DIR . $directory;
    }

    /**
     * Get Js directory of the plugin
     *
     * @param $directory
     *
     * @return string
     */
    public static function pJsDir($directory = "")
    {
        return self::PLUGIN_JS_DIR . $directory;
    }

    /**
     * Get plugin css directory
     *
     * @param $directory
     *
     * @return string
     */
    public static function pCssDir($directory = "")
    {
        return self::PLUGIN_CSS_DIR . $directory;
    }

    /**
     * Get plugin images directory
     *
     * @param $directory
     *
     * @return string
     */
    public static function pImgDir($directory = "")
    {
        return self::PLUGIN_IMAGES_DIR . $directory;
    }

    /**
     * Get plugin directory bower_components
     *
     * @param $directory
     *
     * @return string
     */
    public static function pBwDir($directory = "")
    {
        return self::PLUGIN_BOWER_DIR . $directory;
    }
}