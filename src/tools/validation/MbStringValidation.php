<?php

namespace MocaBonita\tools\validation;

use Exception;
use Illuminate\Support\Arr;

/**
 * String validation class
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools\validation
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 * List of possible arguments for this class:
 *
 * @uses      $arguments['instanceof'] (string | object) : Class instance
 *
 * @uses      $arguments['min'] (int): Minimum number of characters
 * @uses      $arguments['max'] (int): Maximum number of characters (it requires the $arguments['min'])
 * @uses      $arguments['trim'] (bool): Trim the string
 * @uses      $arguments['striptags'] (string | string[]): HTML TAGS Filter and Allowed Tags
 * @uses      $arguments['regex'] (string): Validation regex
 * @uses      $arguments['mask'] (string): String mask, ex: (##) ####-####
 * @uses      $arguments['str_lower'] (bool) : Format string to lowercase.
 * @uses      $arguments['str_upper'] (bool) : Format string for uppercase.
 * @uses      $arguments['alpha_numeric'] (bool) : Format alphanumeric in string.
 * @uses      $arguments['email'] (bool) : Validate if the string is a valid email.
 * @uses      $arguments['html_escape'] (bool) : Converts special characters to HTML reality
 * @uses      $arguments['in_array'] (string[]) : Check if the value is in array
 * @uses      $arguments['filter'] (string|Closure) : Filter value with function or callback
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
        $min = Arr::get($arguments, 'min', false);
        $max = Arr::get($arguments, 'max', false);
        $trim = Arr::get($arguments, 'trim', false);
        $striptags = Arr::get($arguments, 'striptags', false);
        $regex = Arr::get($arguments, 'regex', false);;
        $mask = Arr::get($arguments, 'mask', false);
        $strLower = Arr::get($arguments, 'str_lower', false);
        $strUpper = Arr::get($arguments, 'str_upper', false);
        $alphaNumeric = Arr::get($arguments, 'alpha_numeric', false);
        $email = Arr::get($arguments, 'email', false);
        $htmlEscape = Arr::get($arguments, 'html_escape', false);
        $inArray = Arr::get($arguments, 'in_array', false);
        $filter = Arr::get($arguments, 'filter', false);

        if (!$isString) {
            throw new Exception("O atributo '{$this->getAttribute()}' não é um string!");
        }

        if ($trim) {
            $value = trim($value);
            $value = preg_replace('/\s+/', ' ', $value);
        }

        if ($striptags) {
            if (is_bool($striptags) && $striptags) {
                $value = strip_tags($value);
            } elseif (is_string($striptags)) {
                $value = strip_tags($value, $striptags);
            } elseif (is_array($striptags)) {
                $value = strip_tags($value, implode("", $striptags));
            }
        }

        if ($strLower) {
            $value = strtolower($value);
        }

        if ($strUpper) {
            $value = strtoupper($value);
        }

        if ($htmlEscape) {
            $value = htmlspecialchars($value);
        }

        if ($alphaNumeric) {
            $value = preg_replace("/[^a-zA-Z0-9]+/", "", $value);
        }

        if ($email && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("O atributo '{$this->getAttribute()}' não é um e-mail válido!");
        }

        $qntCaracteres = strlen($value);

        if ($min) {
            $min = intval($min);
        } else {
            $min = false;
        }

        if ($max) {
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

        if ($regex && !preg_match($regex, $value)) {
            throw new Exception(
                "O atributo '{$this->getAttribute()}' não atende a validação regex!"
            );
        }

        if ($mask) {
            $valorNovo = '';
            $k = 0;
            for ($i = 0; $i <= strlen($mask) - 1; $i++) {
                if ($mask[$i] == '#') {
                    if (isset($value[$k])) {
                        $valorNovo .= $value[$k++];
                    }
                } else {
                    if (isset($mask[$i])) {
                        $valorNovo .= $mask[$i];
                    }
                }
            }
            $value = $valorNovo;

        }

        if ($inArray && !in_array($value, $inArray)) {
            throw new Exception("O atributo '{$this->getAttribute()}' não pertence ao conjunto de dados!");
        }

        if ($filter && $filter instanceof \Closure) {
            $value = $filter($value);
        } elseif ($filter) {
            $value = call_user_func($filter, $value);
        }

        return $value;
    }
}