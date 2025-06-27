<?php

use Phinx\Migration\AbstractMigration;

class AlterDiscussionPosts extends AbstractMigration
{ 
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}discussion_posts");
        if (!$table->hasColumn('cp_product_review_id')) {
            $table
                ->addColumn('cp_product_review_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->save();
        }
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}discussion_posts");

        if ($table->hasColumn('cp_product_review_id')) {
            $table->removeColumn('cp_product_review_id');
        }
    }
}
