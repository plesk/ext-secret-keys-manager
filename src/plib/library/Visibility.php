<?php
// Copyright 1999-2026. WebPros International GmbH.

class Modules_SecretKeysManager_Visibility
{
    public const PERMISSIONS_ACCESS = 'access';

    public static function isAccessAllowed(): bool
    {
        $user = \pm_Session::getClient();

        return $user->hasPermission(self::PERMISSIONS_ACCESS);
    }
}
