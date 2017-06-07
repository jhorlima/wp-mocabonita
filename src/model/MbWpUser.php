<?php

namespace MocaBonita\model;

use MocaBonita\tools\eloquent\MbModel;

/**
 * Main class of the MocaBonita User
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
class MbWpUser extends MbModel
{
    /**
     * Stored table name
     *
     * @var string
     */
    protected $table = 'wp_users';

    /**
     * Stored table primarykey
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Stored if table has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Date elements
     *
     * @var array
     */
    protected $dates = [
        'user_registered',
    ];

    /**
     * Fillable elements
     *
     * @var array
     */
    protected $fillable = [
        'user_login',
        'user_pass',
        'user_nicename',
        'user_email',
        'user_url',
        'user_registered',
        'user_status',
        'display_name',
    ];

    /**
     * Get user meta
     *
     * @return MbWpUserMeta[]
     */
    public function meta()
    {
        return $this->hasMany(MbWpUserMeta::class, 'user_id');
    }

    /**
     * Get current user logged
     *
     * @return MbWpUser
     */
    public function getCurrentUser()
    {
        return self::findOrFail(get_current_user_id());
    }
}