<?php

use Phinx\Migration\AbstractMigration;

class AlterCpPrForSeo extends AbstractMigration
{ 
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_for_seo");
        if (!$table->hasColumn('cp_st_custom_bc')) {
            $table
                ->addColumn('cp_st_custom_bc', 'string', array('limit' => 100, 'null' => false, 'default' => ''))
                ->save();
        }
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_for_seo");

        if ($table->hasColumn('cp_st_custom_bc')) {
            $table->removeColumn('cp_st_custom_bc');
        }
    }
}
