<?php
namespace MocaBonita\tools;

use MocaBonita\MocaBonita;
use MocaBonita\view\View;

/**
 * Classe de Shortcode do Wordpress
 *
 * @author Jhordan Lima
 * @category WordPress
 * @package \MocaBonita\Tools
 * @copyright Copyright (c) 2016
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class MbShortCode
{

    /**
     * Nome do Shortcode
     *
     * @var string
     */
    private $nome;

    /**
     * Ação do Shortcode
     *
     * @var MbAcoes
     */
    private $acao;

    /**
     * Assets do Shortcode
     *
     * @var MbAssets
     */
    private $assets;

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     * @return MbShortCode
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return MbAcoes
     */
    public function getAcao()
    {
        return $this->acao;
    }

    /**
     * @param MbAcoes $acao
     * @return MbShortCode
     */
    public function setAcao(MbAcoes $acao)
    {
        $this->acao = $acao;
        return $this;
    }

    /**
     * @return MbAssets
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param MbAssets $assets
     * @return MbShortCode
     */
    public function setAssets(MbAssets $assets)
    {
        $this->assets = $assets;
        return $this;
    }

    /**
     * Adicionar Shortcode ao Wordpress
     *
     * @param string $nome nome do shortcode
     * @param MbAcoes $acao ação do shortcode
     * @param MbAssets $assets Assets do shortcode
     */
    public function __construct($nome, MbAcoes $acao, MbAssets $assets)
    {
        $this->setNome($nome)
            ->setAcao($acao)
            ->setAssets($assets);
    }

    public function processarShorcode(MbAssets $assets, MbRequisicoes $request, MbRespostas $response)
    {
        //Adicionar a instancia da class para uma váriavel
        $shortCode = $this;

        //Inicializar Shorcode
        add_shortcode($this->getNome(), function ($atributos, $conteudo, $tags) use ($shortCode, $assets, $request, $response) {

            MbEventos::processarEventos(MocaBonita::getInstance(), MbEventos::BEFORE_SHORTCODE, $shortCode);

            $request->setShortcode(true);

            //Adicionar assets do plugin
            $assets->setActionEnqueue('front')->processarAssets('plugin');

            //Adicionar assets do shortcode
            $shortCode->getAssets()->setActionEnqueue('front')->processarAssets($shortCode->getNome());

            //Verificar se é uma ação valida
            if ($shortCode->getAcao()->metodoValido()) {

                try {
                    //Atribuir request e response pra view
                    $shortCode->getAcao()
                        ->getPagina()
                        ->getController()
                        ->setView(new View())
                        ->getView()
                        ->setRequest($request)
                        ->setResponse($response);

                    //Carregar dados da controller
                    $shortCode->getAcao()
                        ->getPagina()
                        ->getController()
                        ->setRequest($request)
                        ->setResponse($response);

                    //Definir controller como shortcode
                    $request->setShortcode(true);

                    //Definir template principal
                    $shortCode->getAcao()
                        ->getPagina()
                        ->getController()
                        ->getView()
                        ->setTemplate('shortcode');

                    //Definir página principal
                    $shortCode->getAcao()
                        ->getPagina()
                        ->getController()
                        ->getView()
                        ->setPage('shortcode');

                    //Definir shortcode metodo
                    $shortCode->getAcao()
                        ->getPagina()
                        ->getController()
                        ->getView()
                        ->setAction($shortCode->getAcao()->getNome());

                    try{
                        //Começar a processar a controller
                        ob_start();

                        MbEventos::processarEventos(MocaBonita::getInstance(), MbEventos::BEFORE_ACTION, $shortCode->getAcao());
                        $respostaController = $shortCode->getAcao()
                            ->getPagina()
                            ->getController()
                            ->{$shortCode->getAcao()->getMetodo()}($atributos, $conteudo, $tags);

                        MbEventos::processarEventos(MocaBonita::getInstance(), MbEventos::AFTER_ACTION, $shortCode->getAcao());

                    } catch (\Exception $e){
                        MbEventos::processarEventos(MocaBonita::getInstance(), MbEventos::EXCEPTION_ACTION, $e);
                        $respostaController = $e->getMessage();
                    } finally {
                        MbEventos::processarEventos(MocaBonita::getInstance(), MbEventos::FINISH_ACTION, $shortCode->getAcao());
                        $conteudoController = ob_get_contents();
                        ob_end_clean();
                    }

                    //Verificar se a controller imprimiu alguma coisa e exibir no errolog
                    if ($conteudoController != ""){
                        error_log($conteudoController);
                    }

                    //Verificar se a resposta é nula e então ele pega a view da controller
                    if(is_null($respostaController)){
                        $respostaController = $shortCode->getAcao()->getPagina()->getController()->getView();
                    }

                } catch (\Exception $e){
                    $respostaController = $e->getMessage();

                } finally {
                    //Processar a página
                    $response->setContent($respostaController);
                }

            } else {
                //Processar a página
                $response->setContent(
                    "O shortcode {$shortCode->getNome()} não foi definido! Método: {$shortCode->getAcao()->getMetodo()}."
                );
            }

            //Imprimir conteudo
            $response->sendContent();

            MbEventos::processarEventos(MocaBonita::getInstance(), MbEventos::AFTER_SHORTCODE, $shortCode);
        });
    }
}