<?php

namespace IctCollege\Stagemarkt\Model\Endpoint;

use Cake\Datasource\ConnectionManager;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Schema;
use IctCollege\Stagemarkt\Model\SearchableTrait;

class PositionsEndpoint extends Endpoint
{

    use SearchableTrait;

    public $filterArgs = array(
        'company_id' => array(
            'type' => 'value'
        ),
        'company_name' => array(
            'type' => 'like'
        ),
        'company_address_number' => array(
            'type' => 'value'
        ),
        'company_address_street' => array(
            'type' => 'value'
        ),
        'company_address_postcode' => array(
            'type' => 'value'
        ),
        'company_address_city' => array(
            'type' => 'like'
        ),
        'company_address_country' => array(
            'type' => 'value'
        ),
        'study_program_id' => array(
            'type' => 'value'
        ),
        'learning_pathway' => array(
            'type' => 'value'
        ),
        'description' => array(
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
            'type' => 'position'
        ]);

        return $query;
    }
}
