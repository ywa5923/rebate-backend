<?php

namespace Tests\Feature\Boost;

use PHPUnit\Framework\TestCase;

class BrowserLogsCorsTest extends TestCase
{
    /**
     * @test
     */
    public function boost_browser_logs_endpoint_is_cors_enabled_in_configuration(): void
    {
        /** @var array{
         *     paths: array<int, string>,
         *     allowed_methods: array<int, string>,
         *     allowed_origins: array<int, string>,
         *     allowed_headers: array<int, string>,
         *     supports_credentials: bool
         * } $config
         */
        $config = require __DIR__.'/../../../config/cors.php';

        $this->assertContains('_boost/*', $config['paths']);
        $this->assertContains('*', $config['allowed_methods']);
        $this->assertContains('*', $config['allowed_origins']);
        $this->assertContains('*', $config['allowed_headers']);
        $this->assertTrue($config['supports_credentials']);
    }

    /**
     * @test
     */
    public function nginx_routes_boost_browser_logs_requests_to_laravel(): void
    {
        $nginxConfig = file_get_contents(__DIR__.'/../../../docker/nginx/default.conf');

        $this->assertIsString($nginxConfig);
        $this->assertStringContainsString('_boost', $nginxConfig);
        $this->assertStringContainsString(
            'location ~ ^/(api|sanctum|csrf|_boost|_ignition|storage|login|logout|register|password|docs|telescope)',
            $nginxConfig
        );
    }
}
