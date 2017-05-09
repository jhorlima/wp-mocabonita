<?php

namespace MocaBonita\tools\validation;

use Exception;
use Illuminate\Support\Arr;


/**
 * Number validation class
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
 * @uses $arguments['float'] (bool) : Use value as float
 * @uses $arguments['min'] (float) : Lowest value possible
 * @uses $arguments['max'] (float) : Highest possible value (it requires the $arguments['min'])
 */
class MbNumberValidation extends MbValidationBase
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
     * @return integer|float $value
     */
    public function validate($value, array $arguments = [])
    {
        $isNumeric = is_numeric($value);
        $min = Arr::get($arguments, 'min', false);
        $max = Arr::get($arguments, 'max', false);
        $float = Arr::get($arguments, 'float', false);

        if (!$isNumeric) {
            throw new Exception("O atributo '{$this->getAttribute()}' não é um número!");
        }

        if ($float) {
            $value = $value + 0;
            $value = (float)$value;
        } else {
            $value = (int)$value;
        }

        if ($min && is_numeric($min)) {
            $min = $min + 0;
        } else {
            $min = false;
        }

        if ($max && is_numeric($max)) {
            $max = $max + 0;
        } else {
            $max = false;
        }

        if ($min) {

            if ($value < $min) {
                throw new Exception("O atributo '{$this->getAttribute()}' deve ser maior ou igual a '{$min}'!");
            } elseif ($max && $value > $max) {
                throw new Exception("O atributo '{$this->getAttribute()}' deve ser menor ou igual a '{$max}'!");
            }
        }

        return $value;
    }
}