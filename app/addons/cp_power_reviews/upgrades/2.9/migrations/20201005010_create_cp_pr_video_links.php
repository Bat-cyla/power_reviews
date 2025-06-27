<?php

use Phinx\Migration\AbstractMigration;

class CreateCpPrVideoLinks extends AbstractMigration
{     
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table(
            "{$pr}cp_pr_video_links",
            array('id' => false, 'primary_key' => 'video_id', 'engine' => 'InnoDB')
        );

        if ($table->exists()) {
            return;
        }

        $table
            ->addColumn('video_id', 'integer', array('signed' => false, 'null' => false, 'identity' => true))
            ->addColumn('youtube_id', 'string', array('limit' => 128, 'null' => false))
            ->addColumn('status', 'char', array('limit' => 1, 'null' => false, 'default' => 'A'))
            ->addColumn('post_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addIndex(array('youtube_id'), array('name' => 'youtube_id'))
            ->addIndex(array('post_id'), array('name' => 'post_id'))
            ->create();
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_video_links");

        if ($table->exists()) {
            $table->drop();
        }
    }
}
