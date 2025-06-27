<?php

use Phinx\Migration\AbstractMigration;

class CreateCpPrAttrNameDescriptions extends AbstractMigration
{     
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table(
            "{$pr}cp_pr_attr_name_descriptions",
            array('id' => false, 'engine' => 'InnoDB')
        );

        if ($table->exists()) {
            return;
        }

        $table
            ->addColumn('name_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
            ->addColumn('lang_code', 'string', array('limit' => 2, 'null' => false))
            ->addIndex(array('name_id', 'lang_code'), array('unique' => true,'name' => 'id_lang'))
            ->create();
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_attr_name_descriptions");

        if ($table->exists()) {
            $table->drop();
        }
    }
}
