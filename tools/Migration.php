<?php

namespace MocaBonita\tools;

use Phinx\Migration\AbstractMigration;

/**
 * Class Migration
 * @package MocaBonita\tools
 */
class Migration extends AbstractMigration
{
    /**
     *Iniciar a aplicação
     *
     */
    public function init()
    {
        parent::init();
        $output = MigrationOutput::getInstance();
        $this->setOutput($output);
        $this->setAdapter(MysqlAdapter::getInstance($output));
    }
}