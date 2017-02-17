<?php

namespace MocaBonita\controller;

use MocaBonita\tools\HTTPRespostas;
use MocaBonita\tools\ServicosJSON;
use MocaBonita\view\View;

/**
 * Gerenciamento de requisições do moça bonita
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package moca_bonita\controller
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
abstract class Requisicoes
{

    /**
     * Um array associativo de variáveis passadas para o script atual via o método HTTP GET
     *
     * @var array[]
     */
    protected $httpGet = [];

    /**
     * Um array associativo de variáveis passados para o script atual via método HTTP POST, PUT ou DELETE
     * quando utilizado application/x-www-form-urlencoded ou multipart/form-data como valor do cabeçalho
     * HTTP Content-Type na requisição ou RAW Data enviando um JSON
     *
     * @var array[]
     */
    protected $conteudo;

    /**
     * Contém o método de request utilizando para acessar a página. Geralmente 'GET', 'POST', 'PUT' ou 'DELETE'.
     *
     * @var string
     */
    protected $metodoRequisicao;

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
     * Contém a informação se está em uma página administrativa do Wordpress
     *
     * @var boolean
     */
    protected $admin;

    /**
     * Contém a informação se está em uma página ajax do Wordpress
     *
     * @var boolean
     */
    protected $ajax;

    /**
     * Contém a informação se alguém está logado
     *
     * @var boolean
     */
    protected $login;

    /**
     * Construtor da Class
     *
     */
    public function __construct()
    {
        //Obter o método de requisição atual
        $this->metodoRequisicao = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        //Obter o a página de requisição atual
        $this->page = isset($_GET['page']) ? $_GET['page'] : null;
        //Obter a ação atual e depois valida-la
        $this->action = isset($_GET['action']) ? $_GET['action'] : null;
        //Obter a resposta se a página é administrativa
        $this->admin = (bool)is_admin();
        //Obter a resposta se o usuario tá logado
        $this->login = (bool)is_user_logged_in();
        //Obter a resposta se a página é ajax
        $this->ajax = defined('DOING_AJAX') && DOING_AJAX;
        //Obter dados da requisição
        $this->obterDadosRequisicao();
    }

    /**
     * @return boolean
     */
    public function isAdmin()
    {
        return (bool)$this->admin;
    }

    /**
     * Verificar se a requisição é ajax
     *
     * @return true|false true se a requisição for ajax, false se a requisição não for ajax
     */
    public function isAjax()
    {
        return (bool)$this->ajax;
    }

    /**
     * @return boolean
     */
    public function isLogin()
    {
        return $this->login;
    }

    /**
     * @param boolean $login
     * @return Requisicoes
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * Obter dados da requisição
     *
     */
    private function obterDadosRequisicao()
    {
        //Inicializar o conteudo como array vazio, para posteriormente adicionar
        $this->conteudo = [];
        //Inicializar o de httpGet através da váriavel global $_GET
        $this->httpGet = $_GET;

        //Verificar se o método de requisição é POST e obtém o conteudo se existir. A requisição RAW DATA tem prioridade
        if ($this->isPost()) {
            $post = file_get_contents('php://input');

            if (ServicosJSON::verificarJSON($post)) {
                $_POST = json_decode($post, true);
            }

            $this->conteudo = $_POST;
        }

        //Verificar se o método de requisição é PUT e obtém o conteudo se existir
        if ($this->isPut()) {
            $put = file_get_contents("php://input");

            if (ServicosJSON::verificarJSON($put)) {
                $this->conteudo = ServicosJSON::decodificar($put);
            } else {
                $this->conteudo = [];
            }
        }

        //Verificar se o método de requisição é DELETE e obtém o conteudo se existir
        if ($this->isDelete()) {
            $delete = file_get_contents("php://input");

            if (ServicosJSON::verificarJSON($delete)) {
                $this->conteudo = ServicosJSON::decodificar($delete);
            } else {
                $this->conteudo = [];
            }
        }
    }

    /**
     * Verificar se o método de requisição é 'GET'
     *
     * @return true|false true se o método de requisição for GET, false se o método de requisição for 'GET'
     */
    public function isGet()
    {
        return $this->metodoRequisicao === 'GET';
    }

    /**
     * Verificar se o método de requisição é 'POST'
     *
     * @return true|false true se o método de requisição for POST, false se o método de requisição for 'POST'
     */
    public function isPost()
    {
        return $this->metodoRequisicao === 'POST';
    }

    /**
     * Verificar se o método de requisição é 'PUT'
     *
     * @return true|false true se o método de requisição for PUT, false se o método de requisição for 'PUT'
     */
    public function isPut()
    {
        return $this->metodoRequisicao === 'PUT';
    }

    /**
     * Verificar se o método de requisição é 'DELETE'
     *
     * @return true|false true se o método de requisição for DELETE, false se o método de requisição for 'DELETE'
     */
    public function isDelete()
    {
        return $this->metodoRequisicao === 'DELETE';
    }

    /**
     * Enviar mensagem para o navegador
     *
     * @param mixed $dados Resposta para enviar ao navegador
     */
    protected function enviarDadosNavegador($dados)
    {
        //Verificar se a página atual é ajax
        if ($this->isAjax()) {
            //Se os dados for um array, é convertido para JSON na estrutura do Moca Bonita
            if (is_array($dados)) {
                ServicosJSON::respostaHTTP($dados, $this);
            } //Se os dados for uma string, é adicionado ao atributo content do Moça Bonita
            elseif (is_string($dados)) {
                ServicosJSON::respostaHTTP(['content' => $dados], $this);
            } //Se não for array ou string, então retorna vázio
            else {
                ServicosJSON::respostaHTTP(
                    HTTPRespostas::obterHttpResposta(HTTPRespostas::REQUEST_UNAVAIABLE, 'Nenhum dado foi retornado!'),
                    $this
                );
            }

            //Verificar se os dados é um objeto View e renderiza a página
        } elseif ($dados instanceof View)
            $dados->render();

        //Verificar se os dados é uma string e é mostrada na página
        elseif (is_string($dados))
            echo $dados;

        //Se os dados não atender nenhum dos requisitos, é feito uma depuração
        else
            var_dump($dados);
    }

}