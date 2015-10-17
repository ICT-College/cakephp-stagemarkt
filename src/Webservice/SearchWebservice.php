<?php

namespace IctCollege\Stagemarkt\Webservice;

use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Webservice\Webservice;

class SearchWebservice extends Webservice
{

    /**
     * {@inheritDoc}
     */
    protected function _executeReadQuery(Query $query, array $options = [])
    {
        /* @var \IctCollege\Stagemarkt\Stagemarkt $client */
        $client = $this->driver()->client();

        if ($query->page()) {
            $options['page'] = $query->page();
        }
        if ($query->limit()) {
            $options['limit'] = $query->limit();
        }
        $response = $client->search($query->where(), $options);

        switch ($query->where()['type']) {
            case 'company':
                $results = $response->companies();

                break;
            case 'position':
                $results = $response->positions();

                break;
            default:
                return false;
        }
        $resources = $this->_transformResults($results, $options['resourceClass']);

        return new ResultSet($resources, $response->total());
    }

    /**
     * {@inheritDoc}
     */
    protected function _transformResource(array $result, $resourceClass)
    {
        if ($resourceClass === 'IctCollege\Stagemarkt\Model\Resource\Position') {
            return $this->_createResource($resourceClass, [
                'id' => $result['id'],
                'company' => $this->_createResource('IctCollege\Stagemarkt\Model\Resource\Company', [
                    'id' => $result['company']['id'],
                    'address' => $this->_createResource('IctCollege\Stagemarkt\Model\Resource\AddressCompany', [
                        'address' => $result['company']['address']['address'],
                        'postcode' => $result['company']['address']['postcode'],
                        'city' => $result['company']['address']['city'],
                        'country' => $result['company']['address']['country']
                    ]),
                    'name' => $result['company']['name']
                ]),
                'study_program' => $this->_createResource('IctCollege\Stagemarkt\Model\Resource\StudyProgram', [
                    'id' => $result['study_program']['id'],
                    'description' => $result['study_program']['description'],
                ]),
                'learning_pathway' => $result['learning_pathway'],
                'kind' => $result['kind'],
                'description' => $result['description'],
                'amount' => $result['amount'],
            ]);
        } elseif ($resourceClass === 'IctCollege\Stagemarkt\Model\Resource\Company') {
            return $this->_createResource($resourceClass, [
                'id' => $result['id'],
                'address' => $this->_createResource('IctCollege\Stagemarkt\Model\Resource\AddressCompany', [
                    'address' => $result['address']['address'],
                    'postcode' => $result['address']['postcode'],
                    'city' => $result['address']['city'],
                    'country' => $result['address']['country']
                ]),
                'name' => $result['name']
            ]);
        }

        return false;
    }
}
