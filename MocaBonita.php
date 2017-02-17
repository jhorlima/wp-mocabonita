<?php

namespace MocaBonita;

use MocaBonita\controller\Requisicoes;
use MocaBonita\service\Service;
use MocaBonita\tools\Acoes;
use MocaBonita\tools\MBException;
use MocaBonita\tools\ShortCode;
use MocaBonita\tools\Assets;
use MocaBonita\tools\Paginas;
use MocaBonita\tools\WPAction;

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
final class MocaBonita extends Requisicoes
{

    /**
     * Paginas objeto
     *
     * @var Paginas[]
     */
    protected $paginas = [];

    /**
     * Serviços do Plugin do Moca Bonita
     *
     * @var array
     */
    private $servicosPlugin = [];

    /**
     * Serviços do Wordpress do Moca Bonita
     *
     * @var array
     */
    private $servicosWordPress = [];

    /**
     * Shortcodes do Wordpress do Moca Bonita
     *
     * @var ShortCode[]
     */
    private $shortcodes = [];

    /**
     * Assets do plugin do Wordpress do Moca Bonita
     *
     * @var Assets
     */
    private $assetsPlugin;

    /**
     * Assets do Wordpress do Moca Bonita
     *
     * @var Assets
     */
    private $assetsWordpress;

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
     * Váriavel que armazenda as principais informações do request para alimentar a controller e service
     *
     * @var array
     */
    protected $dadosRequisicao;

    /**
     * @return Assets
     */
    public function getAssetsPlugin()
    {
        return $this->assetsPlugin;
    }

    /**
     * @param Assets $assetsPlugin
     * @return MocaBonita
     */
    public function setAssetsPlugin(Assets $assetsPlugin)
    {
        $this->assetsPlugin = $assetsPlugin;
        return $this;
    }

    /**
     * @return Assets
     */
    public function getAssetsWordpress()
    {
        return $this->assetsWordpress;
    }

    /**
     * @param Assets $assetsWordpress
     * @return MocaBonita
     */
    public function setAssetsWordpress(Assets $assetsWordpress)
    {
        $this->assetsWordpress = $assetsWordpress;
        return $this;
    }

    /**
     * @return array
     */
    public function getServicosPlugin()
    {
        return $this->servicosPlugin;
    }

    /**
     * @return array
     */
    public function getServicosWordPress()
    {
        return $this->servicosWordPress;
    }

    /**
     * Inicializar o processamento do moça bonita
     * @param $plugin \Closure callback de inicialização do plugin
     * @param bool $emDesenvolvimento Definir plugin em desenvolvimento
     */
    public static function loader(\Closure $plugin, $emDesenvolvimento = false)
    {
        if (!defined('ABSPATH')) {
            die('O Framework Moça Bonita precisa ser carregado dentro do Wordpress!' . PHP_EOL);
        }

        WPAction::adicionarCallbackAction('plugins_loaded', function () use ($plugin, $emDesenvolvimento) {
            try {
                $mocaBonita = new self($emDesenvolvimento);
                $plugin($mocaBonita);
                $mocaBonita->launcher();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        });
    }

    /**
     * Inicializar o processamento do moça bonita
     *
     */
    public function launcher()
    {
        try {
            //Organizar as paginas e subpagina do moca bonita
            $this->processarPaginas();

            //Adicionar os menus do wordpress
            WPAction::adicionarAction('admin_menu', $this, 'processarMenu');

            //Adicionar os Assets do wordpress
            $this->getAssetsWordpress()->processarCssWordpress('*');
            $this->getAssetsWordpress()->processarJsWordpress('*');

            //Adicionar os serviços do wordpress
            $this->processarServicos($this->getServicosWordPress());

            //Verificar se a página atual é do plugin
            if ($this->isPaginaPlugin()) {
                //Adicionar os Assets do plugin
                $this->getAssetsPlugin()->processarCssWordpress('plugin');
                $this->getAssetsPlugin()->processarJsWordpress('plugin');
                //Adicionar os serviços do plugin
                $this->processarServicos($this->getServicosPlugin());

                //Verificar se a página atual é adminstradora
                if ($this->isAdmin()) {
                    //Verificar se a página atual é requisitada via ajax
                    if ($this->isAjax()) {
                        //Adicionar o action AdminAjax
                        WPAction::adicionarAction("wp_ajax_{$this->action}", $this, 'mocaBonita');
                    } else {
                        //Adicionar o action AdminPost
                        WPAction::adicionarAction("admin_post_{$this->action}", $this, 'mocaBonita');
                    }

                    //Caso a página atual não é adminstradora
                } else {
                    //Adicionar o action NoAdminAjax
                    if ($this->ajax == 1) {
                        WPAction::adicionarAction("wp_ajax_nopriv_{$this->action}", $this, 'mocaBonita');
                    } else {
                        //Adicionar o action NoAdminPost
                        WPAction::adicionarAction("admin_post_nopriv_{$this->action}", $this, 'mocaBonita');
                    }
                }

                //Adicionar os Assets da página
                $this->getPagina($this->page)->getAssets()->processarCssWordpress($this->page);
                $this->getPagina($this->page)->getAssets()->processarJsWordpress($this->page);
                //Adicionar os serviços da página
                $this->processarServicos($this->getPagina($this->page)->getServicos());
            }

            foreach ($this->shortcodes as $shortcode) {
                $shortcode->processarShorcode([
                    'assets' => $this->getAssetsPlugin(),
                    'dados_requisicao' => $this->dadosRequisicao,
                    'em_desenvolvimento' => $this->emDesenvolvimento,
                ]);
            }

            //Caso ocorra algum erro durante o processamento do plugin
        } catch (\Exception $e) {
            $this->paginaPlugin = false;
            $mb = new MBException($e->getMessage());
            $mb->setRequisicoes($this);

            $callback = function () use ($mb) {
                $mb->processarExcecao();
            };

            WPAction::adicionarCallbackAction('admin_notices', $callback);
            WPAction::adicionarCallbackAction("wp_ajax_{$this->action}", $callback);
            WPAction::adicionarCallbackAction("wp_ajax_nopriv_{$this->action}", $callback);
        }
    }

    /**
     * Método executado pelo wordpress automaticamente através das action
     *
     */
    public function mocaBonita()
    {
        try {

            //Verificar se a página atual pertence ao plugin
            if (!$this->isPaginaPlugin()) {
                return null;
            }

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
            }

            //Verificar se a Ação tem capacidade, se não, obtem a capacidade da página
            if (is_null($acao->getCapacidade())) {
                $acao->setCapacidade($pagina->getCapacidade());
            }

            //Caso a action seja admin, é verificado se o usuário tem capacidade suficiente
            if ($acao->isAdmin() && !current_user_can($acao->getCapacidade())) {
                throw new MBException(
                    "A Ação {$this->action} da página {$this->page} requer um usuário com mais permissões de acesso!"
                );
            } //Caso a ação precise do login e não tenha nenhum usuário logado no wordpress
            elseif ($acao->isAdmin() && !$this->isLogin()) {
                throw new MBException(
                    "A Ação {$this->action} da página {$this->page} requer o login do wordpress!"
                );
            } //Caso a ação precise ser chamada via admin-ajax.php no wordpress e esta sendo chamado de outra forma
            elseif ($acao->isAjax() && !$this->isAjax()) {
                throw new MBException(
                    "A Ação {$this->action} da página {$this->page} precisa ser requisitada em admin-ajax.php!"
                );
            } //Caso a ação tenha um método de requisição diferente da requisição atual
            elseif ($acao->getRequisicao() != $this->metodoRequisicao && !is_null($acao->getRequisicao())) {
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

            //Carregar dados da controller
            $acao->getPagina()->getController()->mocabonita($this->dadosRequisicao);

            //Definir página principal
            $acao->getPagina()->getController()->getView()->setPage($this->page);

            //Definir acao metodo
            $acao->getPagina()->getController()->getView()->setAction($this->action);

            //Começar a processar a controller
            ob_start();

            $respostaController = $acao->getPagina()->getController()->{$acao->getMetodo()}();

            $conteudoController = ob_get_contents();

            ob_end_clean();

            //Verificar se a controller imprimiu alguma coisa e exibir no erro_log
            if ($conteudoController != "") {
                error_log($conteudoController);
            }

            //Verificar se a resposta é nula e a requisicao não é ajax e então ele pega a view da controller
            if (is_null($respostaController) && !$this->isAjax()) {
                $respostaController = $acao->getPagina()->getController()->getView();
            }

            $this->enviarDadosNavegador($respostaController);

            //Caso ocorra algum erro no moca bonita
        } catch (MBException $mb) {
            $mb->setRequisicoes($this);
            $mb->processarExcecao();
        } catch (\Exception $e) {
            $mb = new MBException($e->getMessage());
            $mb->setRequisicoes($this);
            $mb->processarExcecao();
        }
    }

    /**
     * Construtor do Moça Bonita
     * @param bool $emDesenvolvimento Verificar se a página está em desenvolvimento
     */
    public function __construct($emDesenvolvimento = false)
    {
        register_activation_hook(__FILE__, function () {
            if (version_compare(PHP_VERSION, '5.5', '<') || version_compare(get_bloginfo('version'), '4.5', '<')) {
                deactivate_plugins(basename(__FILE__));
            }
        });

        //Chamar o contruct da classe HTTPService
        parent::__construct();

        //Inicializar Assets do Plugin e Wordpress
        $this->setAssetsPlugin(new Assets());
        $this->setAssetsWordpress(new Assets());

        //Definir se a página está em desenvolvimento
        $this->emDesenvolvimento = (bool)$emDesenvolvimento;

        if ($emDesenvolvimento) {
            $this->desabilitarCaches();
        }

        //Obter os dados de requisição gerados pelo HTTPService e armazenar em $requestData
        $this->dadosRequisicao = [
            'metodoRequisicao' => $this->metodoRequisicao,
            'conteudo' => $this->conteudo,
            'httpGet' => $this->httpGet,
            'page' => $this->page,
            'action' => $this->action,
            'admin' => $this->admin,
            'ajax' => $this->ajax,
        ];
    }

    /**
     * Processar serviços da página
     *
     * @param array $servicos
     * @throws MBException
     */
    private function processarServicos(array $servicos)
    {
        foreach ($servicos as $configuracao) {
            $servico = Service::factory($configuracao);

            foreach ($configuracao['metodos'] as $metodos) {
                $nomeMetodo = "{$metodos}Dispatcher";

                if (method_exists($servico, $nomeMetodo)) {
                    $servico->mocabonita($this->dadosRequisicao);
                    $servico->{$nomeMetodo}();
                }
            }
        }
    }

    /**
     * Desabilitar qualquer tipo de cache da página durante o acesso em modo de desenvolvimento
     *
     */
    private function desabilitarCaches()
    {
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
        header("Pragma: no-cache"); // HTTP 1.0.
        header("Expires: 0"); // Proxies.
    }

    /**
     * Verificar se é a página atual é uma página do plugin
     *
     * @return bool
     */
    public function isPaginaPlugin()
    {
        if (is_null($this->paginaPlugin)) {
            $this->paginaPlugin = in_array($this->page, array_keys($this->paginas));
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
     * @param string $servico
     * @param array $metodos
     * @return MocaBonita
     */
    public function adicionarServicosPlugin($servico, array $metodos)
    {
        $this->servicosPlugin[] = Service::configuracoesServicos($servico, $metodos);
        return $this;
    }

    /**
     * @param string $servico
     * @param array $metodos
     * @return MocaBonita
     */
    public function adicionarServicosWordPress($servico, array $metodos)
    {
        $this->servicosWordPress[] = Service::configuracoesServicos($servico, $metodos);
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
        if ($this->isAdmin()) {
            foreach ($this->paginas as $pagina) {
                $pagina->adicionarMenuWordpress();
            }
        }
    }
}
