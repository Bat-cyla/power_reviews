<?php

use Phinx\Migration\AbstractMigration;

class AlterCompanies extends AbstractMigration
{ 
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}companies");
        if (!$table->hasColumn('cp_trust_status')) {
            $table
                ->addColumn('cp_trust_status', 'char', array('limit' => 1, 'null' => false, 'default' => 'Y'))
                ->save();
        }
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}companies");

        if ($table->hasColumn('cp_trust_status')) {
            $table->removeColumn('cp_trust_status');
        }
    }
} 
