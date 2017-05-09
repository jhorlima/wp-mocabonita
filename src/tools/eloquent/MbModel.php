<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbMigration;
use MocaBonita\tools\validation\MbValidation;

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

    /**
     *  Implement method to create schemas
     *
     * @use https://laravel.com/docs/5.2/migrations#creating-tables
     *
     * @param Blueprint $table
     *
     * @throws \Exception
     */
    public function createSchema(Blueprint $table)
    {
        throw new \Exception("The createSchema method was not implemented");
    }

    /**
     * Call createSchema when activating, deactivating or uninstalling plugin
     *
     * @param bool $deleteIfExists To recreate scheme
     *
     * @throws \Exception
     */
    public final static function createSchemaModel($deleteIfExists = false)
    {

        $model = new self();

        if (!MbMigration::schema()->hasTable($model->getTable())) {
            MbMigration::schema()->create($model->getTable(), function (Blueprint $table) use ($model) {
                $model->createSchema($table);
            });
        } elseif ($deleteIfExists) {
            self::dropSchemaModel();
            MbMigration::schema()->create($model->getTable(), function (Blueprint $table) use ($model) {
                $model->createSchema($table);
            });
        } else {
            throw new \Exception("Schema {$model->getTable()} already exists");
        }
    }

    /**
     *  Implement method to update schemas
     *
     * @use https://laravel.com/docs/5.2/migrations#creating-tables
     *
     * @param Blueprint $table
     *
     * @throws \Exception
     */
    public function updateSchema(Blueprint $table)
    {
        throw new \Exception("The updateSchema method was not implemented");
    }

    /**
     * Call updateSchema when activating, deactivating or uninstalling plugin
     *
     * @throws \Exception
     */
    public final static function updateSchemaModel()
    {

        $model = new self();

        if (MbMigration::schema()->hasTable($model->getTable())) {
            MbMigration::schema()->table($model->getTable(), function (Blueprint $table) use ($model) {
                $model->updateSchema($table);
            });
        } else {
            throw new \Exception("Schema {$model->getTable()} was not found");
        }
    }

    /**
     * Call delete scheme
     *
     */
    public final static function dropSchemaModel()
    {
        $model = new self();
        MbMigration::schema()->dropIfExists($model->getTable());
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array $attributes
     *
     * @return Model
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     *
     * @throws MbException
     */
    public function fill(array $attributes)
    {
        $validation = $this->validation($attributes);

        if ($validation instanceof MbValidation) {
            $validation->check(true);
            $attributes = $validation->getData();
        } elseif (is_array($validation)) {
            $attributes = $validation;
        }

        return parent::fill($attributes);
    }

    /**
     * Implement method to validation model
     *
     * @param array $attributes
     *
     * @return array|MbValidation
     */
    protected function validation(array $attributes)
    {
        //
    }

}