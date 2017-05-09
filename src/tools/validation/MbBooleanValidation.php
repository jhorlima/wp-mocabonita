<?php
namespace MocaBonita\tools\validation;

use Exception;


/**
 * Boolean validation class
 *
 * @author Jhordan Lima <jhorlima@icloud.com>
 * @category WordPress
 * @package \MocaBonita\tools\validation
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 * @version 3.1.0
 */
class MbBooleanValidation extends MbValidationBase
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
        $isBool = is_bool($value);

        if (!$isBool) {
            throw new Exception("O atributo '{$this->getAttribute()}' não é um booleano!");
        }

        return $value;
    }
}