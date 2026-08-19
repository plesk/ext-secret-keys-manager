<?php
// Copyright 1999-2026. WebPros International GmbH.

namespace tests\unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PleskX\Api\InternalClient;
use PleskX\Api\Operator\SecretKey;
use PleskX\Api\Struct\SecretKey\Info;
use PleskX\Api\XmlResponse;

class ManagerTest extends TestCase
{
    protected function setUp(): void
    {
        \pm_Client::reset();
    }

    public function testGetAllSecretKeysMapsKeyFields(): void
    {
        \pm_Client::$clients[] = new \pm_Client(7, 'john', 'John Doe', 'client');
        \pm_Client::$clients[] = new \pm_Client(8, 'jane', 'Jane Roe', 'reseller');
        $manager = $this->createManagerReturningKeys([
            $this->createSecretKeyInfo('key-1', '192.0.2.1', 'Mobile access', 'john'),
            $this->createSecretKeyInfo('key-2', '192.0.2.2', 'Backup script', 'jane'),
        ]);

        $this->assertSame([
            'key-1' => [
                'key' => 'key-1',
                'ip_address' => '192.0.2.1',
                'description' => 'Mobile access',
                'owner' => '<a href="/admin/customer/domains/id/7">John Doe</a>',
                'owner_type' => 'client',
            ],
            'key-2' => [
                'key' => 'key-2',
                'ip_address' => '192.0.2.2',
                'description' => 'Backup script',
                'owner' => '<a href="/admin/reseller/domains/id/8">Jane Roe</a>',
                'owner_type' => 'reseller',
            ],
        ], $manager->getAllSecretKeys());
    }

    #[DataProvider('getOwnerRenderingDataProvider')]
    public function testGetAllSecretKeysRendersOwner(string $type, string $expectedOwner): void
    {
        \pm_Client::$clients[] = new \pm_Client(7, 'john', 'John & Co', $type);
        $manager = $this->createManagerReturningKeys([
            $this->createSecretKeyInfo('key-1', '192.0.2.1', 'CI access', 'john'),
        ]);

        $data = $manager->getAllSecretKeys();

        $this->assertSame($expectedOwner, $data['key-1']['owner']);
    }

    public static function getOwnerRenderingDataProvider(): iterable
    {
        yield 'customer becomes escaped link to customer domains' => [
            'client', '<a href="/admin/customer/domains/id/7">John &amp; Co</a>',
        ];
        yield 'reseller becomes escaped link to reseller domains' => [
            'reseller', '<a href="/admin/reseller/domains/id/7">John &amp; Co</a>',
        ];
        yield 'admin stays escaped plain text' => [
            'admin', 'John &amp; Co',
        ];
    }

    public function testCreateSecretKeyBuildsFullRequestAndReturnsKey(): void
    {
        $manager = new \Modules_SecretKeysManager_Manager(
            $this->createApiClientExpectingRequest('00112233-4455-6677-8899-aabbccddeeff', $sentPacket),
        );

        $key = $manager->createSecretKey('192.0.2.1', 'CI access', 'john');

        $this->assertEquals('00112233-4455-6677-8899-aabbccddeeff', $key);
        $create = $sentPacket->secret_key->create;
        $this->assertEquals('192.0.2.1', $create->ip_address);
        $this->assertEquals('CI access', $create->description);
        $this->assertEquals('john', $create->login);
    }

    public function testRemoveSecretKeyReportsStatusPerKey(): void
    {
        $operator = $this->createStub(SecretKey::class);
        $operator->method('delete')->willReturnMap([
            ['key-ok', true],
            ['key-fail', false],
        ]);
        $manager = new \Modules_SecretKeysManager_Manager($this->createApiClient($operator));

        $this->assertSame([
            ['status' => 'ok', 'key' => 'key-ok'],
            ['status' => 'fail', 'key' => 'key-fail'],
        ], $manager->removeSecretKey(['key-ok', 'key-fail']));
    }

    private function createManagerReturningKeys(array $keys): \Modules_SecretKeysManager_Manager
    {
        $operator = $this->createStub(SecretKey::class);
        $operator->method('getAll')->willReturn($keys);

        return new \Modules_SecretKeysManager_Manager($this->createApiClient($operator));
    }

    private function createApiClient(SecretKey $operator): InternalClient
    {
        $apiClient = $this->createStub(InternalClient::class);
        $apiClient->method('secretKey')->willReturn($operator);

        return $apiClient;
    }

    private function createApiClientExpectingRequest(string $keyToReturn, ?\SimpleXMLElement &$sentPacket): InternalClient
    {
        $apiClient = $this->createMock(InternalClient::class);
        $apiClient->method('getPacket')->willReturn(new \SimpleXMLElement('<packet/>'));
        $apiClient->expects($this->once())->method('request')
            ->willReturnCallback(function ($packet) use ($keyToReturn, &$sentPacket) {
                $sentPacket = $packet;

                return new XmlResponse("<result><key>{$keyToReturn}</key></result>");
            });

        return $apiClient;
    }

    private function createSecretKeyInfo(string $key, string $ipAddress, string $description, string $login): Info
    {
        return new Info(new \SimpleXMLElement(
            "<key_info><key>{$key}</key><ip_address>{$ipAddress}</ip_address>"
            . "<description>{$description}</description><login>{$login}</login></key_info>",
        ));
    }
}
