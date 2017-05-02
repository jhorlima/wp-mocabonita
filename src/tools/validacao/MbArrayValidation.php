<?php

namespace MocaBonita\tools\validacao;

use Exception;


/**
 * Validação de Array, vaor minimo e maximo de elementos podendo ser definido como argumentos.
 *
 * json (bool) : Caso o array esteja em json(string)
 * min (float) : Menor quantidade de elementos do array
 * max (float) : Maior quantidade de elementos do array (precisa definir o min)
 * validar (string | function($elemento)) : Função para validar os elementos do array
 *
 */
class MbArrayValidation extends MbValidationBase
{
    /**
     * @param mixed $valor valor para validar
     * @param array $argumentos argumentos para validar
     * @throws \Exception caso ocorra algum erro
     *
     * @return array $valor valor com ou sem mascara
     */
    public function validar($valor, array $argumentos = [])
    {
        $min     = isset($argumentos['min'])      ? $argumentos['min']         : false;
        $max     = isset($argumentos['max'])      ? $argumentos['max']         : false;
        $validar = isset($argumentos['validar'])  ? $argumentos['validar']     : false;
        $json    = isset($argumentos['json'])     ? (bool) $argumentos['json'] : false;

        if($json && $this->isJson($valor)){
            $valor = json_decode($valor);
        }

        $isArray = is_array($valor);

        if (!$isArray) {
            throw new Exception("O atributo '{$this->getAtributo()}' não é um array!");
        }

        $qntElem = count($valor);

        if ($min && is_numeric($min)) {
            $min = intval($min);
        } else {
            $min = false;
        }

        if ($max && is_numeric($max)) {
            $max = intval($max);
        } else {
            $max = false;
        }

        if ($min) {

            if ($qntElem < $min) {
                throw new Exception("O atributo '{$this->getAtributo()}' deve ter no minimo '{$min}' elemento(s)!");
            } elseif ($max && $qntElem > $max) {
                throw new Exception("O atributo '{$this->getAtributo()}' deve ter no máximo '{$max}' elemento(s)!");
            }
        }

        if ($validar) {
            $false = 0;

            if (is_string($validar) && function_exists($validar)) {
                foreach ($valor as $item){
                    if(!call_user_func_array($validar, $item)){
                        $false++;
                    }
                }
            } elseif (is_callable($validar)) {
                foreach ($valor as $item){
                    if(!$validar($item)){
                        $false++;
                    }
                }
            } else {
                throw new Exception("Não foi possivel aplicar a validação no atributo '{$this->getAtributo()}'!");
            }

            if($false > 0){
                throw new Exception("{$false} elemento(s) do atributo'{$this->getAtributo()}' não passou(passaram) na validação!");
            }
        }

        return $valor;
    }

    /**
     * @param $string
     * @return bool
     * @throws Exception
     */
    protected function isJson($string){
        if(!is_string($string)){
            throw new Exception("O atributo '{$this->getAtributo()}' não é um JSON válido!");
        }
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}