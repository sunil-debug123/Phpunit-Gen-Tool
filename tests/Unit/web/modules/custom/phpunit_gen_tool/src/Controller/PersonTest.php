<?php

namespace Tests\Unit\Models;

use App\Models\Person;
use ReflectionClass;
use Tests\TestCase;

/**
 * Class PersonTest.
 *
 * @author John Doe <john.doe@example.com>
 * @version 1.0.0
 *
 * @covers \App\Models\Person
 */
class PersonTest extends TestCase
{
    /**
     * @var Person
     */
    protected $person;

    /**
     * @var string
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->name = '42';
        $this->person = new Person($this->name);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->person);
        unset($this->name);
    }

    public function testGreeting(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Person::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($this->person, $expected);
        $this->assertSame($expected, $this->person->getName());
    }

    public function testSetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Person::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $this->person->setName($expected);
        $this->assertSame($expected, $property->getValue($this->person));
    }
}
