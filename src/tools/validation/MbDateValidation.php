<?php

namespace MocaBonita\tools\validation;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;


/**
 * Date validation class
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
 * @uses $arguments['timezone'] (string) : Timezone default
 * @uses $arguments['input_format'] (string) : Date input format
 * @uses $arguments['output_format'] (string) : Date Return Format
 * @uses $arguments['output_timestamp'] (bool) : Return value in timestamp
 */
class MbDateValidation extends MbValidationBase
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
     * @return \DateTime|integer|string $value
     */
    public function validate($value, array $arguments = [])
    {
        $timezone = Arr::get($arguments, 'timezone', false);
        $inputFormat = Arr::get($arguments, 'input_format', false);
        $outputFormat = Arr::get($arguments, 'output_format', false);
        $outputTimezone = Arr::get($arguments, 'output_timestamp', false);

        if(is_string($inputFormat)){
            $value = is_string($value) ? Carbon::createFromFormat($inputFormat, $value) : false;
        } else {
            try{
                $value = is_string($value) ? Carbon::parse($value) : false;
            } catch (\Exception $e){
                $value = false;
            }
        }

        if (!$value instanceof Carbon) {
            throw new Exception("O atributo '{$this->getAttribute()}' não é uma data válida!");
        }

        if (!$timezone) {
            $timezone = get_option('timezone_string');
            $timezone = !empty($timezone) ? $timezone : 'America/Fortaleza';
        }

        $value->setTimezone($timezone);

        if ($outputFormat) {
            $value = $value->format($outputFormat);

            if (!$value) {
                throw new Exception("O atributo '{$this->getAttribute()}' têm formato de data inválida!");
            }
        }

        if ($outputTimezone) {
            $value = $value->getTimestamp();
        }

        return $value;
    }
}