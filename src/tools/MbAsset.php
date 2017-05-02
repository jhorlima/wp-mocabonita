<?php

namespace MocaBonita\tools;
use MocaBonita\MocaBonita;

/**
 * Classe de Componentes do Wordpress
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\Tools
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class MbAsset
{
    /**
     * Array de CSS
     *
     * @var array
     */
    private $css = [];

    /**
     * Array de Javascript
     *
     * @var array
     */
    private $javascript = [];

    /**
     * Enqueue de Javascript
     *
     * @var string
     */
    private $actionEnqueue;

    /**
     * @return array
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * Adicionar CSS
     *
     * @param string $caminho
     * @return MbAsset
     */
    public function setCss($caminho)
    {
        $this->css[] = $caminho;
        return $this;
    }

    /**
     * @return array
     */
    public function getJavascript()
    {
        return $this->javascript;
    }

    /**
     * Adicionar Javascript
     *
     * @param string $caminho
     * @param bool $noFooter
     * @param bool $versao
     * @return MbAsset
     */
    public function setJavascript($caminho, $noFooter = true, $versao = false)
    {
        $this->javascript[] = [
            'caminho' => $caminho,
            'footer'  => (bool) $noFooter,
            'versao'  => $versao,
        ];

        return $this;
    }

    /**
     * Adicionar o CSS no Wordpress
     *
     * @param string $slug Slug da página
     *
     */
    public function processarAssets($slug)
    {
        $style = $this->css;
        $javascript = $this->javascript;

        MbWPActionHook::adicionarCallbackAction($this->getActionEnqueue(), function () use ($slug, $style, $javascript){
            foreach ($style as $i => $css) {
                wp_enqueue_style("style_mb_{$slug}_{$i}", $css);
            }

            foreach ($javascript as $i => $js) {
                wp_enqueue_script("script_mb_{$slug}_{$i}", $js['caminho'], [], $js['versao'], $js['footer']);
            }
        });
    }

    /**
     * Obter a action do enqueue
     *
     * @return string
     */
    public function getActionEnqueue(){
        if(is_null($this->actionEnqueue)){
            $this->autoEnqueue();
        }

        return $this->actionEnqueue;
    }

    /**
     * Gerar action de acordo com a requisição atual
     *
     */
    private function autoEnqueue(){
        $request = MocaBonita::getInstance()->getMbRequest();

        if($request->isPageLogin()){
            $this->actionEnqueue = "login_enqueue_scripts";
        } elseif ($request->isAdmin()){
            $this->actionEnqueue = "admin_enqueue_scripts";
        } else {
            $this->actionEnqueue = "wp_enqueue_scripts";
        }
    }

    /**
     * Definir em qual action o assets será executado
     *
     * @param $actionEnqueue
     * @return $this
     */
    public function setActionEnqueue($actionEnqueue){
        switch ($actionEnqueue) {
            case 'admin' :
                $this->actionEnqueue = "admin_enqueue_scripts";
                break;
            case 'login' :
                $this->actionEnqueue = "login_enqueue_scripts";
                break;
            default :
                $this->actionEnqueue = "wp_enqueue_scripts";
                break;
        }
        return $this;
    }
}