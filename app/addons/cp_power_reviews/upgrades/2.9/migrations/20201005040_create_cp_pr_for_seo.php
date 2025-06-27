<?php

use Phinx\Migration\AbstractMigration;

class CreateCpPrForSeo extends AbstractMigration
{     
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table(
            "{$pr}cp_pr_for_seo",
            array('id' => false, 'engine' => 'InnoDB')
        );

        if ($table->exists()) {
            return;
        }

        $table
            ->addColumn('thread_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
            ->addColumn('meta_keywords', 'string', array('limit' => 255, 'null' => false))
            ->addColumn('meta_description', 'string', array('limit' => 255, 'null' => false))
            ->addColumn('page_title', 'string', array('limit' => 255, 'null' => false))
            ->addColumn('lang_code', 'string', array('limit' => 2, 'null' => false))
            ->addIndex(array('thread_id', 'lang_code'), array('unique' => true,'name' => 'thread_id_lang'))
            ->addIndex(array('thread_id'), array('name' => 'thread_id'))
            ->create();
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_for_seo");

        if ($table->exists()) {
            $table->drop();
        }
    }
}
