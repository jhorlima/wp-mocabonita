<?php

namespace MocaBonita\tools\validation;

use Exception;

/**
 * String validation class
 *
 * @author Jhordan Lima <jhorlima@icloud.com>
 * @category WordPress
 * @package \MocaBonita\tools\validation
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 * @version 3.1.0
 *
 * List of possible arguments for this class:
 *
 * @uses $arguments['instanceof'] (string | object) : Class instance
 *
 * @uses $arguments['min'] (int): Minimum number of characters
 * @uses $arguments['max'] (int): Maximum number of characters (it requires the $arguments['min'])
 * @uses $arguments['trim'] (bool): Trim the string
 * @uses $arguments['striptags'] (string | string[]): HTML TAGS Filter and Allowed Tags
 * @uses $arguments['regex'] (string): Validation regex
 * @uses $arguments['mask'] (string): String mask, ex: (##) ####-####
 * @uses $arguments['str_lower'] (bool) : Format string to lowercase.
 * @uses $arguments['str_upper'] (bool) : Format string for uppercase.
 * @uses $arguments['alpha_numeric'] (bool) : Format alphanumeric in string.
 * @uses $arguments['email'] (bool) : Validate if the string is a valid email.
 * @uses $arguments['html_escape'] (bool) : Converts special characters to HTML reality
 *
 */
class MbStringValidation extends MbValidationBase
{
    /**
     * Implement validation
     *
     * @param mixed $value
     *
     * @param array $arguments
     *
     * @throws \Exception
     *
     * @return string $value
     */
    public function validate($value, array $arguments = [])
    {
        $isString = is_string($value);
        $min = isset($arguments['min']) ? $arguments['min'] : false;
        $max = isset($arguments['max']) ? $arguments['max'] : false;
        $trim = isset($arguments['trim']) ? (bool)$arguments['trim'] : false;
        $striptags = isset($arguments['striptags']) ? $arguments['striptags'] : false;
        $regex = isset($arguments['regex']) ? $arguments['regex'] : false;;
        $mask = isset($arguments['mask']) ? $arguments['mask'] : false;
        $strLower = isset($arguments['str_lower']) ? (bool) $arguments['str_lower'] : false;
        $strUpper = isset($arguments['str_upper']) ? (bool) $arguments['str_upper'] : false;
        $alphaNumeric = isset($arguments['alpha_numeric']) ? (bool) $arguments['alpha_numeric'] : false;
        $email = isset($arguments['email']) ? (bool) $arguments['email'] : false;
        $htmlEscape = isset($arguments['html_escape']) ? (bool) $arguments['html_escape'] : false;

        if (!$isString) {
            throw new Exception("O atributo '{$this->getAttribute()}' não é um string!");
        }

        if ($trim) {
            $value = trim($value);
            $value = preg_replace('/\s+/', ' ', $value);
        }

        if($striptags){
            if (is_bool($striptags) && $striptags) {
                $value = strip_tags($value);
            } elseif (is_string($striptags)) {
                $value = strip_tags($value, $striptags);
            } elseif (is_array($striptags)) {
                $value = strip_tags($value, implode("", $striptags));
            }
        }

        if($strLower){
            $value = strtolower($value);
        }

        if($strUpper){
            $value = strtoupper($value);
        }

        if($htmlEscape){
            $value = htmlspecialchars($value);
        }

        if ($alphaNumeric){
            $value = preg_replace("/[^a-zA-Z0-9]+/", "", $value);
        }

        if ($alphaNumeric){
            $value = preg_replace("/[^a-zA-Z0-9]+/", "", $value);
        }

        if ($email && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("O atributo '{$this->getAttribute()}' não é um e-mail válido!");
        }

        $qntCaracteres = strlen($value);

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
                    "O atributo '{$this->getAttribute()}' deve ter no minimo '{$min}' caractere(s)!"
                );
            } elseif ($max && $qntCaracteres > $max) {
                throw new Exception(
                    "O atributo '{$this->getAttribute()}' deve ter no maximo '{$max}' caracteres!"
                );
            }

        }

        if ($regex && is_string($regex) && !preg_match($regex, $value)) {
            throw new Exception(
                "O atributo '{$this->getAttribute()}' não atende a validação regex!"
            );
        }

        if($mask && is_string($mask)){
            $valorNovo = '';
            $k = 0;
            for($i = 0; $i<=strlen($mask)-1; $i++)
            {
                if($mask[$i] == '#')
                {
                    if(isset($value[$k])){
                        $valorNovo .= $value[$k++];
                    }
                }
                else
                {
                    if(isset($mask[$i])){
                        $valorNovo .= $mask[$i];
                    }
                }
            }
            $value = $valorNovo;

        }

        return $value;
    }
}