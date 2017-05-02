<?php

namespace MocaBonita\controller;

use MocaBonita\tools\MbResponse;
use MocaBonita\tools\MbRequest;
use MocaBonita\tools\MbException;
use MocaBonita\view\MbView;

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
abstract class MbController
{

    /**
     * Contém a view atual da ação da controller.
     *
     * @var MbView
     */
    protected $view;

    /**
     * Váriavel que armazenda o request
     *
     * @var MbRequest
     */
    protected $request;

    /**
     * Váriavel que armazenda a resposta
     *
     * @var MbResponse
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
     * @param MbRequest $request
     * @param MbResponse $response
     *
     * @return null|string|MbView|void
     */
    public function indexAction(MbRequest $request, MbResponse $response)
    {
        return $this->view;
    }

    /**
     * @return MbRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param MbRequest $request
     *
     * @return MbController
     */
    public function setRequest(MbRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return MbResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param MbResponse $response
     *
     * @return MbController
     */
    public function setResponse(MbResponse $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return MbView
     */
    public final function getView()
    {
        return $this->view;
    }

    /**
     * @param MbView $view
     *
     * @return MbController
     */
    public final function setView(MbView $view)
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
     * @param array $params
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
     * @param $class
     * @return MbController
     * @throws MbException
     */
    public static function create($class)
    {
        $controller = new $class();

        if (!$controller instanceof MbController) {
            throw new MbException("O Controller {$class} não extendeu o Controller do MocaBonita!");
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
