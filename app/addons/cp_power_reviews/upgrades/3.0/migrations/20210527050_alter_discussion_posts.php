<?php

use Phinx\Migration\AbstractMigration;

class AlterDiscussionPosts extends AbstractMigration
{ 
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}discussion_posts");
        if (!$table->hasColumn('cp_pr_exp')) {
            $table
                ->addColumn('cp_pr_exp', 'char', array('limit' => 1, 'null' => false, 'default' => 'C'))
                ->addColumn('cp_pr_user_delete', 'char', array('limit' => 1, 'null' => false, 'default' => 'N'))
                ->addColumn('cp_pr_can_edit', 'char', array('limit' => 1, 'null' => false, 'default' => 'Y'))
                ->save();
        }
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}discussion_posts");

        if ($table->hasColumn('cp_pr_exp')) {
            $table->removeColumn('cp_pr_exp');
        }
        if ($table->hasColumn('cp_pr_user_delete')) {
            $table->removeColumn('cp_pr_user_delete');
        }
        if ($table->hasColumn('cp_pr_can_edit')) {
            $table->removeColumn('cp_pr_can_edit');
        }
    }
}
