<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\QueryException;
use MocaBonita\tools\MbSingleton;

/**
 * Main class of the MocaBonita DatabaseManager
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools\eloquent
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbDatabaseManager extends MbSingleton implements ConnectionInterface
{
    /**
     * Wordpress DB Manager
     *
     * @var \wpdb
     */
    public $wpdb;

    /**
     * The query post processor implementation.
     *
     * @var Processor
     */
    protected $postProcessor;

    /**
     * The query grammar implementation.
     *
     * @var Grammar
     */
    public $queryGrammar;

    /**
     * Count of active transactions
     *
     * @var int
     */
    public $transactionCount = 0;

    /**
     * Method to be started
     *
     * @return void
     */
    protected function init()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->queryGrammar = new Grammar();
        $this->postProcessor = new Processor();
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param  string $table
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table)
    {
        $processor = $this->getPostProcessor();

        $query = new MbDatabaseQueryBuilder($this, $this->getQueryGrammar(), $processor);

        return $query->from($table);
    }

    /**
     * Get a new raw query expression.
     *
     * @param  mixed $value
     *
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * Run a select statement and return a single result.
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @throws QueryException
     *
     * @return mixed
     */
    public function selectOne($query, $bindings = [])
    {
        $query = $this->bind_params($query, $bindings);

        $result = $this->wpdb->get_row($query);

        if ($result === false || $this->wpdb->last_error) {
            throw new QueryException($query, $bindings, new \Exception($this->wpdb->last_error));
        }

        return $result;
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @throws QueryException
     *
     * @return array
     */
    public function select($query, $bindings = [])
    {
        $query = $this->bind_params($query, $bindings);

        $result = $this->wpdb->get_results($query);

        if ($result === false || $this->wpdb->last_error) {
            throw new QueryException($query, $bindings, new \Exception($this->wpdb->last_error));
        }

        return $result;
    }

    /**
     * A hacky way to emulate bind parameters into SQL query
     *
     * @param      $query
     * @param      $bindings
     * @param bool $update
     *
     * @return mixed
     */
    private function bind_params($query, $bindings, $update = false)
    {
        $query = str_replace('"', '`', $query);
        $bindings = $this->prepareBindings($bindings);

        if (!$bindings) {
            return $query;
        }

        $bindings = array_map(function ($replace) {
            if (is_string($replace)) {
                $replace = "'" . esc_sql($replace) . "'";
            } elseif ($replace === null) {
                $replace = "null";
            }

            return $replace;
        }, $bindings);

        $query = str_replace(['%', '?'], ['%%', '%s'], $query);
        $query = vsprintf($query, $bindings);

        return $query;
    }

    /**
     * Bind and run the query
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @throws QueryException
     *
     * @return array
     */
    public function bind_and_run($query, $bindings = [])
    {
        $new_query = $this->bind_params($query, $bindings);

        $result = $this->wpdb->query($new_query);

        if ($result === false || $this->wpdb->last_error) {
            throw new QueryException($new_query, $bindings, new \Exception($this->wpdb->last_error));
        }

        return (array)$result;
    }

    /**
     * Run an insert statement against the database.
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return int
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        $new_query = $this->bind_params($query, $bindings, true);

        return $this->unprepared($new_query);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        $new_query = $this->bind_params($query, $bindings, true);

        $result = $this->wpdb->query($new_query);

        if ($result === false || $this->wpdb->last_error) {
            throw new QueryException($new_query, $bindings, new \Exception($this->wpdb->last_error));
        }

        return intval($result);
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param  string $query
     *
     * @throws QueryException
     *
     * @return bool
     */
    public function unprepared($query)
    {
        $result = $this->wpdb->query($query);

        if ($result === false || $this->wpdb->last_error) {
            throw new QueryException($query, [], new \Exception($this->wpdb->last_error));
        }

        return $result;
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param  array $bindings
     *
     * @return array
     */
    public function prepareBindings(array $bindings)
    {

        foreach ($bindings as $key => $value) {

            // Micro-optimization: check for scalar values before instances
            if (is_bool($value)) {
                $bindings[$key] = intval($value);
            } elseif (is_scalar($value)) {
                continue;
            } elseif ($value instanceof \DateTime) {
                // We need to transform all instances of the DateTime class into an actual
                // date string. Each query grammar maintains its own date string format
                // so we'll just ask the grammar for the format to get from the date.
                $bindings[$key] = $value->format($this->queryGrammar->getDateFormat());
            }
        }

        return $bindings;
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure $callback
     *
     * @param int       $attempts
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function transaction(\Closure $callback, $attempts = 1)
    {
        do {
            $this->beginTransaction();

            try {
                $data = $callback();
                $this->commit();
                break;
            } catch (\Exception $e) {
                $this->rollBack();
                $data = $e;
            }
        } while (--$attempts > 0);

        if($data instanceof \Exception){
            throw $data;
        }

        return $data;
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->unprepared("START TRANSACTION;");
        $this->transactionCount++;
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if ($this->transactionCount < 1) {
            return;
        }
        $this->unprepared("COMMIT;");
        $this->transactionCount--;
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        if ($this->transactionCount < 1) {
            return;
        }
        $this->unprepared("ROLLBACK;");
        $this->transactionCount--;
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactionCount;
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param  \Closure $callback
     *
     * @return array|void
     */
    public function pretend(\Closure $callback)
    {
        // TODO: Implement pretend() method.
    }

    /**
     * Get post processor
     *
     * @return Processor
     */
    public function getPostProcessor()
    {
        return $this->postProcessor;
    }

    /**
     * Get query grammar
     *
     * @return Grammar
     */
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    /**
     * Return self as PDO
     *
     * @return MbDatabaseManager
     */
    public function getPdo()
    {
        return $this;
    }

    /**
     * Return the last insert id
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->wpdb->insert_id;
    }

    /**
     * Get Wpdb
     *
     * @return \wpdb
     */
    public function getWpdb()
    {
        return $this->wpdb;
    }

    /**
     * Set Wpdb
     *
     * @param \wpdb $wpdb
     *
     * @return MbDatabaseManager
     */
    public function setWpdb(\wpdb $wpdb)
    {
        $this->wpdb = $wpdb;

        return $this;
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @throws \Exception
     */
    public function getSchemaBuilder()
    {
        throw new \Exception("You can not use wpdb to create tables!");
    }

    /**
     * WpDb Name
     *
     * @return string
     */
    public function getName()
    {
        return "wpdb";
    }
}