<?php

use Phinx\Migration\AbstractMigration;

class CreateCpPrAttrNames extends AbstractMigration
{     
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table(
            "{$pr}cp_pr_attr_names",
            array('id' => false, 'primary_key' => 'name_id', 'engine' => 'InnoDB')
        );

        if ($table->exists()) {
            return;
        }

        $table
            ->addColumn('name_id', 'integer', array('signed' => false, 'null' => false, 'identity' => true))
            ->addColumn('type', 'string', array('limit' => 2, 'null' => false, 'default' => 'L'))
            ->addColumn('cp_attr_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addIndex(array('type'), array('name' => 'type'))
            ->addIndex(array('cp_attr_id'), array('name' => 'cp_attr_id'))
            ->create();
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_attr_names");

        if ($table->exists()) {
            $table->drop();
        }
    }
}
