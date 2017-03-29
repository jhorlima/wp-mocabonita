<?php

namespace MocaBonita;

use Illuminate\Pagination\Paginator;
use MocaBonita\tools\eloquent\MbDatabaseBuilder;
use MocaBonita\tools\MbDiretorios;
use MocaBonita\tools\MbCapsule;
use MocaBonita\tools\MbRespostas;
use MocaBonita\tools\MbRequisicoes;
use MocaBonita\service\Service;
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
    protected $paginas = [];

    /**
     * Serviços do Plugin e Wordpress do Moca Bonita
     *
     * @var array[]
     */
    private $servicos = [];

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
    protected $paginaPlugin;

    /**
     * Váriavel que verifica se o Plugin atual está em desenvolvimento
     *
     * @var boolean
     */
    public $emDesenvolvimento;

    /**
     * Váriavel que armazenda o request
     *
     * @var MbRequisicoes
     */
    protected $request;

    /**
     * Váriavel que armazenda a resposta
     *
     * @var MbRespostas
     */
    protected $response;

    /**
     * Contém a página atual do wordpress obtida atráves do método httpGet['page']
     *
     * @var string
     */
    protected $page;

    /**
     * Contém a ação atual da página do wordpress obtida atráves do método httpGet['action']
     *
     * @var string
     */
    protected $action;

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
     * @param bool $wordpress
     * @return array[]
     */
    public function getServicos($wordpress = false)
    {
        return $wordpress ? $this->servicos['wordpress'] : $this->servicos['plugin'];
    }

    /**
     * @return array[]
     */
    public function getServicosPlugin()
    {
        return $this->getServicos();
    }

    /**
     * @return array[]
     */
    public function getServicosWordPress()
    {
        return $this->getServicos(true);
    }

    /**
     * @param string $servico
     * @param array $metodos
     * @param bool $wordpress
     *
     * @return MocaBonita
     */
    public function adicionarServicos($servico, array $metodos, $wordpress = false)
    {
        $this->servicos[$wordpress ? 'wordpress' : 'plugin'][] = Service::configuracoesServicos($servico, $metodos);
        return $this;
    }

    /**
     * @param string $servico
     * @param array $metodos
     * @return MocaBonita
     */
    public function adicionarServicosPlugin($servico, array $metodos)
    {
        return $this->adicionarServicos($servico, $metodos);
    }

    /**
     * @param string $servico
     * @param array $metodos
     * @return MocaBonita
     */
    public function adicionarServicosWordPress($servico, array $metodos)
    {
        return $this->adicionarServicos($servico, $metodos, true);
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

        date_default_timezone_set(get_option('timezone_string'));

        $this->response = MbRespostas::create();
        $this->request = MbRequisicoes::capture();
        $this->response->setRequest($this->request);
        $this->page = $this->request->query('page');
        $this->action = $this->request->query('action');

        $this->assets = [
            'plugin' => new MbAssets(),
            'wordpress' => new MbAssets(),
        ];

        $this->servicos = [
            'plugin' => [],
            'wordpress' => [],
        ];

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
        $mocaBonita->emDesenvolvimento = (bool) $emDesenvolvimento;

        if ($emDesenvolvimento) {
            $mocaBonita->desabilitarCaches();
        }

        MbWPAction::adicionarCallbackAction('plugins_loaded', function () use ($plugin, $mocaBonita) {
            try {
                $plugin($mocaBonita);
                $mocaBonita->launcher();
            } catch (\Exception $e) {
                $mocaBonita->response->setConteudo($e);
            } finally {
                $mocaBonita->response->processarHeaders();
            }
        });
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
                MbException::shutdown($e);
                wp_die($e->getMessage());
            }
        });
    }

    /**
     * Verificar versão do wordpress e PHP
     *
     */
    protected static function verificarVersao(){
        if (version_compare(PHP_VERSION, '5.6', '<') || version_compare(get_bloginfo('version'), '4.5', '<')) {
            $exception = new \Exception(
                "Seu PHP ou WP está desatualizado e alguns recursos do MocaBonita podem não funcionar!"
            );

            MbException::adminNotice($exception);

            MbWPAction::adicionarCallbackAction('init', function (){
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                deactivate_plugins(MbDiretorios::PLUGIN_BASENAME);
            });
        }
    }

    /**
     * Verificar permissão de escrita do diretório
     *
     */
    protected static function verificarEscrita(){
        if(!is_writable(MbDiretorios::PLUGIN_DIRETORIO)){
            $exception = new \Exception(
                "O MocaBonita não tem permissão de escrita no diretório do plugin!"
            );

            MbException::adminNotice($exception);

            MbWPAction::adicionarCallbackAction('init', function (){
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
            wp_die("Você não pode executar este método fora do arquivo unistall.php");
        }
    }

    /**
     * Inicializar o processamento do moça bonita
     *
     */
    private function launcher()
    {
        try {
            //Verificar se é necessário carregar o menu wordpress
            $carregarMenu = is_blog_admin();

            //Adicionar os Assets do wordpress
            $this->getAssets(true)->processarAssets('*');

            //Adicionar os serviços do wordpress
            Service::processarServicos($this->getServicos(true), $this->request, $this->response);

            //Processar shortcodes
            foreach ($this->shortcodes as $shortcode) {
                $shortcode->processarShorcode($this->getAssets(), $this->request, $this->response);
            }

            //Adicionar os menus do wordpress
            if($carregarMenu){
                MbWPAction::adicionarAction('admin_menu', $this, 'processarMenu');
            }

            //Verificar se a página atual é do plugin
            if ($this->isPaginaPlugin()) {

                //Obter a lista de query params
                $query = $this->request->query();

                //Verificar se existe atributo da páginação
                if(isset($query[MbDatabaseBuilder::getPageName()])){
                    $paginacao = $query[MbDatabaseBuilder::getPageName()];
                    unset($query[MbDatabaseBuilder::getPageName()]);
                } else {
                    $paginacao = 1;
                }

                //Obter url da página sem páginação
                $url = $this->request->fullUrlWithNewQuery($query);

                //Definir rota da páginação
                Paginator::currentPathResolver(function () use ($url) {
                    return $url;
                });

                //Definir página atual
                Paginator::currentPageResolver(function () use ($paginacao){
                    return is_numeric($paginacao) ? (int) $paginacao : 1;
                });

                //Adicionar os Assets do plugin
                $this->getAssets()->processarAssets('plugin');
                //Adicionar os serviços do plugin
                Service::processarServicos($this->getServicos(), $this->request, $this->response);

                //Adicionar os Assets da página
                $this->getPagina($this->page)->getAssets()->processarAssets($this->page);

                //Adicionar os serviços da página
                Service::processarServicos($this->getPagina($this->page)->getServicos(), $this->request, $this->response);

                //Processar a página
                $this->mocaBonita();

                //Verificar se a página atual é adminstradora
                if ($this->request->isLogin() && !$carregarMenu) {
                    //Verificar se a página atual é requisitada via ajax
                    if ($this->request->isAjax()) {
                        //Adicionar o action AdminAjax
                        MbWPAction::adicionarAction("wp_ajax_{$this->action}", $this, 'getConteudo');
                    } else {
                        //Adicionar o action AdminPost
                        MbWPAction::adicionarAction("admin_post_{$this->action}", $this, 'getConteudo');
                    }

                    //Caso a página atual não é adminstradora
                } elseif(!$carregarMenu) {
                    //Adicionar o action NoAdminAjax
                    if ($this->request->isAjax()) {
                        MbWPAction::adicionarAction("wp_ajax_nopriv_{$this->action}", $this, 'getConteudo');
                    } else {
                        //Adicionar o action NoAdminPost
                        MbWPAction::adicionarAction("admin_post_nopriv_{$this->action}", $this, 'getConteudo');
                    }
                }
            }

            //Caso ocorra algum erro durante o processamento do plugin
        } catch (\Exception $e) {
            if ($this->request->isAjax()) {
                MbWPAction::adicionarAction("wp_ajax_{$this->action}", $this, 'getConteudo');
                MbWPAction::adicionarAction("wp_ajax_nopriv_{$this->action}", $this, 'getConteudo');
            }
            $this->response->setConteudo($e);
        }
    }

    /**
     * Método para exibir conteudo da página
     *
     */
    public function getConteudo()
    {
        try {
            if (!$this->isPaginaPlugin()) {
                throw new \Exception("Você não pode exibir está página!");
            }
        } catch (\Exception $e) {
            $this->response->setConteudo($e);
        } finally {
            $this->response->getContent();
        }
    }

    /**
     * Método que processará a controller e validar a página
     *
     */
    private function mocaBonita()
    {
        try {

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
                $respostaController = $acao->getPagina()
                    ->getController()
                    ->{$acao->getMetodo()}($this->request, $this->response);
                //Caso a controller lance alguma exception, ela será lançada abaixo!
            } catch (\Exception $e) {
                $respostaController = $e;
            } finally {
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
            $this->response->setConteudo($respostaController);

            //Caso ocorra algum erro no moca bonita
        } catch (\Exception $e) {
            $this->response->setConteudo($e);
        }
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

        foreach ($pagina->getSubPaginas() as $subPagina) {
            $subPagina->setPaginaParente($pagina);
            $this->adicionarSubPagina($subPagina);
        }

        $this->paginas[$pagina->getSlug()] = $pagina;

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
     * @param MbRespostas $response
     */
    public function setResponse(MbRespostas $response)
    {
        $this->response = $response;
    }

}