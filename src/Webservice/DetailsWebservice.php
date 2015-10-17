<?php

namespace IctCollege\Stagemarkt\Webservice;

use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Webservice\Webservice;

class DetailsWebservice extends Webservice
{

    /**
     * {@inheritDoc}
     */
    protected function _executeReadQuery(Query $query, array $options = [])
    {
        /* @var \IctCollege\Stagemarkt\Stagemarkt $client */
        $client = $this->driver()->client();

        switch ($query->where()['type']) {
            case 'company':
                $response = $client->detailsForCompany($query->where()['id']);

                return new ResultSet([$this->_transformResource($response->company(), $options['resourceClass'])], 1);
            case 'position':
                $response = $client->detailsForPosition($query->where()['id']);

                return new ResultSet([$this->_transformResource($response->position(), $options['resourceClass'])], 1);
            default:
                return false;
        }
    }
}
