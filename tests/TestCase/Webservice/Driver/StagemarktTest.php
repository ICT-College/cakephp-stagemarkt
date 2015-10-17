<?php

namespace IctCollege\Stagemarkt\Test\TestCase\Webservice\Driver;

use Cake\TestSuite\TestCase;
use IctCollege\Stagemarkt\Webservice\Driver\Stagemarkt;

class StagemarktTest extends TestCase
{

    /**
     * @var \IctCollege\Stagemarkt\Webservice\Driver\Stagemarkt
     */
    public $driver;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->driver = new Stagemarkt([
            'name' => 'test',
            'license' => '123'
        ]);
    }

    public function testInitialize()
    {
        $this->driver->initialize();

        $this->assertInstanceOf('IctCollege\Stagemarkt\Stagemarkt', $this->driver->client());
    }

    public function testConfigName()
    {
        $this->assertEquals('test', $this->driver->configName());
    }

    public function testLogger()
    {
        $this->assertNull($this->driver->logger());
    }

    public function testLogQueries()
    {
        $this->assertFalse($this->driver->logQueries());

        $this->driver->logQueries(true);
        $this->assertTrue($this->driver->logQueries());
    }
}
