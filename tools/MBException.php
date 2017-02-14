<?php

namespace MocaBonita\tools;

use MocaBonita\controller\Requisicoes;

/**
 * Classe de Exceção do Moça Bonita.
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\Tools
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class MBException extends \Exception
{
    /**
     * objeto Requisições
     *
     * @var Requisicoes
     */
    private $requisicoes;

    /**
     * @return Requisicoes
     */
    public function getRequisicoes()
    {
        return $this->requisicoes;
    }

    /**
     * @param Requisicoes $requisicoes
     * @return MBException
     */
    public function setRequisicoes(Requisicoes $requisicoes)
    {
        $this->requisicoes = $requisicoes;
        return $this;
    }

    /**
     * Processar a Exceção para retornar ao navegador os dados
     *
     * @return void
     */
    public function processarExcecao(){
        if($this->requisicoes->isAjax())
            ServicosJSON::respostaHTTP(HTTPRespostas::obterHttpResposta(
                HTTPRespostas::REQUEST_UNAVAIABLE,
                $this->getMessage()
            ), $this->requisicoes);
        else
            echo "<div class='notice notice-error'><p>{$this->getMessage()}</p></div>";
        return null;
    }
}