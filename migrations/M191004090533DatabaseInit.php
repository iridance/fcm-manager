<?php

namespace fcm\manager\migrations;

use yii\db\Migration;

/**
 * Class M191004090533DatabaseInit
 */
class M191004090533DatabaseInit extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create notification table ========================================================
        $this->createTable('{{%fcm_notification}}', [
            'id'              => $this->bigPrimaryKey(),
            'title'           => $this->string()->notNull()->comment('Notification Title'),
            'body'            => $this->string()->notNull()->comment('Notification body'),
            'target'          => $this->text()->notNull()->comment('Send target'),
            'delay_time'      => $this->integer()->notNull()->defaultValue(0)->comment('Delay to send (seconds)'),
            'status'          => $this->smallInteger()->defaultValue(0)->comment('Status'),
            'extra_data'      => $this->text()->comment('Extend data'),
            'create_datetime' => $this->timestamp()->notNull()->comment('Created datetime'),
            'update_datetime' => $this->timestamp()->notNull()->comment('Updated datetime'),
        ]);
        $this->addCommentOnTable('{{%fcm_notification}}', 'FCM Notification');

        $this->createTable('{{%fcm_notification_log}}', [
            'id'                  => $this->bigPrimaryKey(),
            'fcm_notification_id' => $this->bigInteger()->notNull()->comment('Relation to fcm_notification'),
            'result'              => $this->text()->notNull()->comment('Firebase response'),
            'status'              => $this->smallInteger()->defaultValue(0)->comment('Send status'),
            'send_datetime'       => $this->timestamp()->notNull()->comment('Send datetime'),
        ]);
        $this->addCommentOnTable('{{%fcm_notification_log}}', 'FCM Notification log');
        // ==================================================================================
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%fcm_notification}}');
        $this->dropTable('{{%fcm_notification_log}}');
    }
}
