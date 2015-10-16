<?php

namespace IctCollege\Stagemarkt\Model\Endpoint;

use Cake\Datasource\ConnectionManager;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Schema;
use IctCollege\Stagemarkt\Model\SearchableTrait;

class CompaniesEndpoint extends Endpoint
{

    use SearchableTrait;

    public $filterArgs = array(
        'name' => array(
            'type' => 'like'
        ),
    );

    public function initialize(array $config)
    {
        parent::initialize($config);

        $schema = new Schema(null, [
            'id' => [
                'type' => 'string'
            ],
            'name' => [
                'type' => 'string'
            ]
        ]);
        $schema->addConstraint('primary', [
            'type' => Schema::CONSTRAINT_PRIMARY,
            'columns' => 'id'
        ]);
        $this->schema($schema);
        $this->webservice('search');
    }

    /**
     * {@inheritDoc}
     */
    public function find($type = 'all', $options = [])
    {
        $query = parent::find($type, $options);

        $query->where([
            'type' => 'company'
        ]);

        return $query;
    }
}
