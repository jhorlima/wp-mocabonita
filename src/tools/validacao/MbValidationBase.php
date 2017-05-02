<?php

namespace MocaBonita\tools\validacao;

use MocaBonita\tools\MbSingleton;

/**
 * Interface MbValidacaoMascaraInterface
 * @package ExemploPlugin\tools\validacao
 */
abstract class MbValidationBase extends MbSingleton
{
    /**
     * Nome do atributo atual
     *
     * @var
     */
    protected $atributo;

    /**
     * @return mixed
     */
    public function getAtributo()
    {
        return $this->atributo;
    }

    /**
     * @param mixed $atributo
     * @return MbValidationBase
     */
    public function setAtributo($atributo)
    {
        $this->atributo = $atributo;
        return $this;
    }

    /**
     * @param mixed $valor valor para validar
     * @param array $argumentos argumentos para validar
     * @throws \Exception caso ocorra algum erro
     *
     * @return mixed $valor valor com ou sem mascara
     */
    public abstract function validar($valor, array $argumentos = []);

}