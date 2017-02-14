<?php

namespace MocaBonita\tools;

use MocaBonita\MocaBonita;

/**
 * Classe do WPAction do Wordpress.
 *
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\Tools
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class WPAction {

	/**
    * Adicionar uma action no wordpress
    *
    * @param string $action nome da action
    * @param MocaBonita $object Class
    * @param string $method Método da class
    */
	public static function adicionarAction($action, MocaBonita $object, $method){
		add_action($action, [$object, $method]);
	}

	/**
	 * Adicionar uma action callback no wordpress
	 *
	 * @param string $action nome da action
	 * @param \Closure $callback nome do callback para executar
	 */
	public static function adicionarCallbackAction($action, \Closure $callback){
		add_action($action, $callback);
	}

	/**
    * Executar uma action do wordpress
    *
    * @param string $action nome da action
    */
	public static function realizarAction($action){
		do_action($action);
	}

}
