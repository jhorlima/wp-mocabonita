<?php

namespace MocaBonita\model;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class MocabonitaSessao
 *
 *
 * @package MocaBonita\model
 */
class MbSessionModel extends Eloquent
{
    /**
     * @var string
     */
    protected $primaryKey = "sess_id";

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

}