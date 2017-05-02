<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Class MbDatabaseEloquentBuilder
 *
 *
 * @package MocaBonita\tools\eloquent
 */
class MbDatabaseEloquentBuilder extends Builder
{
    /**
     * @param null $perPage
     * @param array $columns
     * @param null $pageName
     * @param null $page
     * @return LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = null, $page = null)
    {
        $pageName = is_string($pageName)?:MbDatabaseQueryBuilder::getPagination();

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?: $this->model->getPerPage();

        $query = $this->toBase();

        $total = $query->getCountForPagination();

        $results = $total ? $this->forPage($page, $perPage)->get($columns) : new Collection();

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}