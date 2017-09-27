<?php

namespace MocaBonita\tools\validation;

use MocaBonita\tools\MbSingleton;

/**
 * Main class of the MocaBonita ValidationBase
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
 */
abstract class MbValidationBase extends MbSingleton
{
    /**
     * Stored current attribute
     *
     * @var string
     */
    protected $attribute;

    /**
     * Get attribute
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set attribute
     *
     * @param string $attribute
     *
     * @return MbValidationBase
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Implement validation
     *
     * @param mixed $value
     *
     * @param array $arguments
     *
     * @throws \Exception
     *
     * @return mixed $value
     */
    public abstract function validate($value, array $arguments = []);

}