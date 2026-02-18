<?php
// Copyright 1999-2026. WebPros International GmbH.

class Modules_SecretKeysManager_Permissions extends pm_Hook_Permissions
{
    public function getPermissions()
    {
        return [
            Modules_SecretKeysManager_Visibility::PERMISSIONS_ACCESS => [
                'default' => false,
                'place' => static::PLACE_ADMIN,
                'section' => static::SECTION_ADMIN_MODULES,
                'name' => pm_Locale::lmsg('restrictedMode.accessPermission'),
            ]
        ];
    }
}
