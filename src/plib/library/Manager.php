<?php
// Copyright 1999-2023. Plesk International GmbH.

class Modules_SecretKeysManager_Manager
{
    /**
     * Retrieve and return all secret keys
     *
     * @return array Secret keys
     */
    public function getAllSecretKeys()
    {
        $client = new \PleskX\Api\InternalClient();
        $keys = $client->secretKey()->getAll();

        $data = [];
        foreach ($keys as $key) {
            $data[$key->key] = [
                'key' => $key->key,
                'ip_address' => $key->ipAddress,
                'description' => $key->description,
            ];
        }

        return $data;
    }

    /**
     * Remove secret keys
     *
     * @param array $keys
     * @return array of remove statuses
     * @throws pm_Exception
     */
    public function removeSecretKey($keys)
    {
        if (!is_array($keys) || empty($keys)) {
            throw new pm_Exception(pm_Locale::lmsg('errorMessageMissingKeys'));
        }

        $client = new \PleskX\Api\InternalClient();
        $statuses = [];

        foreach ($keys as $keyId) {
            $result = $client->secretKey()->delete($keyId);
            $statuses[] = [
                'status' => $result ? 'ok' : 'fail',
                'key' => $keyId,
            ];
        }

        return $statuses;
    }

    /**
     * Create secret key
     *
     * @param string $ipAddress
     * @param string $description
     * @return string
     */
    public function createSecretKey($ipAddress, $description)
    {
        if ('' === $ipAddress) {
            $ipAddress = null;
        }

        $client = new \PleskX\Api\InternalClient();
        return $client->secretKey()->create($ipAddress, $description);
    }

}
