<?php

use yii\db\Migration;

class m250623_095832_add_course_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%course}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
            'teacher_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Add foreign key for teacher_id
        $this->addForeignKey(
            'fk-course-teacher_id',
            '{{%course}}',
            'teacher_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */

    public function safeDown()
    {
        $this->dropForeignKey('fk-course-teacher_id', '{{%course}}');
        $this->dropTable('{{%course}}');
    }
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250623_095832_add_course_table cannot be reverted.\n";

        return false;
    }
    */
}