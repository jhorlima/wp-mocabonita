<?php

namespace MocaBonita\tools;

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
class Assets
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
     * @return Assets
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
     * @return Assets
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
     */
    public function processarCssWordpress($slug)
    {
        foreach ($this->css as $i => $css)
            wp_enqueue_style("style_mb_{$slug}_{$i}", $css);
    }

    /**
     * Adicionar o JS no Wordpress
     *
     * @param string $slug Slug da página
     */
    public function processarJsWordpress($slug)
    {
        foreach ($this->javascript as $i => $js)
                wp_enqueue_script("script_mb_{$slug}_{$i}", $js['caminho'], [], $js['versao'], $js['footer']);

    }
}
