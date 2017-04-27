<?php

namespace MocaBonita\tools;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Response;
use MocaBonita\view\View;

/**
 * Gerenciamento de respostas do moça bonita
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package moca_bonita\tools
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class MbRespostas extends Response
{

    /**
     * Váriavel que armazenda o request
     *
     * @var MbRequisicoes
     */
    protected $request;

    /**
     * @var string
     */
    protected $content;

    /**
     * @return MbRequisicoes
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param MbRequisicoes $request
     * @return MbRespostas
     */
    public function setRequest(MbRequisicoes $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Processar resposta para o navegador
     *
     * @param mixed $content
     * @return MbRespostas
     * @internal param mixed $dados Resposta para enviar ao navegador
     */
    public function setContent($content)
    {
        if(is_null($this->request)){
            return $this;
        } elseif ($this->request->isMethod("GET")) {
            $this->statusCode = 200;
        } elseif ($this->request->isMethod("POST") || $this->request->isMethod("PUT")) {
            $this->statusCode = 201;
        } else {
            $this->statusCode = 204;
        }

        if ($content instanceof \Exception) {
            $this->statusCode = $content->getCode();
            $this->statusCode = $this->statusCode < 300 ? 400 : $this->statusCode;
        }

        //Verificar se a página atual é ajax
        if ($this->request->isAjax()) {
            $this->respostaAjax($content);
            //Caso a requisição não seja ajax
        } else {
            $this->respostaHtml($content);
        }

        return $this;
    }

    /**
     * Enviar o conteudo pra página
     *
     */
    public function sendContent()
    {
        if ($this->request->isAjax()) {
            wp_send_json($this->original, $this->statusCode);
        } else {
            parent::sendContent();
        }
    }

    /**
     * Redirecionar uma página
     *
     * @param string $url
     */
    public function redirect($url)
    {
        header("Location: {$url}");
        exit();
    }

    /**
     * Transformar o array em JSON e formatar o retorno
     *
     * @param array|\Exception $dados Os dados para resposta do Moça Bonita
     *
     * @return array[]
     */
    protected function respostaAjax($dados)
    {
        $message = null;

        $this->header('Content-Type', 'application/json');

        if ($dados instanceof Arrayable) {
            $dados = $dados->toArray();

        } //Se os dados for uma string, é adicionado ao atributo content do Moça Bonita
        elseif (is_string($dados)) {
            $dados = ['content' => $dados];

        } //Se não for array ou string, então retorna vázio
        elseif (!is_array($dados) && !$dados instanceof \Exception) {
            return $this->respostaAjax(new \Exception("Nenhum conteúdo válido foi enviado!"));

        } elseif ($dados instanceof \Exception) {
            $message = $dados->getMessage();
            $dados   = $dados instanceof MbException ? $dados->getDadosArray() : null;
        }

        $this->original = [
            'meta' => [
                'code' => $this->getStatusCode(),
                'message' => $message
            ],
            'data' => $dados,
        ];

        return $this->original;

    }

    /**
     * Gerar resposta html
     *
     * @param $dados
     * @return string
     */
    protected function respostaHtml($dados)
    {
        if ($dados instanceof \Exception) {
            $dados = "<div class='notice notice-error'><p>{$dados->getMessage()}</p></div>";

        } //Caso seja uma view
        elseif ($dados instanceof View) {
            $dados = $dados->render();

        } //Caso seja algum valor diferente de string
        elseif (!is_string($dados)) {
            ob_start();
            var_dump($dados);
            $dados = ob_get_contents();
            ob_end_clean();
        }

        parent::setContent($dados);

        return $dados;
    }
}