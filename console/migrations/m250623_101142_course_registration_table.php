<?php

use yii\db\Migration;

class m250623_101142_course_registration_table extends Migration
{
    /**
     * {@inheritdoc}
     */
      public function safeUp()
    {
        $this->createTable('{{%course_registration}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer()->notNull(),
            'course_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-course_registration-student_id',
            '{{%course_registration}}',
            'student_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-course_registration-course_id',
            '{{%course_registration}}',
            'course_id',
            '{{%course}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-course_registration-student_id', '{{%course_registration}}');
        $this->dropForeignKey('fk-course_registration-course_id', '{{%course_registration}}');
        $this->dropTable('{{%course_registration}}');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250623_101142_course_registration_table cannot be reverted.\n";

        return false;
    }
    */
}