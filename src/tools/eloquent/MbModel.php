<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Main class of the MocaBonita Model
 *
 * @author Jhordan Lima <jhorlima@icloud.com>
 * @category WordPress
 * @package \MocaBonita\tools\eloquent
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 * @version 3.1.0
 */
class MbModel extends Model
{

    /**
     * New base query builder
     *
     * @return MbDatabaseQueryBuilder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        return new MbDatabaseQueryBuilder($conn, $grammar, $conn->getPostProcessor());
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|MbDatabaseEloquentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new MbDatabaseEloquentBuilder($query);
    }

}