<?php

namespace MocaBonita;

use MocaBonita\tools\Diretorios;
use MocaBonita\tools\Respostas;
use MocaBonita\tools\Requisicoes;
use MocaBonita\service\Service;
use MocaBonita\tools\Acoes;
use MocaBonita\tools\MBException;
use MocaBonita\tools\ShortCode;
use MocaBonita\tools\Assets;
use MocaBonita\tools\Paginas;
use MocaBonita\tools\WPAction;
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
final class MocaBonita
{
    /**
     * Versão do Moca Bonita.
     */
    const VERSION = '3.0.0';

    /**
     * Instancia da classe.
     *
     * @var MocaBonita
     */
    protected static $instance;

    /**
     * Paginas objeto
     *
     * @var Paginas[]
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
     * @var ShortCode[]
     */
    private $shortcodes = [];

    /**
     * Assets do plugin e Wordpress do Moca Bonita
     *
     * @var Assets[]
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
     * @var Requisicoes
     */
    protected $request;

    /**
     * Váriavel que armazenda a resposta
     *
     * @var Respostas
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
     * @return Assets
     */
    public function getAssets($wordpress = false)
    {
        return $wordpress ? $this->assets['wordpress'] : $this->assets['plugin'];
    }

    /**
     * Obter assets do plugin
     *
     * @return Assets
     */
    public function getAssetsPlugin()
    {
        return $this->getAssets();
    }

    /**
     * Obter assets do wordpress
     *
     * @return Assets
     */
    public function getAssetsWordpress()
    {
        return $this->getAssets(true);
    }

    /**
     * @param Assets $assets
     * @param bool $wordpress
     *
     * @return MocaBonita
     */
    public function setAssets(Assets $assets, $wordpress = false)
    {
        $this->assets[$wordpress ? 'wordpress' : 'plugin'] = $assets;
        return $this;
    }

    /**
     * @param Assets $assetsPlugin
     *
     * @return MocaBonita
     */
    public function setAssetsPlugin(Assets $assetsPlugin)
    {
        return $this->setAssets($assetsPlugin);
    }

    /**
     * @param Assets $assetsWordpress
     * @return MocaBonita
     */
    public function setAssetsWordpress(Assets $assetsWordpress)
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
     * @return MocaBonita
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Inicializar o processamento do moça bonita
     * @param $plugin \Closure callback de inicialização do plugin
     * @param bool $emDesenvolvimento Definir plugin em desenvolvimento
     */
    public static function loader(\Closure $plugin, $emDesenvolvimento = false)
    {
        $mocaBonita = self::getInstance();
        $mocaBonita->emDesenvolvimento = $emDesenvolvimento;

        if (!defined('ABSPATH')) {
            die('O Framework Moça Bonita precisa ser carregado dentro do Wordpress!' . PHP_EOL);
        }

        register_activation_hook(Diretorios::PLUGIN_BASENAME, function () {
            if (version_compare(PHP_VERSION, '5.6', '<') || version_compare(get_bloginfo('version'), '4.5', '<')) {
                deactivate_plugins(Diretorios::PLUGIN_BASENAME);
            }
        });

        WPAction::adicionarCallbackAction('plugins_loaded', function () use ($plugin, $mocaBonita) {
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
     * @param bool $emDesenvolvimento Definir plugin em desenvolvimento
     */
    public static function active(\Closure $active, $emDesenvolvimento = false)
    {
        $mocaBonita = self::getInstance();
        $mocaBonita->emDesenvolvimento = $emDesenvolvimento;

        if (!defined('ABSPATH')) {
            die('O Framework Moça Bonita precisa ser carregado dentro do Wordpress!' . PHP_EOL);
        }

        register_activation_hook(Diretorios::PLUGIN_BASENAME, function () use ($active, $mocaBonita) {
            try {
                $active($mocaBonita->request, $mocaBonita->response);
            } catch (\Exception $e) {
                $mocaBonita->response->setConteudo($e);
            }
        });
    }

    /**
     * Callback para ser executado ao desativar o plugin
     * @param $deactive \Closure callback de inicialização do plugin
     * @param bool $emDesenvolvimento Definir plugin em desenvolvimento
     */
    public static function deactive(\Closure $deactive, $emDesenvolvimento = false)
    {
        $mocaBonita = self::getInstance();
        $mocaBonita->emDesenvolvimento = $emDesenvolvimento;

        if (!defined('ABSPATH')) {
            die('O Framework Moça Bonita precisa ser carregado dentro do Wordpress!' . PHP_EOL);
        }

        register_deactivation_hook(Diretorios::PLUGIN_BASENAME, function () use ($deactive, $mocaBonita) {
            try {
                $deactive($mocaBonita->request, $mocaBonita->response);
            } catch (\Exception $e) {
                $mocaBonita->response->setConteudo($e);
            }
        });
    }

    /**
     * Callback para ser executado ao apagar o plugin
     * @param $unistall \Closure callback de inicialização do plugin
     * @param bool $emDesenvolvimento Definir plugin em desenvolvimento
     */
    public static function uninstall(\Closure $unistall, $emDesenvolvimento = false)
    {
        $mocaBonita = self::getInstance();
        $mocaBonita->emDesenvolvimento = $emDesenvolvimento;

        if (!defined('ABSPATH')) {
            die('O Framework Moça Bonita precisa ser carregado dentro do Wordpress!' . PHP_EOL);
        }

        register_uninstall_hook(Diretorios::PLUGIN_BASENAME, function () use ($unistall, $mocaBonita) {
            try {
                $unistall($mocaBonita->request, $mocaBonita->response);
            } catch (\Exception $e) {
                $mocaBonita->response->setConteudo($e);
            }
        });
    }

    /**
     * Inicializar o processamento do moça bonita
     *
     */
    private function launcher()
    {
        try {
            //Organizar as paginas e subpagina do moca bonita
            $this->processarPaginas();

            //Adicionar os menus do wordpress
            WPAction::adicionarAction('admin_menu', $this, 'processarMenu');

            //Adicionar os Assets do wordpress
            $this->getAssets(true)->processarAssets('*');

            //Adicionar os serviços do wordpress
            Service::processarServicos($this->getServicos(true), $this->request, $this->response);

            //Processar shortcodes
            foreach ($this->shortcodes as $shortcode) {
                $shortcode->processarShorcode($this->getAssets(), $this->request, $this->response);
            }

            //Verificar se a página atual é do plugin
            if ($this->isPaginaPlugin()) {

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
                if ($this->request->isLogin()) {
                    //Verificar se a página atual é requisitada via ajax
                    if ($this->request->isAjax()) {
                        //Adicionar o action AdminAjax
                        WPAction::adicionarAction("wp_ajax_{$this->action}", $this, 'getConteudo');
                    } else {
                        //Adicionar o action AdminPost
                        WPAction::adicionarAction("admin_post_{$this->action}", $this, 'getConteudo');
                    }

                    //Caso a página atual não é adminstradora
                } else {
                    //Adicionar o action NoAdminAjax
                    if ($this->request->isAjax()) {
                        WPAction::adicionarAction("wp_ajax_nopriv_{$this->action}", $this, 'getConteudo');
                    } else {
                        //Adicionar o action NoAdminPost
                        WPAction::adicionarAction("admin_post_nopriv_{$this->action}", $this, 'getConteudo');
                    }
                }
            }

            //Caso ocorra algum erro durante o processamento do plugin
        } catch (\Exception $e) {
            if ($this->request->isAjax()) {
                WPAction::adicionarAction("wp_ajax_{$this->action}", $this, 'getConteudo');
                WPAction::adicionarAction("wp_ajax_nopriv_{$this->action}", $this, 'getConteudo');
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
                throw new MBException(
                    "A Ação {$this->action} da página {$this->page} não foi instânciada no objeto da página!"
                );
            } //Verificar se a Ação tem capacidade, se não, obtem a capacidade da página
            elseif (is_null($acao->getCapacidade())) {
                $acao->setCapacidade($pagina->getCapacidade());
            }

            //Caso a ação precise do login e não tenha nenhum usuário logado no wordpress
            if ($acao->isLogin() && !$this->request->isLogin()) {
                throw new MBException(
                    "A Ação {$this->action} da página {$this->page} requer o login do wordpress!"
                );
            } //Caso a action seja admin, é verificado se o usuário tem capacidade suficiente
            elseif ($acao->isLogin() && !current_user_can($acao->getCapacidade())) {
                throw new MBException(
                    "A Ação {$this->action} da página {$this->page} requer um usuário com mais permissões de acesso!"
                );
            } //Caso a ação precise ser chamada via admin-ajax.php no wordpress e esta sendo chamado de outra forma
            elseif ($acao->isAjax() && !$this->request->isAjax()) {
                throw new MBException(
                    "A Ação {$this->action} da página {$this->page} precisa ser requisitada em admin-ajax.php!"
                );
            } //Caso a ação tenha um método de requisição diferente da requisição atual
            elseif ($acao->getRequisicao() != $this->request->method() && !is_null($acao->getRequisicao())) {
                throw new MBException(
                    "A Ação {$this->action} da página {$this->page} precisa ser requisitada via {$acao->getRequisicao()}!"
                );
            } //Caso a ação não tenha um método criado ou publico na controller
            elseif (!$acao->metodoValido()) {
                throw new MBException(
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
     * Construtor do Moça Bonita
     * @param bool $emDesenvolvimento Verificar se a página está em desenvolvimento
     */
    private function __construct($emDesenvolvimento = false)
    {
        $this->response = Respostas::create();
        $this->request = Requisicoes::capture();
        $this->response->setRequest($this->request);
        $this->page = $this->request->query('page');
        $this->action = $this->request->query('action');

        $this->assets = [
            'plugin' => new Assets(),
            'wordpress' => new Assets(),
        ];

        $this->servicos = [
            'plugin' => [],
            'wordpress' => [],
        ];

        //Definir se a página está em desenvolvimento
        $this->emDesenvolvimento = (bool)$emDesenvolvimento;

        if ($emDesenvolvimento) {
            $this->desabilitarCaches();
        }
    }

    /**
     * O método mágico __clone() é declarado como private para impedir a clonagem de uma instância da classe através
     * do operador clone.
     *
     */
    final private function __clone()
    {
        //
    }

    /**
     * O método mágico __wakeup() é declarado como private para evitar unserializing de uma instância da classe via
     * a função global unserialize ().
     *
     */
    final private function __wakeup()
    {
        //
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
     * @throws MBException
     * @return Paginas
     */
    public function getPagina($nomePagina)
    {
        if (!isset($this->paginas[$nomePagina])) {
            throw new MBException("A página {$nomePagina} não foi adicionada na lista de páginas do MocaBonita!");
        }

        return $this->paginas[$nomePagina];
    }

    /**
     * Obter shortcode através do slug
     *
     * @param string $nomeShortcode da shortcode adicionada
     * @throws MBException
     * @return ShortCode
     */
    public function getShortcode($nomeShortcode)
    {
        if (!isset($this->shortcodes[$nomeShortcode])) {
            throw new MBException("O shortcode {$nomeShortcode} não foi adicionado na lista de shortcode do MocaBonita!");
        }

        return $this->shortcodes[$nomeShortcode];
    }

    /**
     * Processar e organizar todas as páginas do Moçabonita para melhor exibição
     *
     * @throws MBException
     */
    private function processarPaginas()
    {
        foreach ($this->paginas as $pagina) {
            if ($pagina->isMenuPrincipal()) {
                foreach ($pagina->getSubPaginas() as $subPagina) {
                    $subPagina->setPaginaParente($pagina);
                    $this->adicionarSubPagina($subPagina);
                }
            } else {
                $this->adicionarPagina($pagina->getPaginaParente());
            }
        }
    }

    /**
     * Adicionar páginas ao wordpress
     *
     * @param Paginas $pagina o objeto da página principal
     * @return MocaBonita
     */
    public function adicionarPagina(Paginas $pagina)
    {
        $pagina->setSubmenu(false);

        $pagina->setMenuPrincipal(true);

        $this->paginas[$pagina->getSlug()] = $pagina;

        $pagina->setMocaBonita($this);

        return $this;
    }

    /**
     * Adicionar SubMenu ao wordpress
     *
     * @param Paginas $pagina o objeto da sub página
     * @return MocaBonita
     */
    public function adicionarSubPagina(Paginas $pagina)
    {
        $pagina->setMenuPrincipal(false);

        $pagina->setSubmenu(true);

        $this->paginas[$pagina->getSlug()] = $pagina;

        $pagina->setMocaBonita($this);

        return $this;
    }

    /**
     * @param string $nome
     * @param Paginas $pagina
     * @param string $metodo
     * @param Assets $assets
     * @return ShortCode
     */
    public function adicionarShortcode($nome, Paginas $pagina, $metodo, Assets $assets = null)
    {
        $acao = new Acoes($pagina, $metodo);

        $acao->setShortcode(true)->setComplemento('Shortcode');

        $this->shortcodes[$nome] = new ShortCode($nome, $acao, is_null($assets) ? new Assets() : $assets);

        return $this->shortcodes[$nome];
    }

    /**
     * Processar menu das páginas
     */
    public function processarMenu()
    {
        if ($this->request->isAdmin()) {
            foreach ($this->paginas as $pagina) {
                $pagina->adicionarMenuWordpress();
            }
        }
    }

    /**
     * @return Requisicoes
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Requisicoes $request
     */
    public function setRequest(Requisicoes $request)
    {
        $this->request = $request;
    }

    /**
     * @return Respostas
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Respostas $response
     */
    public function setResponse(Respostas $response)
    {
        $this->response = $response;
    }

}