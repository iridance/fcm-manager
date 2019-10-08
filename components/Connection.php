<?php

namespace fcm\manager\components;

use Yii;
use yii\base\Component;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class Connection extends Component
{
    protected $_client;

    /**
     * Set fcm client
     *
     * @param [type] $value
     * @return Connection
     */
    public function setClient($value): self
    {
        $this->_client = $value;
        return $this;
    }

    /**
     * Get fcm client
     *
     * @return Kreait\Firebase\Messaging
     */
    public function getClient(): \Kreait\Firebase\Messaging
    {
        if ($this->_client === null) {
            $path = Yii::$app->params['firebase']['config-path'];

            $firebase = (new Factory)
                ->withServiceAccount($path)
                ->create();
    
            $this->_client = $firebase->getMessaging();
        }

        return $this->_client;
    }
    
    /**
     * Subscribe to topic
     *
     * @param string $topic
     * @param string|array $tokens
     * @return boolean
     */
    public function subscribeToTopic(string $topic, $tokens)
    {
        if (is_string($tokens)) {
            $tokens = [$tokens];
        }
        
        if (count($tokens) > 1000) {
            throw new \yii\base\InvalidArgumentException('The number of tokens cannot exceed 1000.');
        }

        return $this->getClient()->subscribeToTopic($topic, $tokens);
    }

    /**
     * Unsubscribe from topic
     *
     * @param string $topic
     * @param string|array $tokens
     * @return boolean
     */
    public function unsubscribeFromTopic(string $topic, $tokens)
    {
        if (is_string($tokens)) {
            $tokens = [$tokens];
        }
        
        if (count($tokens) > 1000) {
            throw new \yii\base\InvalidArgumentException('The number of tokens cannot exceed 1000.');
        }

        return $this->getClient()->unsubscribeFromTopic($topic, $tokens);
    }

    /**
     * Send message to topic
     *
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param string $image
     * @return boolean
     */
    public function sendToTopic(string $topic, string $title, string $body, string $image = null): array
    {
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification(Notification::create($title, $body, $image));

        return $this->getClient()->send($message);
    }

    /**
     * Send message to tokens
     *
     * @param string|array $tokens
     * @param array $notification
     * @return boolean
     */
    public function sendToTokens($tokens, string $title, string $body, string $image = null): array
    {
        if (is_string($tokens)) {
            $tokens = [$tokens];
        }

        if (count($tokens) > 100) {
            throw new \yii\base\InvalidArgumentException('The number of tokens cannot exceed 100.');
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body, $image));

        $sendReport = $this->getClient()->sendMulticast($message, $tokens);

        $items = $sendReport->getItems();

        $result = [];
        foreach ($items as $item) {
            $result[] = $item->result();
        }

        return $result;
    }

    /**
     * Send message to all device
     * 
     * ***Must subscribe device to 'all' topic.***
     *
     * @param string $title
     * @param string $body
     * @param string $image
     * @return boolean
     */
    public function sendToAllDevice(string $title, string $body, string $image = null) 
    {
        $message = CloudMessage::withTarget('topic', 'all')
            ->withNotification(Notification::create($title, $body, $image));

        return $this->getClient()->send($message);
    }

    /**
     * Get device info by token.
     *
     * @param array|string $deviceTokens
     * @return array
     */
    public function getDeviceInfo($deviceTokens): array
    {
        if (is_string($deviceTokens)) {
            $deviceTokens = [$deviceTokens];
        }

        $result = [];

        foreach ($deviceTokens as $token) {
            $instance = $this->getClient()->getAppInstance($token);
            $result[$token] = $instance->rawData();
        }
        return $result;
    }

    /**
     * Device register instance.
     *
     * @var \fcm\manager\models\DeviceRegisterInterface|null
     */
    protected $deviceRegisterClass;

    /**
     * Device register class setter method.
     *
     * @param string $className
     * @return self
     */
    public function setDeviceRegisterClass(string $className): self
    {
        $instance = new $className();
        if ($instance instanceof \fcm\manager\models\DeviceRegisterInterface === false) {
            throw new \yii\base\InvalidConfigException('deviceRegisterClass must be implemented by \fcm\manager\models\DeviceRegisterInterface.');
        }
        $this->deviceRegisterClass = $instance;
        return $this;
    }

    /**
     * Device register class getter method.
     *
     * @return void
     */
    public function getDeviceRegisterClass(): \fcm\manager\models\DeviceRegisterInterface
    {
        if ($this->deviceRegisterClass === null) {
            $this->deviceRegisterClass = new \fcm\manager\models\FcmDeviceRegister();
        }
        return $this->deviceRegisterClass;
    }

}