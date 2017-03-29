<?php

namespace MocaBonita\tools\validacao;

use Exception;


/**
 * Validação e Filtro de String e contagem de seus caracteres podendo ser definido como argumentos.
 *
 * min (int): Número minimo de caracteres
 * max (int): Número máximo de caracteres (precisa definir o min)
 * trim (bool): Fazer o trim na string
 * striptags (string | string[]): Filtro de TAGS HTML e Tags permitidas
 * mask (string): Aplicar mascara a string, ex: (##) ####-####
 * str_lower (bool) : Filtro para formatar a string para minuscula.
 * str_upper (bool) : Filtro para formatar a string para maiuscula.
 * alpha_numeric (bool) : Filtro para formatar alphanumeric na string.
 * email (bool) : Validar se a string é um e-mail válido.
 * html_escape (bool) : Converte caracteres especiais para a realidade HTML
 *
 */
class MbValidacaoString extends MbModeloValidacao
{
    /**
     * @param mixed $valor valor para validar
     * @param array $argumentos argumentos para validar
     * @throws \Exception caso ocorra algum erro
     *
     * @return string $valor valor com ou sem mascara
     */
    public function validar($valor, array $argumentos = [])
    {
        $isString = is_string($valor);
        $min = isset($argumentos['min']) ? $argumentos['min'] : false;
        $max = isset($argumentos['max']) ? $argumentos['max'] : false;
        $trim = isset($argumentos['trim']) ? (bool)$argumentos['trim'] : false;
        $striptags = isset($argumentos['striptags']) ? $argumentos['striptags'] : false;
        $mask = isset($argumentos['mask']) ? $argumentos['mask'] : false;
        $strLower = isset($argumentos['str_lower']) ? (bool) $argumentos['str_lower'] : false;
        $strUpper = isset($argumentos['str_upper']) ? (bool) $argumentos['str_upper'] : false;
        $alphaNumeric = isset($argumentos['alpha_numeric']) ? (bool) $argumentos['alpha_numeric'] : false;
        $email = isset($argumentos['email']) ? (bool) $argumentos['email'] : false;
        $htmlEscape = isset($argumentos['html_escape']) ? (bool) $argumentos['html_escape'] : false;

        if (!$isString) {
            throw new Exception("O atributo '{$this->getAtributo()}' não é um string!");
        }

        if ($trim) {
            $valor = trim($valor);
            $valor = preg_replace('/\s+/', ' ', $valor);
        }

        if($striptags){
            if (is_string($striptags)) {
                $valor = strip_tags($valor, $striptags);
            } elseif(is_array($striptags)){
                $valor = strip_tags($valor, implode("", $striptags));
            }
        }

        if($strLower){
            $valor = strtolower($valor);
        }

        if($strUpper){
            $valor = strtoupper($valor);
        }

        if($htmlEscape){
            $valor = htmlspecialchars($valor);
        }

        if ($alphaNumeric){
            $valor = preg_replace("/[^a-zA-Z0-9]+/", "", $valor);
        }

        if ($alphaNumeric){
            $valor = preg_replace("/[^a-zA-Z0-9]+/", "", $valor);
        }

        if ($email && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("O atributo '{$this->getAtributo()}' não é um e-mail válido!");
        }

        $qntCaracteres = strlen($valor);

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

            if ($qntCaracteres < $min) {
                throw new Exception(
                    "O atributo '{$this->getAtributo()}' deve ter no minimo '{$min}' caractere(s)!"
                );
            } elseif ($max && $qntCaracteres > $max) {
                throw new Exception(
                    "O atributo '{$this->getAtributo()}' deve ter no maximo '{$max}' caracteres!"
                );
            }

        }

        if($mask && is_string($mask)){
            $valorNovo = '';
            $k = 0;
            for($i = 0; $i<=strlen($mask)-1; $i++)
            {
                if($mask[$i] == '#')
                {
                    if(isset($valor[$k])){
                        $valorNovo .= $valor[$k++];
                    }
                }
                else
                {
                    if(isset($mask[$i])){
                        $valorNovo .= $mask[$i];
                    }
                }
            }
            $valor = $valorNovo;
        }

        return $valor;
    }
}