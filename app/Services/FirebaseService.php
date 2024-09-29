<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        // Initialize Firebase Messaging with the factory and credentials
        $firebase = (new Factory)->withServiceAccount('./croxxtalent-c1019-firebase-adminsdk-6z0kl-208b0eeb01.json');
        $this->messaging = $firebase->createMessaging();
    }

    /**
     * Send Push Notification via Firebase Cloud Messaging (FCM)
     *
     * @param string $title
     * @param string $body
     * @param string $deviceToken
     * @param array $data
     * @return void
     */
    public function sendPushNotification($title, $body, $deviceToken, array $data = [])
    {
        // Create a notification
        $notification = Notification::create($title, $body);

        // Create the message
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification)
            ->withData($data); // Optional data payload

        // Send the message via Firebase Messaging
        $this->messaging->send($message);
    }
}
