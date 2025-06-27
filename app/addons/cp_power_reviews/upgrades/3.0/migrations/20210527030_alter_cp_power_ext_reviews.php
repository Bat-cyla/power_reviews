<?php

use Phinx\Migration\AbstractMigration;

class AlterCpPowerExtReviews extends AbstractMigration
{ 
    public function up()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_power_ext_reviews");
        if (!$table->hasColumn('view_type')) {
            $table
                ->addColumn('view_type', 'char', array('limit' => 1, 'null' => false, 'default' => 'D'))
                ->save();
        }
        if (!$table->hasColumn('position')) {
            $table
                ->addColumn('position', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->save();
        }
        if (!$table->hasColumn('required')) {
            $table
                ->addColumn('required', 'char', array('limit' => 1, 'null' => false, 'default' => 'Y'))
                ->save();
        }
    }

    public function down()
    {
        $options = $this->adapter->getOptions();
        $pr = $options['prefix'];

        $table = $this->table("{$pr}cp_power_ext_reviews");

        if ($table->hasColumn('view_type')) {
            $table->removeColumn('view_type');
        }
        if ($table->hasColumn('position')) {
            $table->removeColumn('position');
        }
        if ($table->hasColumn('required')) {
            $table->removeColumn('required');
        }
    }
}
