<?php

namespace MocaBonita\model;

use MocaBonita\tools\eloquent\MbModel;

/**
 * Main class of the MocaBonita UserMeta
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\model
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 */
class MbWpUserMeta extends MbModel
{
    /**
     * Stored table primarykey
     *
     * @var string
     */
    protected $primaryKey = 'umeta_id';

    /**
     * Disable timestamps.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->getWpdb()->base_prefix . "usermeta";
    }
}