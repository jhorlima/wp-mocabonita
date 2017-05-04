<?php

namespace MocaBonita\model;

use MocaBonita\tools\eloquent\MbModel;

/**
 * Main class of the MocaBonita UserMeta
 *
 * @author Jhordan Lima <jhorlima@icloud.com>
 * @category WordPress
 * @package \MocaBonita\model
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 * @version 3.1.0
 */
class MbWpUserMeta extends MbModel
{
    /**
     * Stored table name
     *
     * @var string
     */
    protected $table = 'wp_usermeta';

    /**
     * Stored table primarykey
     *
     * @var string
     */
    protected $primaryKey = 'umeta_id';

    /**
     * Stored if table has timestamps
     *
     * @var bool
     */
    protected $timestamps = false;
}