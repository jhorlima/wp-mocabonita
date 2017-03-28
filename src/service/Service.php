<?php

namespace MocaBonita\service;

use MocaBonita\tools\MbException;
use MocaBonita\tools\MbRequisicoes;
use MocaBonita\tools\MbRespostas;

/**
 * Classe de gerenciamento de services do moçabonita.
 *
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\service
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
abstract class Service
{

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
     * @return MbRequisicoes
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param MbRequisicoes $request
     *
     * @return Service
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
     *
     * @return Service
     */
    public function setResponse(MbRespostas $response)
    {
        $this->response = $response;
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
     * @return Service
     * @throws MbException
     */
    public static function create($class)
    {
        $servico = new $class();

        if (!$servico instanceof Service){
            throw new MbException("O Serviço {$class} não extendeu o Service do MocaBonita!");
        }

        return $servico;
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

    /**
     * Array de configuração de Services
     *
     * @param string $servico
     * @param array $metodos
     * @return array
     */
    public final static function configuracoesServicos($servico, array $metodos)
    {
        return [
            'class' => $servico,
            'service' => null,
            'metodos' => $metodos,
        ];
    }

    /**
     * Processar serviços da página
     *
     * @param array $servicos
     * @param MbRequisicoes $request
     * @param MbRespostas $response
     *
     * @throws MbException
     */
    public static function processarServicos(array $servicos, MbRequisicoes $request, MbRespostas $response)
    {
        foreach ($servicos as $configuracao) {
            $servico = Service::create($configuracao['class']);

            foreach ($configuracao['metodos'] as $metodos) {
                $nomeMetodo = "{$metodos}Dispatcher";

                if (method_exists($servico, $nomeMetodo)) {
                    $servico->setRequest($request);
                    $servico->setResponse($response);
                    $servico->{$nomeMetodo}($request, $response);
                }
            }
        }
    }
}
