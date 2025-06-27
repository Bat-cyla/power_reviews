<?php

use Phinx\Migration\AbstractMigration;

class AlterDiscussionMessages extends AbstractMigration
{
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}discussion_messages");

        $table
            ->addColumn('cp_pr_title', 'string', array('limit' => 255, 'null' => false))
            ->addColumn('cp_pr_advantages', 'text', array())
            ->addColumn('cp_pr_disadvantages', 'text', array())
            ->save();
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}discussion_messages");

        if ($table->hasColumn('cp_pr_title')) {
            $table->removeColumn('cp_pr_title');
        }
        if ($table->hasColumn('cp_pr_advantages')) {
            $table->removeColumn('cp_pr_advantages');
        }
        if ($table->hasColumn('cp_pr_disadvantages')) {
            $table->removeColumn('cp_pr_disadvantages');
        }
    }
}
