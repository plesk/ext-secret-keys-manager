<?php
// Copyright 1999-2025. Plesk International GmbH.

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

        $clientsCache = [];
        $data = [];
        foreach ($keys as $key) {
            $login = $key->login;
            if (!isset($clientsCache[$login])) {
                $clientsCache[$login] = $this->resolveClient($login);
            }

            $data[$key->key] = [
                'key' => $key->key,
                'ip_address' => $key->ipAddress,
                'description' => $key->description,
                'owner' => $clientsCache[$login]['owner'],
                'owner_type' => $clientsCache[$login]['owner_type'],
            ];
        }

        return $data;
    }

    /**
     * @return array{owner: string, owner_type: string}
     */
    private function resolveClient(string $login): array
    {
        try {
            $client = \pm_Client::getByLogin($login);
            $pname = htmlspecialchars($client->getProperty('pname'), ENT_QUOTES);
            $type = $client->getProperty('type');
            $id = (int) $client->getId();

            if ($client->isClient()) {
                $owner = '<a href="/admin/customer/domains/id/' . $id . '">' . $pname . '</a>';
            } elseif ($client->isReseller()) {
                $owner = '<a href="/admin/reseller/domains/id/' . $id . '">' . $pname . '</a>';
            } else {
                $owner = $pname;
            }

            return [
                'owner' => $owner,
                'owner_type' => $type,
            ];
        } catch (\Exception $e) {
            return [
                'owner' => htmlspecialchars($login, ENT_QUOTES),
                'owner_type' => '',
            ];
        }
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
     * @param string $login
     * @return string
     */
    public function createSecretKey($ipAddress, $description, $login = '')
    {
        $client = new \PleskX\Api\InternalClient();
        $packet = $client->getPacket();
        $createTag = $packet->addChild('secret_key')->addChild('create');
        $createTag->addChild('ip_address', $ipAddress);

        if ('' !== $description) {
            $createTag->addChild('description', $description);
        }
        if ('' !== $login) {
            $createTag->addChild('login', $login);
        }

        $response = $client->request($packet);
        return (string) $response->key;
    }

    /**
     * @return array<string, string>
     */
    public function getAccountOptions(): array
    {
        $options = ['' => \pm_Locale::lmsg('ownerAdmin')];

        foreach (\pm_Client::getAll() as $client) {
            if ($client->isAdmin()) {
                continue;
            }
            $options[$client->getLogin()] = sprintf(
                '%s (%s)',
                $client->getProperty('pname'),
                $client->getProperty('type'),
            );
        }

        return $options;
    }

}
