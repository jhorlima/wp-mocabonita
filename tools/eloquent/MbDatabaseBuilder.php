<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Class MbDatabaseBuilder
 * @package MocaBonita\tools\eloquent
 */
class MbDatabaseBuilder extends Builder
{

    /**
     * @var string
     */
    protected static $pageName = "paginacao";

    /**
     * @return string
     */
    public static function getPageName()
    {
        return self::$pageName;
    }

    /**
     * @param string $pageName
     */
    public static function setPageName($pageName)
    {
        self::$pageName = $pageName;
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
        $pageName = is_string($pageName)?:self::$pageName;

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = $this->getCountForPagination($columns);

        $results = $total ? $this->forPage($page, $perPage)->get($columns) : [];

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

}