<?php

use Phinx\Migration\AbstractMigration;

class CreateCpPrReviewsStorefronts extends AbstractMigration
{     
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table(
            "{$pr}cp_pr_reviews_storefronts",
            array('id' => false, 'primary_key' => 'post_id', 'engine' => 'InnoDB')
        );

        if ($table->exists()) {
            return;
        }

        $table
            ->addColumn('post_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addColumn('storefront_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
            ->addIndex(array('storefront_id'), array('name' => 'storefront_id'))
            ->create();
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_pr_reviews_storefronts");

        if ($table->exists()) {
            $table->drop();
        }
    }
}
