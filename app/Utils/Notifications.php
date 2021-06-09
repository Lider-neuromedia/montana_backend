<?php

namespace App\Utils;

use App\Entities\User;

class Notifications
{
    public function sendNotification(User $user, array $notification, array $data = [])
    {
        if (!$user->device_token) {
            return false;
        }

        $SERVER_API_KEY = env('FIREBASE_SERVER_API_KEY', null);

        $raw = [
            "registration_ids" => [$user->device_token],
            "notification" => $notification,
            "data" => $data,
        ];

        $dataString = json_encode($raw);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
        $decoded_response = json_decode($response);

        return $decoded_response->failure == 0;
    }
}
