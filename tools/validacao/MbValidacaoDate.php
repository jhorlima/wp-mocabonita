<?php

namespace MocaBonita\tools\validacao;

use Exception;


/**
 * Validação de Data.
 *
 * timezone (string): Timezone da classe
 * format (string): Formato de retorno
 * timestamp (bool) : Retornar em timestamp
 *
 */
class MbValidacaoDate extends MbModeloValidacao
{
    /**
     * @param mixed $valor valor para validar
     * @param array $argumentos argumentos para validar
     * @throws \Exception caso ocorra algum erro
     *
     * @return \DateTime|integer|string $valor valor com ou sem mascara
     */
    public function validar($valor, array $argumentos = [])
    {
        $isTimestamp = is_string($valor) ? strtotime($valor) : false;
        $timezone    = isset($argumentos['timezone'])  ? $argumentos['timezone']  : false;
        $format      = isset($argumentos['format'])    ? $argumentos['format']    : false;
        $timestamp   = isset($argumentos['timestamp']) ? (bool) $argumentos['timestamp'] : false;

        if (!$isTimestamp && !$valor instanceof \DateTime) {
            throw new Exception("O atributo '{$this->getAtributo()}' não é uma data válida!");
        } elseif ($isTimestamp){
            $valor = (new \DateTime())->setTimestamp($valor);
        }

        if ($timezone && is_string($timezone)){
            $valor->setTimezone(new \DateTimeZone($timezone));
        }

        if ($format && is_string($format)){
            $valor = $valor->format($format);

            if (!$valor) {
                throw new Exception("O atributo '{$this->getAtributo()}' têm formato de data inválida!");
            }
        }

        if ($timestamp){
            $valor = $valor->getTimestamp();
        }

        return $valor;
    }
}