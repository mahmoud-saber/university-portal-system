<?php

use yii\db\Migration;

class m250623_095927_add_grade_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%grade}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer()->notNull(),
            'course_id' => $this->integer()->notNull(),
            'grade' => $this->string(10),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),

        ]);

        $this->addForeignKey(
            'fk-grade-student_id',
            '{{%grade}}',
            'student_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-grade-course_id',
            '{{%grade}}',
            'course_id',
            '{{%course}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-grades-student_id', '{{%grade}}');
        $this->dropForeignKey('fk-grades-course_id', '{{%grade}}');
        $this->dropTable('{{%grade}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250623_095927_add_grade_table cannot be reverted.\n";

        return false;
    }
    */
}