<?php

namespace Modules\Brokers\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountTypesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
       // parent::setUp();
       // $this->seed(AccountTypeSeeder::class);
    }
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
