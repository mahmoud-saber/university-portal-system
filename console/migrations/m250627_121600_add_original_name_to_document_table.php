<?php

use yii\db\Migration;

class m250627_121600_add_original_name_to_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->addColumn('document', 'original_name', $this->string(255)->after('file_path'));
    }

    public function down()
    {
        $this->dropColumn('document', 'original_name');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250627_121600_add_original_name_to_document_table cannot be reverted.\n";

        return false;
    }
    */
}