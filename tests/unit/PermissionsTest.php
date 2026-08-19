<?php
// Copyright 1999-2026. WebPros International GmbH.

namespace tests\unit;

use PHPUnit\Framework\TestCase;

class PermissionsTest extends TestCase
{
    public function testAccessPermissionIsRegistered(): void
    {
        $permissions = (new \Modules_SecretKeysManager_Permissions())->getPermissions();

        $this->assertArrayHasKey(\Modules_SecretKeysManager_Visibility::PERMISSIONS_ACCESS, $permissions);
    }
}
