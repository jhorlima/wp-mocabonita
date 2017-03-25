<?php

namespace MocaBonita\tools;

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
class Respostas extends Response
{

    /**
     * Processar resposta para o navegador
     *
     * @param mixed $dados Resposta para enviar ao navegador
     * @param Requisicoes $request Requisição da página
     *
     * @return void
     */
    public function processarResposta($resposta, Requisicoes $request){

        if($request->method() == "GET"){
            $this->statusCode = 200;
        } elseif($request->method() == "POST" || $request->method() == "PUT") {
            $this->statusCode = 201;
        } elseif ($resposta instanceof \Exception){
            $this->statusCode = $resposta->getCode();
        } else {
            $this->statusCode = 204;
        }

        //Verificar se a página atual é ajax
        if ($request->isAjax()) {

            //Se os dados for um array, é convertido para JSON na estrutura do Moca Bonita
            if (is_array($resposta)) {
                $resposta = $this->respostaJson($resposta);
            } //Se os dados for uma string, é adicionado ao atributo content do Moça Bonita
            elseif (is_string($resposta)) {
                $resposta = $this->respostaJson(['content' => $resposta]);
            } //Se não for array ou string, então retorna vázio
            elseif ($resposta instanceof \Exception) {
                $resposta = $this->respostaJson($resposta);
            } else {
                $resposta = $this->respostaJson(new \Exception("Nenhum conteúdo foi enviado!"));
            }
            //Caso a requisição não seja ajax
        } else {
            //Caso a resposta seja uma exception
            if($resposta instanceof \Exception){
                MBException::adminNotice($resposta);
                //Caso seja uma view
            } elseif ($resposta instanceof View){
                $resposta = $resposta->render();
                //Caso seja algum valor diferente de string
            } elseif (!is_string($resposta)){
                ob_start();
                var_dump($resposta);
                $resposta = ob_get_contents();
                ob_end_clean();
            }
        }

        //Tratar resposta
        $this->setContent($resposta);

        //Tratar cabeçalho
        $this->processarHeaders();

        //Imprimir conteudo
        echo $this->getContent();
    }

    /**
     * Redirecionar uma página
     *
     * @param string $url
     */
    public function redirect($url){
        header("Location: {$url}");
        exit();
    }

    /**
     * Transformar o array em JSON e formatar o retorno
     *
     * @param array|\Exception $dados Os dados para resposta do Moça Bonita
     */
    private function respostaJson($dados)
    {
        //Callback de resposta de sucesso do Moça Bonita
        $respostaSucesso = function ($codigo) use (&$dados) {
            return [
                'meta' => ['code' => $codigo],
                'data' => $dados,
            ];
        };

        //Callback de resposta de erro do Moça Bonita
        $respostaErro = function ($codigo) use (&$dados) {
            return [
                'meta' => [
                    'code' => (int) $codigo,
                    'error_message' => $dados->getMessage(),
                ],
            ];
        };

        return $dados instanceof \Exception ? $respostaErro($this->statusCode) : $respostaSucesso($this->statusCode);
    }

    public function processarHeaders(){
        $headers = $this->headers->all();

        foreach ($headers as $key => &$header){
            header("{$key}: {$header[0]}");
        }
    }
}