<?php

namespace MocaBonita\tools;

use MocaBonita\controller\MbController;
use MocaBonita\MocaBonita;

/**
 * Classe de páginas do Wordpress
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\Tools
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class MbPage
{
    /**
     * Nome da página
     *
     * @var string
     */
    private $nome;

    /**
     * Capacidade da página
     *
     * @var string
     */
    private $capability;

    /**
     * Slug da página
     *
     * @var string
     */
    private $slug;

    /**
     * Ícone da página
     *
     * @var string
     */
    private $icone;

    /**
     * Posição da página no menu
     *
     * @var int
     */
    private $posicao;

    /**
     * Página Parente
     *
     * @var MbPage
     */
    private $paginaParente;

    /**
     * Remover página do submenu quando houver
     *
     * @var bool
     */
    private $removerSubMenuPagina;

    /**
     * Lista de Páginas
     *
     * @var MbPage[]
     */
    private $subPaginas = [];

    /**
     * Verificar se é página do menu principal
     *
     * @var bool
     */
    private $menuPrincipal;

    /**
     * Verificar se é página do submenu do wordpress
     *
     * @var bool
     */
    private $submenu;

    /**
     * Objeto Moca Bonita
     *
     * @var MocaBonita
     */
    private $mocaBonita;

    /**
     * Controller da página
     *
     * @var MbController|string
     */
    private $controller;

    /**
     * Verificar se é necessário esconder o menu dessa página
     *
     * @var bool
     */
    private $esconderMenu;

    /**
     * Ações da página
     *
     * @var MbAction[]
     */
    private $acoes = [];

    /**
     * Complementos da página
     *
     * @var MbAsset
     */
    private $assets;

    /**
     * Construir uma página a partir do parente
     *
     * @param MbPage $paginaParente
     * @param bool $menuPrincipal
     * @param int $posicao
     */
    public function __construct(MbPage $paginaParente = null, $menuPrincipal = true, $posicao = 100)
    {
        $this->setNome("Moça Bonita")
            ->setCapability("manage_options")
            ->setIcone("dashicons-editor-code")
            ->setEsconderMenu(false)
            ->setAssets(new MbAsset())
            ->setPaginaParente($paginaParente)
            ->setMenuPrincipal($menuPrincipal)
            ->setSubmenu(!$menuPrincipal)
            ->setPosicao($posicao)
            ->adicionarAcao('index');
    }

    /**
     * @param string $nome Nome da página a ser criada
     *
     * @return MbPage
     */
    public static function create($nome){
        $pagina = new self();

        return $pagina->setNome($nome);
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     * @return MbPage
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
        $this->setSlug($nome);
        return $this;
    }

    /**
     * @return string
     */
    public function getCapability()
    {
        return $this->capability;
    }

    /**
     * @param string $capability
     * @return MbPage
     */
    public function setCapability($capability)
    {
        $this->capability = $capability;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return MbPage
     */
    public function setSlug($slug)
    {
        $this->slug = sanitize_title($slug);
        return $this;
    }

    /**
     * @return string
     */
    public function getIcone()
    {
        return $this->icone;
    }

    /**
     * @param string $icone
     * @return MbPage
     */
    public function setIcone($icone)
    {
        $this->icone = $icone;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosicao()
    {
        return $this->posicao;
    }

    /**
     * @param int $posicao
     * @return MbPage
     */
    public function setPosicao($posicao)
    {
        $this->posicao = $posicao;
        return $this;
    }

    /**
     * @throws MbException
     * @return MbPage
     */
    public function getPaginaParente()
    {
        if (is_null($this->paginaParente))
            throw new MbException("Nenhuma página parente foi definida em {$this->getNome()}");

        return $this->paginaParente;
    }

    /**
     * @param MbPage $paginaParente
     * @return MbPage
     */
    public function setPaginaParente(MbPage $paginaParente = null)
    {
        $this->paginaParente = $paginaParente;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRemoverSubMenuPagina()
    {
        return $this->removerSubMenuPagina;
    }

    /**
     * @param boolean $removerSubMenuPagina
     * @return MbPage
     */
    public function setRemoverSubMenuPagina($removerSubMenuPagina = true)
    {
        $this->removerSubMenuPagina = $removerSubMenuPagina;
        return $this;
    }

    /**
     * @return MbPage[]
     */
    public function getSubPaginas()
    {
        return $this->subPaginas;
    }

    /**
     * @param string $slug
     * @return MbPage|null
     */
    public function getSubPagina($slug)
    {
        if (!isset($this->subPaginas[$slug]))
            return null;

        return $this->subPaginas[$slug];
    }

    /**
     * @param MbPage $pagina
     * @return MbPage Retorna a SubPagina para melhor tratamento
     */
    public function setSubPagina(MbPage $pagina)
    {
        $this->subPaginas[$pagina->getSlug()] = $pagina;
        $pagina->setPaginaParente($this);
        return $pagina;
    }

    /**
     * @param string $slug
     * @return MbPage Retorna a SubPagina para melhor tratamento
     */
    public function adicionarSubPagina($slug)
    {
        $pagina = new self();
        $pagina->setSlug($slug)
            ->setPaginaParente($this);

        $this->subPaginas[$pagina->getSlug()] = $pagina;
        return $pagina;
    }

    /**
     * @return boolean
     */
    public function isMenuPrincipal()
    {
        return $this->menuPrincipal;
    }

    /**
     * @param boolean $menuPrincipal
     * @return MbPage
     */
    public function setMenuPrincipal($menuPrincipal = true)
    {
        $this->menuPrincipal = $menuPrincipal;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSubmenu()
    {
        return $this->submenu;
    }

    /**
     * @param boolean $submenu
     * @return MbPage
     */
    public function setSubmenu($submenu = true)
    {
        $this->submenu = $submenu;
        return $this;
    }

    /**
     * @return MocaBonita
     */
    public function getMocaBonita()
    {
        return $this->mocaBonita;
    }

    /**
     * @param MocaBonita $mocaBonita
     * @return MbPage
     */
    public function setMocaBonita(MocaBonita $mocaBonita)
    {
        $this->mocaBonita = $mocaBonita;
        return $this;
    }

    /**
     * @throws MbException caso não exista controller definido
     * @return MbController
     */
    public function getController()
    {
        if (is_string($this->controller)) {
            $this->controller = MbController::create($this->controller);
        } elseif (is_null($this->controller) || !$this->controller instanceof MbController) {
            throw new MbException("Nenhum Controller foi definido para a página {$this->getNome()}.");
        }

        return $this->controller;
    }

    /**
     * @param MbController|string $controller
     * @return MbPage
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEsconderMenu()
    {
        return $this->esconderMenu;
    }

    /**
     * @param boolean $esconderMenu
     * @return MbPage
     */
    public function setEsconderMenu($esconderMenu = true)
    {
        $this->esconderMenu = $esconderMenu;
        return $this;
    }

    /**
     * @param string $acao
     * @return MbAction|null
     */
    public function getAcao($acao)
    {
        if (!isset($this->acoes[$acao]))
            return null;

        return $this->acoes[$acao];
    }

    /**
     * @param MbAction $acao
     * @return MbAction
     */
    public function setAcao(MbAction $acao)
    {
        $this->acoes[$acao->getNome()] = $acao;
        return $acao;
    }

    /**
     * @param string $nome
     * @return MbAction
     */
    public function adicionarAcao($nome)
    {
        $this->acoes[$nome] = new MbAction($this, $nome);
        return $this->acoes[$nome];
    }

    /**
     * @return MbAsset
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param MbAsset $assets
     * @return MbPage
     */
    public function setAssets(MbAsset $assets)
    {
        $this->assets = $assets;
        return $this;
    }

    /**
     * Adicionar as páginas ao menu do wordpress
     *
     */
    public function adicionarMenuWordpress()
    {
        //Adicionar menu principal
        if ($this->isEsconderMenu()) {

            add_submenu_page(
                null,
                $this->getNome(),
                $this->getNome(),
                $this->getCapability(),
                $this->getSlug(),
                [MocaBonita::getInstance(), 'sendContent']
            );

            //Adicionar menu principal
        } elseif ($this->isMenuPrincipal()) {

            add_menu_page(
                $this->getNome(),
                $this->getNome(),
                $this->getCapability(),
                $this->getSlug(),
                [MocaBonita::getInstance(), 'sendContent'],
                $this->getIcone(),
                $this->getPosicao()
            );

            //Adicionar submenu
        } elseif ($this->isSubmenu()) {

            add_submenu_page(
                $this->getPaginaParente()->getSlug(),
                $this->getNome(),
                $this->getNome(),
                $this->getCapability(),
                $this->getSlug(),
                [MocaBonita::getInstance(), 'sendContent']
            );

            //Remover submenu semelhante ao menu principal
            if ($this->getPaginaParente()->isRemoverSubMenuPagina()) {
                remove_submenu_page($this->getPaginaParente()->getSlug(), $this->getPaginaParente()->getSlug());
                $this->getPaginaParente()->setRemoverSubMenuPagina(false);
            }
        }

    }
}