<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MbModel
 * @package MocaBonita\tools\eloquent
 */
class MbModel extends Model
{

    /**
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
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|MbDatabaseEloquentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new MbDatabaseEloquentBuilder($query);
    }

}