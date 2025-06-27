<?php

use Phinx\Migration\AbstractMigration;

class CreateCpPrReviewsSeo extends AbstractMigration
{     
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table(
            "{$pr}cp_pr_reviews_seo",
            array('id' => false, 'primary_key' => 'id', 'engine' => 'InnoDB')
        );

        if ($table->exists()) {
            return;
        }

        $table
            ->addColumn('id', 'integer', array('signed' => false, 'null' => false, 'identity' => true))
            ->addColumn('company_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->create();
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_reviews_seo");

        if ($table->exists()) {
            $table->drop();
        }
    }
}
