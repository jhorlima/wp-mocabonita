<?php

namespace MocaBonita\controller;

use MocaBonita\tools\Respostas;
use MocaBonita\tools\Requisicoes;
use MocaBonita\tools\MBException;
use MocaBonita\view\View;

/**
 * Classe de gerenciamento de controller do moçabonita.
 *
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\controller
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
abstract class Controller
{

    /**
     * Contém a view atual da ação da controller.
     *
     * @var View
     */
    protected $view;

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
     * Ação principal da controller
     *
     * Se o retorno for null, ele irá chamar a view desta controller e redenrizar
     * Se o retorno for string, ele irá imprimir a string na tela
     * Se o retorno for View, ele irá redenrizar a view desta controller
     * Se o retorno for qualquer outro tipo, ele irá fazer um var_dump do retorno
     *
     * @param Requisicoes $request
     * @param Respostas $response
     *
     * @return null|string|View|void
     */
    public function indexAction(Requisicoes $request, Respostas $response)
    {

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
     *
     * @return Controller
     */
    public function setRequest(Requisicoes $request)
    {
        $this->request = $request;
        return $this;
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
     *
     * @return Controller
     */
    public function setResponse(Respostas $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return View
     */
    public final function getView()
    {
        return $this->view;
    }

    /**
     * @param View $view
     *
     * @return Controller
     */
    public final function setView(View $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @return string
     */
    public final function getMetodoRequisicao()
    {
        return $this->request->method();
    }

    /**
     * Receber conteudo enviado no corpo da requisição
     *
     * @param string|null $key
     * @return array|string|null
     */
    public final function getConteudo($key = null)
    {
        if (is_null($key)) {
            return $this->request->input();
        } elseif ($this->request->has($key)) {
            return $this->request->input($key);
        } else {
            return null;
        }
    }

    /**
     * @param string|null $key
     * @return array|string|null
     */
    public final function getHttpGet($key = null)
    {
        if (is_null($key)) {
            return $this->request->query();
        } elseif (!is_null($this->request->query($key))) {
            return $this->request->query($key);
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public final function getPage()
    {
        return $this->request->query('page');
    }

    /**
     * @return string
     */
    public final function getAction()
    {
        return $this->request->query('action');
    }

    /**
     * @return bool
     */
    public final function isAdmin()
    {
        return $this->request->isAdmin();
    }

    /**
     * @return bool
     */
    public final function isAjax()
    {
        return $this->request->isAjax();
    }

    /**
     * @return bool
     */
    public final function isShortcode()
    {
        return $this->request->isShortcode();
    }

    /**
     * Redirecionar uma página
     *
     * @param string $url
     */
    protected final function redirect($url, array $params = [])
    {
        if (is_string($url)) {
            $url .= !empty($params) ? "?" . http_build_query($params) : "";
            $this->response->redirect($url);
        }
    }

    /**
     * Construtor de Controller
     *
     * @throws MBException
     * @return Controller
     */
    public static function create($class)
    {
        $controller = new $class();

        if (!$controller instanceof Controller) {
            throw new MBException("O Controller {$class} não extendeu o Controller do MocaBonita!");
        }

        return $controller;
    }

    /**
     * Método clone do tipo privado previne a clonagem dessa instância
     * da classe
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Método unserialize do tipo privado para prevenir a desserialização
     * da instância dessa classe.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
