<?php
namespace MocaBonita\view;

use MocaBonita\tools\MbDiretorios;
use MocaBonita\tools\MbRequisicoes;
use MocaBonita\tools\MbRespostas;

/**
 * Classe de View do MocaBonita
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\View
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class View
{

    /**
     * Nome do layout da view
     *
     * @var string
     */
    protected $template;

    /**
     * Nome da página atual, respectivamente o nome da pasta onde se encontra a view
     *
     * @var string
     */
    protected $page;

    /**
     * Nome da ação atual, respectivamente o nome da view na pasta da página
     *
     * @var string
     */
    protected $action;

    /**
     * Conjunto de variáveis que serão criadas na view enviadas pela controller
     *
     * @var string[]
     */
    protected $variaveis;

    /**
     * Conteudo da view processado
     *
     * @var string
     */
    protected $conteudo;

    /**
     * Extensão da view atual
     *
     * @var string
     */
    protected $extensao;

    /**
     * Caminho atual da pasta view
     *
     * @var string
     */
    protected $caminhoView;

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
     * Construtor da View.
     */
    public function __construct()
    {
        $this->variaveis = [];
        $this->conteudo = "";
        $this->extensao = "phtml";
        $this->caminhoView = MbDiretorios::PLUGIN_VIEW_DIR;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * É recomendado que no template seja chamado a função $this->getConteudo()
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
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
     */
    public function setPage($page)
    {
        $this->page = $page;
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
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string[]
     */
    public function getVariaveis()
    {
        return $this->variaveis;
    }

    /**
     * @param string[] $variaveis
     */
    public function setVariaveis(array $variaveis)
    {
        $this->variaveis = $variaveis;
    }

    /**
     * @return string
     */
    public function getConteudo()
    {
        return $this->conteudo;
    }

    /**
     * @param string $conteudo
     */
    public function setConteudo($conteudo)
    {
        $this->conteudo = $conteudo;
    }

    /**
     * @return string
     */
    public function getExtensao()
    {
        return $this->extensao;
    }

    /**
     * @param string $extensao
     */
    public function setExtensao($extensao)
    {
        $this->extensao = $extensao;
    }

    /**
     * @return string
     */
    public function getCaminhoView()
    {
        return $this->caminhoView;
    }

    /**
     * @param string $caminhoView
     */
    public function setCaminhoView($caminhoView)
    {
        $this->caminhoView = $caminhoView;
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
     * @return View
     */
    public function setRequest(MbRequisicoes $request)
    {
        $this->request = $request;
        return $this;
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
     * @return View
     */
    public function setResponse(MbRespostas $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Definir atributos da view em uma unica instância
     *
     * @param string $template Template da página atual, é recomendado que no template seja chamado a função $this->getConteudo()
     * @param string $page Pasta da view
     * @param string $action Nome da view
     * @param array $variaveis Variaveis da view
     * @param string $extensao Extensão da view padrão
     * @return View
     */
    public function setView($template, $page, $action, array $variaveis = [], $extensao = "phtml")
    {
        $this->setTemplate($template);
        $this->setPage($page);
        $this->setAction($action);
        $this->setVariaveis($variaveis);
        $this->setExtensao($extensao);
        return $this;
    }

    /**
     * Processar o caminho da view ou template
     *
     * @param string $tipo Tipo de caminho para ser criado
     * @return string
     */
    private function processarCaminho($tipo = 'action')
    {
        if ($tipo == 'action')
            return $this->caminhoView . "{$this->page}/{$this->action}.{$this->extensao}";
        else
            return $this->caminhoView . "{$this->template}.{$this->extensao}";
    }

    /**
     * Processar a view no template com as váriaveis definidas
     *
     * Apos esse processo, todos os dados processados estarão na variável $conteudo e a view será exibida
     *
     */
    public function render()
    {
        //Obter caminhos da view e template respectivamente
        $caminhoView = $this->processarCaminho();
        $caminhoTemplate = $this->processarCaminho('template');

        //Atribuir variaveis definidas para a view e template
        foreach ($this->variaveis as $attr => $value) {
            if (is_string($attr)){
                $$attr = $value;
            }
        }

        //Verificar se a view existe e processa-la
        if (file_exists($caminhoView)) {
            ob_start();
            include $caminhoView;
            $conteudo = ob_get_contents();
            ob_end_clean();
        } else //Caso o arquivo não exista, enviar um erro para a tela do wordpress
            $conteudo = "<div class='notice notice-error'>
                            <p>O arquivo <strong>{$caminhoView}</strong> não foi encontrado!</p>
                         </div>";

        //Atribuir a view processada para o conteudo
        $this->setConteudo($conteudo);

        //Verificar se o template existe e processa-lo
        if (file_exists($caminhoTemplate)) {
            ob_start();
            include $caminhoTemplate;
            $conteudo = ob_get_contents();
            ob_end_clean();
        } else //Caso o arquivo não exista, enviar um erro para a tela do wordpress
            $conteudo = "<div class='notice notice-error'>
                            <p>O arquivo <strong>{$caminhoTemplate}</strong> de template não foi encontrado!</p>
                         </div>";

        //Mostrar o conteudo na tela
        return $conteudo;
    }

}