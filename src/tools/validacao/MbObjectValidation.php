<?php

namespace MocaBonita\tools\validacao;

use Exception;


/**
 * Validação de Números Números, valor minimo e maximo podendo ser definido como argumentos.
 *
 * instanceof (string | object): Instancia da classe
 *
 */
class MbObjectValidation extends MbValidationBase
{
    /**
     * @param mixed $valor valor para validar
     * @param array $argumentos argumentos para validar
     * @throws \Exception caso ocorra algum erro
     *
     * @return object $valor valor com ou sem mascara
     */
    public function validar($valor, array $argumentos = [])
    {
        $isObjeto   = is_object($valor);
        $instanceOf = isset($argumentos['instanceof'])  ? $argumentos['instanceof'] : false;

        if (!$isObjeto) {
            throw new Exception("O atributo '{$this->getAtributo()}' não é um objeto!");
        }

        if ($instanceOf){
            if(is_string($instanceOf)){
                //
            } elseif (is_object($instanceOf)){
                $instanceOf = get_class($instanceOf);
            } else {
                throw new Exception("O InstanceOf do atributo '{$this->getAtributo()}' não é válido!");
            }

            if (!$valor instanceof $instanceOf) {
                throw new Exception("O atributo '{$this->getAtributo()}' não é uma instância de '{$instanceOf}'!");
            }
        }

        return $valor;
    }
}