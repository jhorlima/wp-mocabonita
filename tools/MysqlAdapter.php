<?php

namespace MocaBonita\tools;

use Phinx\Db\Adapter\MysqlAdapter as Base;

/**
 * Class MysqlAdapter
 * @package MocaBonita\tools
 */
class MysqlAdapter extends Base
{

    /**
     * Instancia da classe.
     *
     * @var MysqlAdapter
     */
    protected static $instance;

    /**
     * Obter instancia da aplicação.
     * @param MigrationOutput $output
     *
     * @return MysqlAdapter
     */
    public static function getInstance(MigrationOutput $output = null)
    {
        if (is_null(self::$instance)) {
            global $wpdb;

            self::$instance = new self([
                "default_migration_table" => "moca_bonita",
                "host"         => DB_HOST,
                "name"         => DB_NAME,
                "user"         => DB_USER,
                "pass"         => DB_PASSWORD,
                "charset"      => DB_CHARSET,
                "collation"    => DB_COLLATE ?: $wpdb->collate,
                "table_prefix" => $wpdb->prefix,
            ]);

        }

        if(!is_null($output)){
            self::$instance->setOutput($output);
        }

        return self::$instance;
    }

    /**
     * @return MigrationOutput
     */
    public function getOutput()
    {
        return parent::getOutput();
    }
}