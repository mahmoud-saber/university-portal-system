<?php

use yii\db\Migration;

class m250623_100147_add_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'file_path' => $this->string(255)->notNull(),
            'file_type' => $this->string(50),
            'created_at' =>  $this->integer()->notNull(),
            'updated_at' =>  $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-document-user_id',
            '{{%document}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

        public function safeDown()
        {
            $this->dropForeignKey('fk-document-user_id', '{{%document}}');
            $this->dropTable('{{%document}}');
        }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250623_100147_add_document_table cannot be reverted.\n";

        return false;
    }
    */
}