<?php

use Phinx\Migration\AbstractMigration;

class DropCpPrReaviewsSeoDescr extends AbstractMigration
{     
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_reaviews_seo_descr");

        if ($table->exists()) {
            $table->drop();
        }
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table(
            "{$pr}cp_pr_reaviews_seo_descr",
            array('id' => false, 'engine' => 'InnoDB')
        );

        if ($table->exists()) {
            return;
        }

        $table
            ->addColumn('id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
            ->addColumn('lang_code', 'string', array('limit' => 2, 'null' => false))
            ->addIndex(array('id', 'lang_code'), array('unique' => true,'name' => 'id_lang'))
            ->create();
    }
}
