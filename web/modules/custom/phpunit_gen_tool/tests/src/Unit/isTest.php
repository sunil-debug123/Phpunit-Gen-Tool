<?php

namespace App\Models\Tests;

use ReflectionClass;
use App\Models\is;
use PHPUnit\Framework\TestCase;

/**
 * Class isTest.
 *
 * @covers \App\Models\is
 */
final class isTest extends TestCase
{
    private ;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classInstance = new is();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->classInstance);
    }


    public function testGreeting(): void
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    public function testGetName(): void
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

    public function testSetName(): void
    {
        $this->markTestIncomplete('This test is incomplete.');
    }

}
