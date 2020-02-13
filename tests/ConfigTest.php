<?php

namespace Gemz\HttpClient\Tests;

use Gemz\HttpClient\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @test */
    public function has_json_as_default()
    {
        $config = Config::make();
        $this->assertTrue($config->toArray()['headers']['Content-Type'] == 'application/json');
    }

    /** @test */
    public function config_has_array_with_one_element()
    {
        $config = Config::make();

        $this->assertEquals(1, count($config->toArray()));
    }

    /** @test */
    public function config_can_be_instantiated()
    {
        $config = Config::make();
        $this->assertIsArray($config->toArray());

        $config = new Config();
        $this->assertIsArray($config->toArray());
    }

    /** @test */
    public function config_has_expected_keys_and_values()
    {
        $config = Config::make()
            ->doNotVerifySsl()
            ->timeout(3)
            ->baseUri('https://www.example.com')
            ->allowRedirects(4)
            ->authBasic('test', 'password')
            ->authBearer('token')
            ->toArray();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('timeout', $config);
        $this->assertArrayHasKey('max_redirects', $config);
        $this->assertArrayHasKey('base_uri', $config);
        $this->assertArrayHasKey('verify_peer', $config);
        $this->assertEquals($config['auth_basic'], 'test:password');
        $this->assertArrayHasKey('auth_bearer', $config);
        $this->assertArrayHasKey('headers', $config);
        $this->assertIsArray($config['headers']);

        $this->assertEquals(3, $config['timeout']);
        $this->assertEquals(4, $config['max_redirects']);
        $this->assertEquals('token', $config['auth_bearer']);
        $this->assertEquals('https://www.example.com', $config['base_uri']);
        $this->assertEquals(false, $config['verify_peer']);
        $this->assertEquals('application/json', $config['headers']['Content-Type']);
    }
}
