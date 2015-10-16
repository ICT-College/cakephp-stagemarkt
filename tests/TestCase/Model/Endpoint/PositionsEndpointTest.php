<?php

namespace IctCollege\Stagemarkt\Test\TestCase\Model\Endpoint;

use Cake\TestSuite\TestCase;
use IctCollege\Stagemarkt\Model\Endpoint\PositionsEndpoint;
use IctCollege\Stagemarkt\Webservice\Driver\Stagemarkt;

class PositionsEndpointTest extends TestCase
{

    /**
     * @var \IctCollege\Stagemarkt\Model\Endpoint\PositionsEndpoint
     */
    public $endpoint;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();

        $stagemarkt = new Stagemarkt([
            'license' => '123'
        ]);
        $stagemarkt->initialize();

        $this->endpoint = new PositionsEndpoint([
            'connection' => $stagemarkt
        ]);
    }

    public function testFindAll()
    {
        $query = $this->endpoint->find();

        $conditions = $query->clause('where');

        $this->assertArrayHasKey('type', $conditions);
        $this->assertEquals('position', $conditions['type']);
    }
}
