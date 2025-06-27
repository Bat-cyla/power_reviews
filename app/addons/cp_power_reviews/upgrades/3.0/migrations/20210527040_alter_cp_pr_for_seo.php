<?php

use Phinx\Migration\AbstractMigration;

class AlterCpPrForSeo extends AbstractMigration
{ 
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_for_seo");
        if (!$table->hasColumn('h1')) {
            $table
                ->addColumn('h1', 'string', array('limit' => 255, 'null' => false))
                ->save();
        }
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_for_seo");

        if ($table->hasColumn('h1')) {
            $table->removeColumn('h1');
        }
    }
}
