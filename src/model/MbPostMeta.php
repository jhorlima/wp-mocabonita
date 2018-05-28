<?php

namespace MocaBonita\model;

use MocaBonita\tools\eloquent\MbModel;

/**
 * Main class of the MocaBonita Post Meta
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\model
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbPostMeta extends MbModel
{
    /**
     * Use table base or current blog (MultiSite).
     *
     * @var boolean
     */
    protected $baseTable = false;

    /**
     * Disable timestamps.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'meta_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meta_key',
        'meta_value',
    ];


    /**
     * @return bool
     */
    public function isBaseTable()
    {
        return $this->baseTable;
    }

    /**
     * @param bool $baseTable
     */
    public function setBaseTable($baseTable = true)
    {
        $this->baseTable = $baseTable;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        $prefix = $this->isBaseTable() ? $this->getWpdb()->base_prefix : $this->getWpdb()->prefix ;
        return "{$prefix}postmeta";
    }

    /**
     * Post relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(MbPost::class, 'post_id');
    }
}