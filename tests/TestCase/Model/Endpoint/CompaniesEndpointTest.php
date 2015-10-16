<?php

namespace IctCollege\Stagemarkt\Test\TestCase\Model\Endpoint;

use Cake\TestSuite\TestCase;
use IctCollege\Stagemarkt\Model\Endpoint\CompaniesEndpoint;
use IctCollege\Stagemarkt\Webservice\Driver\Stagemarkt;

class CompaniesEndpointTest extends TestCase
{

    /**
     * @var \IctCollege\Stagemarkt\Model\Endpoint\CompaniesEndpoint
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

        $this->endpoint = new CompaniesEndpoint([
            'connection' => $stagemarkt
        ]);
    }

    public function testaaa()
    {
        $query = $this->endpoint->find();

        $conditions = $query->clause('where');

        $this->assertArrayHasKey('type', $conditions);
        $this->assertEquals('company', $conditions['type']);
    }
}
