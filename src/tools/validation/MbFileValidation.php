<?php

namespace MocaBonita\tools\validation;

use Illuminate\Support\Arr;
use MocaBonita\tools\MbException;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File validation class
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
 *
 * List of possible arguments for this class:
 *
 * @uses      $arguments['mimetype'] (string | string[]) : MimeTypes valids
 * @uses      $arguments['extension'] (string | string[]) : Extensions valids
 * @uses      $arguments['minSize'] (int) : Minimum file size KB
 * @uses      $arguments['maxSize'] (int) : Maximum file size KB
 * @uses      $arguments['arrayFiles'] (bool) : File Array
 * @uses      $arguments['filter'] (string|Closure) : Filter value with function or callback
 */
class MbFileValidation extends MbValidationBase
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
     * @return SplFileInfo|SplFileInfo[] $value
     */
    public function validate($value, array $arguments = [])
    {
        $mimetype = Arr::get($arguments, 'mimetype', false);
        $extension = Arr::get($arguments, 'extension', false);
        $minSize = Arr::get($arguments, 'minSize', false);
        $maxSize = Arr::get($arguments, 'maxSize', false);
        $arrayFiles = Arr::get($arguments, 'arrayFiles', false);
        $filter = Arr::get($arguments, 'filter', false);

        if ($arrayFiles && is_array($value)) {
            $files = [];
            Arr::set($arguments, 'arrayFiles', false);
            foreach ($value as $file) {
                $files[] = $this->validate($file, $arguments);
            }

            return $files;

        } elseif (!$value instanceof SplFileInfo || !$value->isFile() || $value->getPath() == '') {
            throw new MbException("O atributo '{$this->getAttribute()}' deve ser um arquivo válido!");
        }

        if($value instanceof UploadedFile && !$value->isValid()){
            throw new MbException("O atributo '{$this->getAttribute()}' é inválido!");
        }

        $tamanho = $value->getSize() / 1024;

        if (is_string($mimetype)) {
            $mimetype = [$mimetype];
        }

        if (is_array($mimetype) && !in_array($value->getMimeType(), $mimetype)) {
            throw new MbException("O arquivo '{$this->getAttribute()}' tem um formato inválido!");
        }

        if (is_string($extension)) {
            $extension = [$extension];
        }

        if (is_array($extension) && !in_array($value->getExtension(), $extension)) {
            throw new MbException(
                "A extensão {$value->getExtension()} do arquivo '{$this->getAttribute()}' é inválida! Por favor utilizar uma das extensöes (" . implode(", ",
                    $extension) . ")."
            );
        }

        if (is_numeric($minSize) && $minSize > $tamanho) {
            throw new MbException("O arquivo '{$this->getAttribute()}' tem um tamanho menor que {$minSize}KB!");
        }

        if (is_numeric($maxSize) && $maxSize < $tamanho) {
            throw new MbException("O arquivo '{$this->getAttribute()}' tem um tamanho maior que {$maxSize}KB!");
        }

        if ($filter instanceof \Closure) {
            $value = $filter($value);
        } elseif ($filter) {
            $value = call_user_func($filter, $value);
        }

        return $value;
    }
}