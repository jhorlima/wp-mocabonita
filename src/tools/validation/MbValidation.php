<?php

namespace MocaBonita\tools\validation;

use \Exception;
use Illuminate\Contracts\Support\Arrayable;
use MocaBonita\tools\MbException;

/**
 * Main class of the MocaBonita Validation
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
class MbValidation implements Arrayable
{
    /**
     * Stored error in validation
     *
     * @var bool
     */
    protected $error = false;

    /**
     * Is error
     *
     * @return boolean
     */
    public function isError()
    {
        return (bool)$this->error;
    }

    /**
     * Set error
     *
     * @param boolean $error
     *
     * @return MbValidation
     */
    private function setErro($error = true)
    {
        $this->error = (bool)$error;

        return $this;
    }

    /**
     * Stored data for validation
     *
     * @var mixed[]
     */
    protected $data;

    /**
     * Stored attributes that can be null
     *
     * @var string[]
     */
    protected $nullable;

    /**
     * Stored Validations
     *
     * @var array[]
     */
    protected $validations;

    /**
     * Stored Remove unused
     *
     * @var bool
     */
    protected $removeUnused = false;

    /**
     * Stored validation error messages
     *
     * @var array[]
     */
    protected $errorMessages = [];

    /**
     * Get data
     *
     * @param null $attribute
     *
     * @return \array[]|mixed|null
     */
    public function getData($attribute = null)
    {
        if (is_null($attribute)) {
            return $this->data;
        } elseif (isset($this->data[$attribute])) {
            return $this->data[$attribute];
        } else {
            return null;
        }
    }

    /**
     * Set data
     *
     * @param \array[] $data
     *
     * @return MbValidation
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get nullable
     *
     * @return \string[]
     */
    public function getNullable()
    {
        return !is_null($this->nullable) ? $this->nullable : [];
    }

    /**
     * Set nullable
     *
     * @param \string[] $nullable
     *
     * @return MbValidation
     */
    public function setNullable(array $nullable)
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * Is remove unused
     *
     * @return boolean
     */
    public function isRemoveUnused()
    {
        return $this->removeUnused;
    }

    /**
     * Set remove unused
     *
     * @param boolean $removeUnused
     *
     * @return MbValidation
     */
    public function setRemoveUnused($removeUnused = true)
    {
        $this->removeUnused = (bool)$removeUnused;

        return $this;
    }

    /**
     * Get an instance of MbValidation
     *
     * @param array $data
     *
     * @return MbValidation
     */
    public static function validate(array $data)
    {
        $validation = new self();

        return $validation->setData($data);
    }

    /**
     * Get Validations
     *
     * @param string $attribute
     *
     * @return array[]
     */
    public function getValidations($attribute = null)
    {
        if (is_null($attribute)) {
            return $this->validations;
        } elseif (isset($this->validations[$attribute])) {
            return $this->validations[$attribute];
        } else {
            return [];
        }
    }

    /**
     * Set Validations
     *
     * @param string           $attribute
     *
     * @param MbValidationBase $mbValidationBase
     *
     * @param array            $arguments
     *
     * @return $this
     */
    public function setValidations($attribute, MbValidationBase $mbValidationBase, array $arguments = [])
    {

        if (!isset($this->validations[$attribute])) {
            $this->validations[$attribute] = [];
        }

        $this->validations[$attribute][] = [
            'attribute'  => $attribute,
            'validation' => $mbValidationBase,
            'arguments'  => $arguments,
        ];

        return $this;
    }

    /**
     * Get error messages
     *
     * @param string|null $attribute
     *
     * @return array|\array[]
     */
    public function getErrorMessages($attribute = null)
    {
        return is_null($attribute) ? $this->errorMessages : $this->errorMessages[$attribute];
    }

    /**
     * Set error messages
     *
     * @param array $errorMessages
     */
    protected function setErrorMessages(array $errorMessages)
    {
        $this->errorMessages = $errorMessages;
    }

    /**
     * Set error message
     *
     * @param string $attribute
     *
     * @param string $errorMessage
     */
    protected function setErrorMessage($attribute, $errorMessage)
    {
        if (!isset($this->errorMessages[$attribute])) {
            $this->errorMessages[$attribute] = [];
        }

        $this->errorMessages[$attribute][] = $errorMessage;
    }

    /**
     * Check validations
     *
     * @param bool $exceptionOnError
     *
     * @return bool
     *
     * @throws MbException
     */
    public function check($exceptionOnError = false)
    {
        $attributes = array_keys($this->validations);

        $this->setErrorMessages([]);

        foreach ($attributes as $attribute) {

            $attributeExists = array_key_exists($attribute, $this->data);
            $attributeNull = true;

            if($attributeExists){
                $attributeNull = is_bool($this->data[$attribute]) || is_int($this->data[$attribute]) ? false : is_null($this->data[$attribute]) || empty($this->data[$attribute]);
            }

            $isNullable = in_array($attribute, $this->getNullable());
            $attirbuteRoles = $this->getValidations($attribute);

            if (!$attributeNull && !empty($attirbuteRoles)) {
                foreach ($attirbuteRoles as $role) {
                    try {
                        $role['validation']::getInstance()->setAttribute($attribute);

                        $this->data[$attribute] = $role['validation']::getInstance()
                            ->validate($this->data[$attribute], $role['arguments']);

                    } catch (Exception $e) {
                        $this->setErrorMessage($attribute, $e->getMessage());
                    }
                }
            } elseif ($attributeNull && $isNullable) {
                $this->data[$attribute] = null;
            } else {
                $this->setErrorMessage($attribute, "O atributo '{$attribute}' não pode ser nulo!");
            }
        }

        if ($this->isRemoveUnused()) {
            $sentAttributes = array_keys($this->data);

            foreach ($sentAttributes as $attribute) {
                if (!in_array($attribute, $attributes)) {
                    unset($this->data[$attribute]);
                }
            }
        }

        $this->setErro(!empty($this->errorMessages) ? true : false);

        if ($exceptionOnError && $this->isError()) {
            throw new MbException("Seus dados não passaram na validação!", 400, $this, $this->errorMessages);
        }

        return $this->isError();
    }

    /**
     * Get the instance as an array.
     *
     * @return array[]
     */
    public function toArray()
    {
        return [
            'error'    => $this->isError(),
            'messages' => $this->getErrorMessages(),
            'data'     => $this->getData(),
        ];
    }
}