<?php

use Phinx\Migration\AbstractMigration;

class CreateCpPrReviewsReply extends AbstractMigration
{     
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table(
            "{$pr}cp_pr_reviews_reply",
            array('id' => false, 'primary_key' => 'post_id', 'engine' => 'InnoDB')
        );

        if ($table->exists()) {
            return;
        }

        $table
            ->addColumn('post_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addColumn('thread_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addColumn('cp_admin_answ', 'text', array('null' => false))
            ->addColumn('cp_admin_answ_time', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addColumn('cp_admin_id', 'string', array('limit' => 128, 'null' => false, 'default' => ''))
            ->addColumn('reason', 'text', array('null' => false, 'default' => ''))
            ->addColumn('status', 'char', array('limit' => 1, 'null' => false, 'default' => 'A'))
            ->create();
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_reviews_reply");

        if ($table->exists()) {
            $table->drop();
        }
    }
}
