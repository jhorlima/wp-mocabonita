<?php
namespace MocaBonita\tools\eloquent;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\QueryException;

/**
 * Class Database
 * @package MocaBonita\tools\Eloquent
 */
class MbDatabaseManager implements ConnectionInterface
{

    /**
     * DB Wordpress
     *
     * @var \wpdb
     */
    public $wpdb;

    /**
     *
     * @var Processor
     */
    protected $processor;

    /**
     *
     * @var Grammar
     */
    public $grammar;

    /**
     * Count of active transactions
     *
     * @var int
     */
    public $transactionCount = 0;

    /**
     * Initializes the Database class
     *
     * @return MbDatabaseManager
     */
    public static function instance()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->grammar = new Grammar();
        $this->processor = new Processor();
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
     * @param  array $bindings
     * @throws QueryException
     *
     * @return mixed
     */
    public function selectOne($query, $bindings = array())
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
     * @param  array $bindings
     * @throws QueryException
     *
     * @return array
     */
    public function select($query, $bindings = array())
    {
        $query = $this->bind_params($query, $bindings);

        $result = $this->wpdb->get_results($query);

        if ($result === false || $this->wpdb->last_error) {
            throw new QueryException($query, $bindings, new \Exception($this->wpdb->last_error));
        }

        return $result;
    }

    /** @noinspection PhpUnusedParameterInspection */
    /** @noinspection PhpUnusedParameterInspection */
    /**
     * A hacky way to emulate bind parameters into SQL query
     *
     * @param $query
     * @param $bindings
     *
     * @param bool $update
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

        $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
        $query = vsprintf($query, $bindings);

        return $query;
    }

    /**
     * Bind and run the query
     *
     * @param  string $query
     * @param  array $bindings
     * @throws QueryException
     *
     * @return array
     */
    public function bind_and_run($query, $bindings = array())
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
     * @param  array $bindings
     *
     * @return bool
     */
    public function insert($query, $bindings = array())
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     *
     * @param  string $query
     * @param  array $bindings
     *
     * @return int
     */
    public function update($query, $bindings = array())
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param  string $query
     * @param  array $bindings
     *
     * @return int
     */
    public function delete($query, $bindings = array())
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string $query
     * @param  array $bindings
     *
     * @return bool
     */
    public function statement($query, $bindings = array())
    {
        $new_query = $this->bind_params($query, $bindings, true);

        return $this->unprepared($new_query);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string $query
     * @param  array $bindings
     *
     * @return int
     */
    public function affectingStatement($query, $bindings = array())
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

        return ($result === false || $this->wpdb->last_error);
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
                $bindings[$key] = $value->format($this->grammar->getDateFormat());
            }
        }

        return $bindings;
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure $callback
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function transaction(\Closure $callback)
    {
        $this->beginTransaction();
        try {
            $data = $callback();
            $this->commit();
            return $data;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $transaction = $this->unprepared("START TRANSACTION;");
        if ($transaction) {
            $this->transactionCount++;
        }
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
        $transaction = $this->unprepared("COMMIT;");
        if ($transaction) {
            $this->transactionCount--;
        }
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
        $transaction = $this->unprepared("ROLLBACK;");
        if ($transaction) {
            $this->transactionCount--;
        }
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
     * @return array
     */
    public function pretend(\Closure $callback)
    {
        // TODO: Implement pretend() method.
    }

    /**
     * @return Processor
     */
    public function getPostProcessor()
    {
        return $this->processor;
    }

    /**
     * @return Grammar
     */
    public function getQueryGrammar()
    {
        return $this->grammar;
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
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->wpdb->insert_id;
    }

    /**
     * @return \wpdb
     */
    public function getWpdb()
    {
        return $this->wpdb;
    }

    /**
     * @param \wpdb $wpdb
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
     */
    public function getSchemaBuilder()
    {
        throw new \Exception("Você não pode utilizar o wpdb para criar tabelas!");
    }
}