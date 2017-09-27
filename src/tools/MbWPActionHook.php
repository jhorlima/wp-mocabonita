<?php

namespace MocaBonita\tools;

/**
 * Main class of the MocaBonita ActionHook
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
class MbWPActionHook
{

    /**
     * Add an action in wordpress
     *
     * @param string $action
     * @param object $object
     * @param string $method
     */
    public static function addAction($action, $object, $method)
    {
        add_action($action, [$object, $method]);
    }

    /**
     * Add a callback action in wordpress
     *
     * @param string   $action
     * @param \Closure $callback
     */
    public static function addActionCallback($action, \Closure $callback)
    {
        add_action($action, $callback);
    }

    /**
     * Perform a wordpress action
     *
     * @param string $action
     */
    public static function doAction($action)
    {
        do_action($action);
    }

}
