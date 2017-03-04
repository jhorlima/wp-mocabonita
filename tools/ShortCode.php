<?php
namespace MocaBonita\tools;

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
class ShortCode
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
     * @var Acoes
     */
    private $acao;

    /**
     * Assets do Shortcode
     *
     * @var Assets
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
     * @return ShortCode
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return Acoes
     */
    public function getAcao()
    {
        return $this->acao;
    }

    /**
     * @param Acoes $acao
     * @return ShortCode
     */
    public function setAcao(Acoes $acao)
    {
        $this->acao = $acao;
        return $this;
    }

    /**
     * @return Assets
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param Assets $assets
     * @return ShortCode
     */
    public function setAssets(Assets $assets)
    {
        $this->assets = $assets;
        return $this;
    }

    /**
     * Adicionar Shortcode ao Wordpress
     *
     * @param string $nome nome do shortcode
     * @param Acoes $acao ação do shortcode
     * @param Assets $assets Assets do shortcode
     */
    public function __construct($nome, Acoes $acao, Assets $assets)
    {
        $this->setNome($nome)
            ->setAcao($acao)
            ->setAssets($assets);
    }

    public function processarShorcode(array $dadosPlugin)
    {
        //Adicionar a instancia da class para uma váriavel
        $shortCode = $this;

        //Inicializar Shorcode
        add_shortcode($this->getNome(), function ($atributos, $conteudo, $tags) use ($shortCode, $dadosPlugin) {

            //Adicionar assets do plugin
            $dadosPlugin['assets']->processarCssWordpress('plugin');
            $dadosPlugin['assets']->processarJsWordpress('plugin');

            //Adicionar assets do shortcode
            $shortCode->getAssets()->processarCssWordpress($shortCode->getNome());
            $shortCode->getAssets()->processarJsWordpress($shortCode->getNome());

            //Verificar se é uma ação valida
            if ($shortCode->getAcao()->metodoValido()) {

                //Carregar dados da controller
                $shortCode->getAcao()
                    ->getPagina()
                    ->getController()
                    ->mocabonita($dadosPlugin['dados_requisicao']);

                //Definir controller como shortcode
                $shortCode->getAcao()
                    ->getPagina()
                    ->getController()
                    ->setShortcode(true);

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

                //Começar a processar a controller
                ob_start();

                try{
                    $respostaController = $shortCode->getAcao()
                        ->getPagina()
                        ->getController()
                        ->{$shortCode->getAcao()->getMetodo()}($atributos, $conteudo, $tags);

                } catch (\Exception $e){
                    $respostaController = $e->getMessage();
                } finally {
                    $conteudoController = ob_get_contents();                
                }

                ob_end_clean();

                //Verificar se a controller imprimiu alguma coisa e exibir no errolog
                if ($conteudoController != ""){
                    error_log($conteudoController);
                }

                //Verificar se a resposta é nula e então ele pega a view da controller
                if(is_null($respostaController)){
                    $respostaController = $shortCode->getAcao()
                        ->getPagina()
                        ->getController()
                        ->getView();
                }

                //Verificar se o retorno é uma view e redenriza
                if ($respostaController instanceof View){
                    $respostaController->render();
                }

                //Verificar se a resposta é uma string e imprime
                elseif (is_string($respostaController)){
                    echo $respostaController;
                }

                //Mensagem quando o shortcode não atender os requisitos
                else {
                    echo "Nenhum conteudo foi retornado!";
                }

            } else {
                echo "O shortcode {$shortCode->getNome()} não foi definido!", "<br>";
                if ($dadosPlugin['em_desenvolvimento']){
                    echo "Shortcode: {$shortCode->getNome()}; Método: {$shortCode->getAcao()->getMetodo()}.";
                }
            }

        });
    }

}