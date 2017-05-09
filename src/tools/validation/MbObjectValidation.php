<?php

namespace MocaBonita\tools\validation;

use Exception;
use Illuminate\Support\Arr;


/**
 * Object validation class
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
 */
class MbObjectValidation extends MbValidationBase
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
     * @return object $value
     */
    public function validate($value, array $arguments = [])
    {
        $isObject = is_object($value);
        $instanceOf = Arr::get($arguments, 'instanceof', false);

        if (!$isObject) {
            throw new Exception("O atributo '{$this->getAttribute()}' não é um objeto!");
        }

        if ($instanceOf) {
            if (is_string($instanceOf)) {
                //No needed
            } elseif (is_object($instanceOf)) {
                $instanceOf = get_class($instanceOf);
            } else {
                throw new Exception("O InstanceOf do atributo '{$this->getAttribute()}' não é válido!");
            }

            if (!$value instanceof $instanceOf) {
                throw new Exception("O atributo '{$this->getAttribute()}' não é uma instância de '{$instanceOf}'!");
            }
        }

        return $value;
    }
}