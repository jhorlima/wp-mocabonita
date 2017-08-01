<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use MocaBonita\tools\MbMigration;
use MocaBonita\tools\validation\MbValidation;
use Illuminate\Support\Arr;

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
 * @version 3.2.1
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
     * @return \Illuminate\Database\Eloquent\Builder|MbD
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
     */
    public final static function createSchemaModel($deleteIfExists = false)
    {
        $model = new static();

        if (!MbMigration::schema()->hasTable($model->getTable())) {
            MbMigration::schema()->create($model->getTable(), function (Blueprint $table) use ($model) {
                $table->engine = 'InnoDB';
                $model->createSchema($table);
            });
        } elseif ($deleteIfExists) {
            self::dropSchemaModel();
            MbMigration::schema()->create($model->getTable(), function (Blueprint $table) use ($model) {
                $table->engine = 'InnoDB';
                $model->createSchema($table);
            });
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

        $model = new static();

        if (MbMigration::schema()->hasTable($model->getTable())) {
            MbMigration::schema()->table($model->getTable(), function (Blueprint $table) use ($model) {
                $table->engine = 'InnoDB';
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
        $model = new static();
        MbMigration::schema()->dropIfExists($model->getTable());
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if(Arr::get($options, 'validation', true)){

            $attributes = $this->getAttributes();

            $validation = count($attributes) ? $this->validation($attributes) : null;

            if ($validation instanceof MbValidation) {
                $validation->check(true);
                $attributes = $validation->getData();
            } elseif (is_array($validation)) {
                $attributes = $validation;
            }

            $this->fill($attributes);
        }

        return parent::save($options);
    }

    /**
     * Implement method to validation model
     *
     * @param array $attributes
     *
     * @return array|MbValidation|null
     */
    protected function validation(array $attributes)
    {
        return null;
    }

    /**
     * Get TableSchema Builder
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public final function getSchema(){
        return MbMigration::schema();
    }

}