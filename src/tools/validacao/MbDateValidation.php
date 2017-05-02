<?php

namespace MocaBonita\tools\validacao;

use Exception;
use Carbon\Carbon;


/**
 * Validação de Data.
 *
 * timezone (string): Timezone da classe
 * formato_entrada (string): Formato de entrada da data
 * formato_saida (string): Formato de retorno da data
 * timestamp (bool) : Retornar em timestamp
 *
 */
class MbDateValidation extends MbValidationBase
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
        $timezone = isset($argumentos['timezone']) ? $argumentos['timezone'] : false;
        $formatoEntrada = isset($argumentos['formato_entrada']) ? $argumentos['formato_entrada'] : false;
        $formatoSaida = isset($argumentos['formato_saida']) ? $argumentos['formato_saida'] : false;
        $timestamp = isset($argumentos['timestamp']) ? (bool)$argumentos['timestamp'] : false;

        if(is_string($formatoEntrada)){
            $valor = is_string($valor) ? Carbon::createFromFormat($formatoEntrada, $valor) : false;
        } else {
            try{
                $valor = is_string($valor) ? Carbon::parse($valor) : false;
            } catch (\Exception $e){
                $valor = false;
            }
        }

        if (!$valor instanceof Carbon) {
            throw new Exception("O atributo '{$this->getAtributo()}' não é uma data válida!");
        }

        if (!is_string($timezone)) {
            $timezone = get_option('timezone_string');
            $timezone = !empty($timezone) ? $timezone : 'America/Fortaleza';
        }

        $valor->setTimezone($timezone);

        if ($formatoSaida && is_string($formatoSaida)) {
            $valor = $valor->format($formatoSaida);

            if (!$valor) {
                throw new Exception("O atributo '{$this->getAtributo()}' têm formato de data inválida!");
            }
        }

        if ($timestamp) {
            $valor = $valor->getTimestamp();
        }

        return $valor;
    }
}