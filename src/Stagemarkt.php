<?php

namespace IctCollege\Stagemarkt;

use Cake\Core\InstanceConfigTrait;
use IctCollege\Stagemarkt\Soap\Details;
use IctCollege\Stagemarkt\Soap\Search;

class Stagemarkt
{

    use InstanceConfigTrait;

    protected $_defaultConfig = [
        'testing' => false
    ];

    /**
     * @var \IctCollege\Stagemarkt\Soap\Search
     */
    private $__searchClient;

    /**
     * @var \IctCollege\Stagemarkt\Soap\Details
     */
    private $__detailsClient;

    /**
     * Construct the Stagemarkt client
     *
     * @param array $options The configuration to use
     */
    public function __construct(array $options)
    {
        $this->config($options);
    }

    /**
     * Search for positions and companies
     *
     * @param array $conditions The conditions to apply search with
     * @param array $options The options to apply to the search
     *
     * @return \IctCollege\Stagemarkt\Soap\Response\SearchResponse
     */
    public function search(array $conditions, array $options = [])
    {
        return $this->searchClient()->search($conditions, $options);
    }

    /**
     * Get details for a position
     *
     * @param string $position The ID of the position to get details for
     *
     * @return \IctCollege\Stagemarkt\Soap\Response\DetailsResponse
     */
    public function detailsForPosition($position)
    {
        return $this->detailsClient()->details([
            'type' => 'position',
            'id' => $position
        ]);
    }

    /**
     * Get details for a company
     *
     * @param string $company The ID of the company to get details for
     *
     * @return \IctCollege\Stagemarkt\Soap\Response\DetailsResponse
     */
    public function detailsForCompany($company)
    {
        return $this->detailsClient()->details([
            'type' => 'company',
            'id' => $company
        ]);
    }

    /**
     * @param null|\IctCollege\Stagemarkt\Soap\Search
     *
     * @return \IctCollege\Stagemarkt\Soap\Search|$this
     */
    public function searchClient($searchClient = null)
    {
        if ($searchClient !== null) {
            $this->__searchClient = $searchClient;

            return $this;
        }

        if (!$this->__searchClient) {
            $this->__searchClient = new Search($this->config());
            $this->__searchClient->stagemarktClient($this);
        }

        return $this->__searchClient;
    }

    /**
     * @return \IctCollege\Stagemarkt\Soap\Details
     */
    public function detailsClient()
    {
        if (!$this->__detailsClient) {
            $this->__detailsClient = new Details($this->config());
            $this->__detailsClient->stagemarktClient($this);
        }

        return $this->__detailsClient;
    }

    /**
     * Converts a Dutch company name to a country code
     *
     * @param string $country The country name to convert
     *
     * @return null|string The converted country code
     */
    public function convertCountry($country)
    {
        switch ($country) {
            case 'Nederland':
            case 'NEDERLAND':
                return 'NL';
            case 'China':
                return 'CN';
            case 'Spanje':
                return 'ES';
            case 'Verenigd Koninkrijk':
                return 'GB';
            case 'Turkije':
                return 'TR';
            case 'Curaçao':
                return 'CW';
            case 'Finland':
                return 'FI';
            case 'Noorwegen':
                return 'NO';
            case 'Aruba':
                return 'AW';
            case 'India':
                return 'IN';
            case 'Australië':
                return 'AU';
            case 'Zuid-Afrika':
                return 'ZA';
        }

        return null;
    }
}
