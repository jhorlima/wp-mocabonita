<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Class MbDatabaseBuilder
 * @package MocaBonita\tools\eloquent
 */
class MbDatabaseQueryBuilder extends Builder
{

    /**
     * @var string
     */
    protected static $pagination = "pagination";

    /**
     * @return string
     */
    public static function getPagination()
    {
        return self::$pagination;
    }

    /**
     * @param string $pagination
     */
    public static function setPagination($pagination)
    {
        self::$pagination = $pagination;
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 15, $columns = ['*'], $pageName = null, $page = null)
    {
        $pageName = is_string($pageName)?:self::$pagination;

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = $this->getCountForPagination($columns);

        $results = $total ? $this->forPage($page, $perPage)->get($columns) : [];

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

}