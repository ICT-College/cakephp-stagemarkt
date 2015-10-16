<?php

namespace IctCollege\Stagemarkt\Test\TestCase;

use Cake\TestSuite\TestCase;
use IctCollege\Stagemarkt\Stagemarkt;

class StagemarktTest extends TestCase
{

    /**
     * @var \IctCollege\Stagemarkt\Stagemarkt
     */
    public $stagemarkt;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->stagemarkt = new Stagemarkt([
            'license' => '123'
        ]);
    }

    public function testConstruct()
    {
        $statemarkt = new Stagemarkt([]);
    }

    public function testSearchClient()
    {
        $this->assertInstanceOf('IctCollege\Stagemarkt\Soap\Search', $this->stagemarkt->searchClient());
    }

    public function testDetailsClient()
    {
        $this->assertInstanceOf('IctCollege\Stagemarkt\Soap\Details', $this->stagemarkt->detailsClient());
    }
}
