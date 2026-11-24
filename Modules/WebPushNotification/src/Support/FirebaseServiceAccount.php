<?php

namespace Modules\WebPushNotification\Support;

use InvalidArgumentException;

class FirebaseServiceAccount
{
    public static function fromJson(string $json): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $decoded = base64_decode($json, true);
            $data = $decoded === false ? null : json_decode($decoded, true);
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Provide a valid Firebase service-account JSON file.');
        }

        return self::validate($data);
    }

    public static function validate(array $data): array
    {
        if (($data['type'] ?? null) !== 'service_account'
            || !is_string($data['project_id'] ?? null) || $data['project_id'] === ''
            || !is_string($data['client_email'] ?? null) || !filter_var($data['client_email'], FILTER_VALIDATE_EMAIL)
            || !is_string($data['private_key'] ?? null)) {
            throw new InvalidArgumentException('Firebase credentials must include a service-account type, project ID, client email and private key.');
        }

        $data['private_key'] = str_replace(['\\r\\n', '\\n', "\r\n"], "\n", $data['private_key']);
        $key = @openssl_pkey_get_private($data['private_key']);
        $details = $key === false ? false : openssl_pkey_get_details($key);
        if (!$details || $details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw new InvalidArgumentException('Firebase private key is invalid. Upload the original service-account JSON downloaded from Firebase.');
        }

        return $data;
    }
}
