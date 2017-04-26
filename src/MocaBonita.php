<?php

namespace MocaBonita;

use Illuminate\Pagination\Paginator;
use MocaBonita\tools\eloquent\MbDatabaseQueryBuilder;
use MocaBonita\tools\MbDiretorios;
use MocaBonita\tools\MbCapsule;
use MocaBonita\tools\MbRespostas;
use MocaBonita\tools\MbRequisicoes;
use MocaBonita\tools\MbEventos;
use MocaBonita\tools\MbAcoes;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbShortCode;
use MocaBonita\tools\MbAssets;
use MocaBonita\tools\MbPaginas;
use MocaBonita\tools\MbSingleton;
use MocaBonita\tools\MbWPAction;
use MocaBonita\view\View;

/**
 * Um framework para o desenvolvimento de plugins na plataforma wordpress.
 *
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
final class MocaBonita extends MbSingleton
{
    /**
     * Versão do Moca Bonita.
     */
    const VERSION = '3.0.0';

    /**
     * Paginas objeto
     *
     * @var MbPaginas[]
     */
    private $paginas = [];

    /**
     * Serviços do Plugin e Wordpress do Moca Bonita
     *
     * @var array[]
     */
    private $eventos = [];

    /**
     * Shortcodes do Wordpress do Moca Bonita
     *
     * @var MbShortCode[]
     */
    private $shortcodes = [];

    /**
     * Assets do plugin e Wordpress do Moca Bonita
     *
     * @var MbAssets[]
     */
    private $assets;

    /**
     * Váriavel que verifica se a página atual foi gerada pelo Plugin atual
     *
     * @var boolean
     */
    private $paginaPlugin;

    /**
     * Váriavel que verifica se o Plugin atual está em desenvolvimento
     *
     * @var boolean
     */
    public $emDesenvolvimento;

    /**
     * Váriavel que verifica se a página atual é adminBlog
     *
     * @var boolean
     */
    public $blogAdmin;

    /**
     * Váriavel que armazenda o request
     *
     * @var MbRequisicoes
     */
    private $request;

    /**
     * Váriavel que armazenda a resposta
     *
     * @var MbRespostas
     */
    private $response;

    /**
     * Contém a página atual do wordpress obtida atráves do método httpGet['page']
     *
     * @var string
     */
    private $page;

    /**
     * Contém a ação atual da página do wordpress obtida atráves do método httpGet['action']
     *
     * @var string
     */
    private $action;

    /**
     * Obter todos os assets
     *
     * @param bool $wordpress
     *
     * @return MbAssets
     */
    public function getAssets($wordpress = false)
    {
        return $wordpress ? $this->assets['wordpress'] : $this->assets['plugin'];
    }

    /**
     * Obter assets do plugin
     *
     * @return MbAssets
     */
    public function getAssetsPlugin()
    {
        return $this->getAssets();
    }

    /**
     * Obter assets do wordpress
     *
     * @return MbAssets
     */
    public function getAssetsWordpress()
    {
        return $this->getAssets(true);
    }

    /**
     * @param MbAssets $assets
     * @param bool $wordpress
     *
     * @return MocaBonita
     */
    public function setAssets(MbAssets $assets, $wordpress = false)
    {
        $this->assets[$wordpress ? 'wordpress' : 'plugin'] = $assets;
        return $this;
    }

    /**
     * @param MbAssets $assetsPlugin
     *
     * @return MocaBonita
     */
    public function setAssetsPlugin(MbAssets $assetsPlugin)
    {
        return $this->setAssets($assetsPlugin);
    }

    /**
     * @param MbAssets $assetsWordpress
     * @return MocaBonita
     */
    public function setAssetsWordpress(MbAssets $assetsWordpress)
    {
        return $this->setAssets($assetsWordpress, true);
    }

    /**
     * @param string|null $dispatch
     *
     * @return array|MbEventos[]
     */
    public function getEventos($dispatch = null)
    {
        if (is_null($dispatch)) {
            return $this->eventos;
        } elseif (isset($this->eventos[$dispatch])) {
            return $this->eventos[$dispatch];
        } else {
            return [];
        }
    }

    /**
     * @param MbEventos $eventoClass
     * @param string|array $dispatch
     *
     * @return MocaBonita
     */
    public function adicionarEvento(MbEventos $eventoClass, $dispatch)
    {
        if (is_array($dispatch)) {
            foreach ($dispatch as $item) {
                $this->adicionarEvento($eventoClass, $item);
            }
        } else {
            if (!isset($this->eventos[$dispatch])) {
                $this->eventos[$dispatch] = [];
            }
            $this->eventos[$dispatch][] = $eventoClass;
        }
        return $this;
    }

    /**
     * Obter versão atual
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Obter instancia da aplicação.
     *
     * @return void
     */
    protected function init()
    {
        if (!defined('ABSPATH')) {
            die('O Framework Moça Bonita precisa ser carregado dentro do Wordpress!' . PHP_EOL);
        }

        $timezone = get_option('timezone_string');

        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }

        $this->setRequest(MbRequisicoes::capture());
        $this->setResponse(MbRespostas::create());
        $this->getResponse()->setRequest($this->request);
        $this->setPage($this->request->query('page'));
        $this->setAction($this->request->query('action'));
        $this->setBlogAdmin(is_blog_admin());

        $this->assets = [
            'plugin' => new MbAssets(),
            'wordpress' => new MbAssets(),
        ];

        $this->eventos = [];

        MbCapsule::wpdb();
    }

    /**
     * Inicializar o processamento do moça bonita
     * @param $plugin \Closure callback de inicialização do plugin
     * @param bool $emDesenvolvimento Definir plugin em desenvolvimento
     */
    public static function loader(\Closure $plugin, $emDesenvolvimento = false)
    {
        $mocaBonita = self::getInstance();
        $mocaBonita->emDesenvolvimento = (bool)$emDesenvolvimento;

        if ($emDesenvolvimento) {
            $mocaBonita->desabilitarCaches();
        }

        MbWPAction::adicionarCallbackAction('plugins_loaded', function () use ($plugin, $mocaBonita) {
            try {
                $plugin($mocaBonita);
                $mocaBonita->launcher();
            } catch (\Exception $e) {
                $mocaBonita->response->setContent($e);
            } finally {
                $mocaBonita->adicionarActionPagina();
                $mocaBonita->response->sendHeaders();
            }
        });
    }

    /**
     * @return boolean
     */
    public function isBlogAdmin()
    {
        return $this->blogAdmin;
    }

    /**
     * @param boolean $blogAdmin
     * @return MocaBonita
     */
    public function setBlogAdmin($blogAdmin)
    {
        $this->blogAdmin = $blogAdmin;
        return $this;
    }

    /**
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param string $page
     * @return MocaBonita
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return MocaBonita
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Callback para ser executado ao ativar o plugin
     * @param $active \Closure callback de inicialização do plugin
     */
    public static function active(\Closure $active)
    {
        $mocaBonita = self::getInstance();

        register_activation_hook(MbDiretorios::PLUGIN_BASENAME, function () use ($active, $mocaBonita) {
            try {
                self::verificarVersao();
                MocaBonita::verificarEscrita();
                MbCapsule::pdo();
                $active($mocaBonita);
            } catch (\Exception $e) {
                deactivate_plugins(basename(MbDiretorios::PLUGIN_BASENAME));
                wp_die($e->getMessage());
            }
        });
    }

    /**
     * Callback para ser executado ao desativar o plugin
     * @param $deactive \Closure callback de inicialização do plugin
     */
    public static function deactive(\Closure $deactive)
    {
        $mocaBonita = self::getInstance();

        register_deactivation_hook(MbDiretorios::PLUGIN_BASENAME, function () use ($deactive, $mocaBonita) {
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
     * Verificar versão do wordpress e PHP
     *
     */
    private static function verificarVersao()
    {
        if (version_compare(PHP_VERSION, '5.6', '<') || version_compare(get_bloginfo('version'), '4.5', '<')) {
            $exception = new \Exception(
                "Seu PHP ou WP está desatualizado e alguns recursos do MocaBonita podem não funcionar!"
            );

            MbException::adminNotice($exception);

            MbWPAction::adicionarCallbackAction('init', function () {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                deactivate_plugins(MbDiretorios::PLUGIN_BASENAME);
            });
        }
    }

    /**
     * Verificar permissão de escrita do diretório
     *
     */
    private static function verificarEscrita()
    {
        if (!is_writable(MbDiretorios::PLUGIN_DIRETORIO)) {
            $exception = new \Exception(
                "O MocaBonita não tem permissão de escrita no diretório do plugin!"
            );

            MbException::adminNotice($exception);

            MbWPAction::adicionarCallbackAction('init', function () {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                deactivate_plugins(MbDiretorios::PLUGIN_BASENAME);
            });
        }
    }

    /**
     * Callback para ser executado ao apagar o plugin
     * @param $unistall \Closure callback de inicialização do plugin
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
     * Inicializar o processamento do moça bonita
     *
     */
    private function launcher()
    {
        //Adicionar os Assets do wordpress
        $this->getAssets(true)->processarAssets('*');

        //Processar shortcodes
        foreach ($this->shortcodes as $shortcode) {
            $shortcode->processarShorcode($this->getAssets(), $this->request, $this->response);
        }

        //Adicionar os menus do wordpress
        if ($this->isBlogAdmin()) {
            MbWPAction::adicionarAction('admin_menu', $this, 'processarMenu');
        }

        MbEventos::processarEventos($this, MbEventos::START_WORDPRESS);

        //Verificar se a página atual é do plugin
        if ($this->isPaginaPlugin()) {

            $pagina = $this->getPagina($this->page);

            try {

                MbEventos::processarEventos($this, MbEventos::BEFORE_PAGE, $pagina);

                //Obter a lista de query params
                $query = $this->request->query();

                //Verificar se existe atributo da páginação
                if (isset($query[MbDatabaseQueryBuilder::getPageName()])) {
                    $paginacao = $query[MbDatabaseQueryBuilder::getPageName()];
                    unset($query[MbDatabaseQueryBuilder::getPageName()]);
                } else {
                    $paginacao = 1;
                }

                //Obter url da página sem páginação
                $url = $this->request->fullUrlWithNewQuery($query);

                //Definir rota da páginar para gerar url de páginação
                Paginator::currentPathResolver(function () use ($url) {
                    return $url;
                });

                //Definir página atual da paginação
                Paginator::currentPageResolver(function () use ($paginacao) {
                    return is_numeric($paginacao) ? (int)$paginacao : 1;
                });

                //Adicionar os Assets do plugin
                $this->getAssets()->processarAssets('plugin');

                //Adicionar os Assets da página
                $pagina->getAssets()->processarAssets($this->page);

                //Processar a página
                $this->processarPaginaAtual();

                MbEventos::processarEventos($this, MbEventos::AFTER_PAGE, $pagina);
            } catch (\Exception $e) {
                MbEventos::processarEventos($this, MbEventos::EXCEPTION_PAGE, $e);
                throw $e;
            } finally {
                MbEventos::processarEventos($this, MbEventos::FINISH_PAGE, $pagina);
            }
        }
        MbEventos::processarEventos($this, MbEventos::FINISH_WORDPRESS);
    }

    /**
     * Adicionar action de acordo com a página
     *
     */
    private function adicionarActionPagina()
    {
        if (!$this->isPaginaPlugin() || $this->isBlogAdmin()) {
            return false;
        }

        //Verificar se a página atual é adminstradora
        if ($this->request->isLogin()) {
            //Verificar se a página atual é requisitada via ajax
            if ($this->request->isAjax()) {
                //Adicionar o action AdminAjax
                $actionHook = "wp_ajax_{$this->getAction()}";
            } else {
                //Adicionar o action AdminPost
                $actionHook = "admin_post_{$this->getAction()}";
            }

        } //Caso a página atual não é adminstradora
        else {
            //Adicionar o action NoAdminAjax
            if ($this->request->isAjax()) {
                $actionHook = "wp_ajax_nopriv_{$this->getAction()}";
            } else {
                //Adicionar o action NoAdminPost
                $actionHook = "admin_post_nopriv_{$this->getAction()}";
            }
        }

        MbWPAction::adicionarAction($actionHook, $this, 'getConteudo');

        return true;
    }

    /**
     * Método para exibir conteudo da página
     *
     */
    public function getConteudo()
    {
        $this->response->sendContent();
    }

    /**
     * Método que processará a controller e validar a página
     *
     * @throws MbException
     */
    private function processarPaginaAtual()
    {
        //Obter as configurações da página atual
        $pagina = $this->getPagina($this->page);
        $nomeController = get_class($pagina->getController());

        //Obter a ação atual da página
        $acao = $pagina->getAcao($this->action);

        //Caso a ação não seja definida no objeto página, lançar uma exception
        if (is_null($acao)) {
            throw new MbException(
                "A Ação {$this->action} da página {$this->page} não foi instânciada no objeto da página!"
            );
        } //Verificar se a Ação tem capacidade, se não, obtem a capacidade da página
        elseif (is_null($acao->getCapacidade())) {
            $acao->setCapacidade($pagina->getCapacidade());
        }

        //Caso a ação precise do login e não tenha nenhum usuário logado no wordpress
        if ($acao->isLogin() && !$this->request->isLogin()) {
            throw new MbException(
                "A Ação {$this->action} da página {$this->page} requer o login do wordpress!"
            );
        } //Caso a action seja admin, é verificado se o usuário tem capacidade suficiente
        elseif ($acao->isLogin() && !current_user_can($acao->getCapacidade())) {
            throw new MbException(
                "A Ação {$this->action} da página {$this->page} requer um usuário com mais permissões de acesso!"
            );
        } //Caso a ação precise ser chamada via admin-ajax.php no wordpress e esta sendo chamado de outra forma
        elseif ($acao->isAjax() && !$this->request->isAjax()) {
            throw new MbException(
                "A Ação {$this->action} da página {$this->page} precisa ser requisitada em admin-ajax.php!"
            );
        } //Caso a ação tenha um método de requisição diferente da requisição atual
        elseif ($acao->getRequisicao() != $this->request->method() && !is_null($acao->getRequisicao())) {
            throw new MbException(
                "A Ação {$this->action} da página {$this->page} precisa ser requisitada via {$acao->getRequisicao()}!"
            );
        } //Caso a ação não tenha um método criado ou publico na controller
        elseif (!$acao->metodoValido()) {
            throw new MbException(
                "A Ação {$this->action} da página {$this->page} não tem um método publico na controller {$nomeController}. 
                    Por favor, criar ou tornar public o método {$acao->getMetodo()} em {$nomeController}!"
            );
        }

        //Carregar view e suas configuracoes da controller
        $acao->getPagina()->getController()->setView(new View());
        $acao->getPagina()
            ->getController()
            ->getView()
            ->setView('index', $this->page, $this->action)
            ->setRequest($this->request)
            ->setResponse($this->response);

        //Carregar request e response da controller
        $acao->getPagina()->getController()->setRequest($this->request)->setResponse($this->response);

        //Definir página principal
        $acao->getPagina()->getController()->getView()->setPage($this->page);

        //Definir acao metodo
        $acao->getPagina()->getController()->getView()->setAction($this->action);

        //Começar a processar a controller
        ob_start();

        try {
            MbEventos::processarEventos($this, MbEventos::BEFORE_ACTION, $acao);

            $respostaController = $acao->getPagina()
                ->getController()
                ->{$acao->getMetodo()}($this->request, $this->response);
            MbEventos::processarEventos($this, MbEventos::AFTER_ACTION, $acao);

            //Caso a controller lance alguma exception, ela será lançada abaixo!
        } catch (\Exception $e) {
            MbEventos::processarEventos($this, MbEventos::EXCEPTION_ACTION, $e);
            $respostaController = $e;
        } finally {
            MbEventos::processarEventos($this, MbEventos::FINISH_ACTION, $acao);
            $conteudoController = ob_get_contents();
        }

        ob_end_clean();

        //Verificar se a controller imprimiu alguma coisa e exibir no erro_log
        if ($conteudoController != "") {
            error_log($conteudoController);
        }

        //Verificar se a resposta é nula e a requisicao não é ajax e então ele pega a view da controller
        if (is_null($respostaController) && !$this->request->isAjax()) {
            $respostaController = $acao->getPagina()->getController()->getView();
        }

        //Processar a página
        $this->response->setContent($respostaController);
    }

    /**
     * Desabilitar qualquer tipo de cache da página durante o acesso em modo de desenvolvimento
     *
     */
    private function desabilitarCaches()
    {
        $this->response
            ->header("Cache-Control", "no-cache, no-store, must-revalidate")
            ->header("Pragma", "no-cache")
            ->header("Expires", "0");
    }

    /**
     * Verificar se é a página atual é uma página do plugin
     *
     * @return bool
     */
    public function isPaginaPlugin()
    {
        if (is_null($this->page)) {
            return false;
        }

        if (is_null($this->paginaPlugin)) {
            $this->paginaPlugin = in_array($this->page, array_keys($this->paginas));
        }

        if ($this->paginaPlugin && is_null($this->action)) {
            $query = http_build_query([
                'page' => $this->page,
                'action' => 'index',
            ]);
            $url = admin_url($this->request->getPageNow()) . "?" . $query;
            $this->response->redirect($url);
        }

        return $this->paginaPlugin;
    }

    /**
     * Obter página através do slug
     *
     * @param string $nomePagina da página adicionada
     * @throws MbException
     * @return MbPaginas
     */
    public function getPagina($nomePagina)
    {
        if (!isset($this->paginas[$nomePagina])) {
            throw new MbException("A página {$nomePagina} não foi adicionada na lista de páginas do MocaBonita!");
        }

        return $this->paginas[$nomePagina];
    }

    /**
     * Obter shortcode através do slug
     *
     * @param string $nomeShortcode da shortcode adicionada
     * @throws MbException
     * @return MbShortCode
     */
    public function getShortcode($nomeShortcode)
    {
        if (!isset($this->shortcodes[$nomeShortcode])) {
            throw new MbException("O shortcode {$nomeShortcode} não foi adicionado na lista de shortcode do MocaBonita!");
        }

        return $this->shortcodes[$nomeShortcode];
    }

    /**
     * Adicionar páginas ao wordpress
     *
     * @param MbPaginas $pagina o objeto da página principal
     * @return MocaBonita
     */
    public function adicionarPagina(MbPaginas $pagina)
    {
        $pagina->setSubmenu(false);

        $pagina->setMenuPrincipal(true);

        $this->paginas[$pagina->getSlug()] = $pagina;

        foreach ($pagina->getSubPaginas() as $subPagina) {
            $subPagina->setPaginaParente($pagina);
            $this->adicionarSubPagina($subPagina);
        }

        $pagina->setMocaBonita($this);

        return $this;
    }

    /**
     * Adicionar SubMenu ao wordpress
     *
     * @param MbPaginas $pagina o objeto da sub página
     * @return MocaBonita
     */
    public function adicionarSubPagina(MbPaginas $pagina)
    {
        $pagina->setMenuPrincipal(false);

        $pagina->setSubmenu(true);

        $this->paginas[$pagina->getSlug()] = $pagina;

        $pagina->setMocaBonita($this);

        return $this;
    }

    /**
     * @param string $nome
     * @param MbPaginas $pagina
     * @param string $metodo
     * @param MbAssets $assets
     * @return MbShortCode
     */
    public function adicionarShortcode($nome, MbPaginas $pagina, $metodo, MbAssets $assets = null)
    {
        $acao = new MbAcoes($pagina, $metodo);

        $acao->setShortcode(true)->setComplemento('Shortcode');

        $this->shortcodes[$nome] = new MbShortCode($nome, $acao, is_null($assets) ? new MbAssets() : $assets);

        return $this->shortcodes[$nome];
    }

    /**
     * Processar menu das páginas
     */
    public function processarMenu()
    {
        foreach ($this->paginas as $pagina) {
            $pagina->adicionarMenuWordpress();
        }
    }

    /**
     * @return MbRequisicoes
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param MbRequisicoes $request
     */
    public function setRequest(MbRequisicoes $request)
    {
        $this->request = $request;
    }

    /**
     * @return MbRespostas
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param MbRespostas|\Symfony\Component\HttpFoundation\Response $response
     */
    public function setResponse(MbRespostas $response)
    {
        $this->response = $response;
    }

}