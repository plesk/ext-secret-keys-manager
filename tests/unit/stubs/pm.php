<?php
// Copyright 1999-2026. WebPros International GmbH.

class pm_Exception extends Exception
{
}

class pm_Locale
{
    public static function lmsg($key, $params = [])
    {
        return "[{$key}]";
    }
}

class pm_Client
{
    /** @var pm_Client[] */
    public static array $clients = [];

    /** @var string[] */
    public static array $loginLookups = [];

    public function __construct(
        private int $id = 1,
        private string $login = 'admin',
        private string $pname = 'Administrator',
        private string $type = 'admin',
    ) {
    }

    public static function reset(): void
    {
        self::$clients = [];
        self::$loginLookups = [];
    }

    public static function getByLogin($login)
    {
        self::$loginLookups[] = $login;
        foreach (self::$clients as $client) {
            if ($client->login === $login) {
                return $client;
            }
        }
        throw new pm_Exception("No such client: {$login}");
    }

    public static function getAll()
    {
        return self::$clients;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getProperty($name)
    {
        return ['pname' => $this->pname, 'type' => $this->type][$name] ?? null;
    }

    public function isAdmin()
    {
        return 'admin' === $this->type;
    }

    public function isClient()
    {
        return 'client' === $this->type;
    }

    public function isReseller()
    {
        return 'reseller' === $this->type;
    }
}

abstract class pm_Hook_Permissions
{
    public const PLACE_MAIN = 'main';
    public const PLACE_ADDITIONAL = 'additional';
    public const PLACE_ADMIN = 'admin';
    public const SECTION_ADMIN_MODULES = 'admin-modules';

    abstract public function getPermissions();
}
