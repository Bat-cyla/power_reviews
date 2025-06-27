<?php

use Phinx\Migration\AbstractMigration;

class AlterDiscussionPosts extends AbstractMigration
{ 
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}discussion_posts");
        if (!$table->hasColumn('cp_pr_is_pos')) {
            $table
                ->addColumn('cp_pr_is_pos', 'char', array('limit' => 1, 'null' => false, 'default' => 'N'))
                ->save();
        }
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}discussion_posts");

        if ($table->hasColumn('cp_pr_is_pos')) {
            $table->removeColumn('cp_pr_is_pos');
        }
    }
}
