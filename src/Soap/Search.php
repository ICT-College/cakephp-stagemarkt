<?php

namespace IctCollege\Stagemarkt\Soap;

use IctCollege\Stagemarkt\Soap\Response\SearchResponse;

/**
 * Class Search
 * @package Stagemarkt\Soap
 *
 * @method \IctCollege\Stagemarkt\Soap\Response\SearchResponse Zoeken(array $parameters)
 */
class Search extends SoapClient
{

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
        $defaultOptions = [
            'page' => 1,
            'limit' => 10,
        ];
        $options = array_merge($defaultOptions, $options);

        if ($options['limit'] > 25) {
            throw new \InvalidArgumentException('The limit should not be higher than 25');
        }

        $parameters = [
            'AantalResultatenPerPagina' => $options['limit'],
            'Pagina' => $options['page'],
            'ZoekInDeBuurt' => true
        ];

        switch ($conditions['type']) {
            case 'company':
                $parameters += [
                    'LeerplaatsErkenningAanduiding' => 'E'
                ];

                if (isset($conditions['id'])) {
                    $parameters['CodeLeerbedrijf'] = $conditions['id'];
                }
                if (isset($conditions['name'])) {
                    $parameters['LeerbedrijfNaam'] = $conditions['name'];
                    $parameters['LeerbedrijfNaamExact'] = ((substr($conditions['name'], 0, 1) !== '%') && (substr($conditions['name'], -1, 1) !== '%'));
                }

                break;
            case 'position':
                $parameters += [
                    'LeerplaatsErkenningAanduiding' => 'L'
                ];

                if (isset($conditions['company_id'])) {
                    $parameters['CodeLeerbedrijf'] = $conditions['company_id'];
                }
                if (isset($conditions['company_name'])) {
                    $parameters['LeerbedrijfNaam'] = $conditions['company_name'];
                    $parameters['LeerbedrijfNaamExact'] = ((substr($conditions['company_name'], 0, 1) !== '%') && (substr($conditions['company_name'], -1, 1) !== '%'));
                }
                if (isset($conditions['company_address_number'])) {
                    $parameters['Vestigingsadres']['Huisnummer'] = $conditions['company_address_number'];
                }
                if (isset($conditions['company_address_street'])) {
                    $parameters['Vestigingsadres']['Straat'] = $conditions['company_address_street'];
                }
                if (isset($conditions['company_address_postcode'])) {
                    $parameters['PostcodeRange'] = $conditions['company_address_postcode'] . $conditions['company_address_postcode'];
                }
                if (isset($conditions['company_address_city'])) {
                    $parameters['Vestigingsadres']['Plaats'] = $conditions['company_address_city'];
                }
                if (isset($conditions['company_address_country'])) {
                    $parameters['Vestigingsadres']['Land'] = $conditions['company_address_country'];
                }
                if (isset($conditions['study_program_id'])) {
                    $parameters['Crebonummer'] = $conditions['study_program_id'];
                }
                if (isset($conditions['learning_pathway'])) {
                    $parameters['Leerweg'] = $conditions['learning_pathway'];
                }

                break;
        }

        return $this->Zoeken($parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function testingUrl()
    {
        return 'http://wl-acc.stagemarkt.nl/webservices/whitelabel/ws_whitelabelzoekenv03.asmx?WSDL';
    }

    /**
     * {@inheritDoc}
     */
    public function liveUrl()
    {
        return 'http://wl-acc.stagemarkt.nl/webservices/whitelabel/ws_whitelabelzoekenv03.asmx?WSDL';
    }

    /**
     * {@inheritDoc}
     */
    public function resultProperty()
    {
        return 'ZoekenResult';
    }

    /**
     * {@inheritDoc}
     *
     * @return \IctCollege\Stagemarkt\Soap\Response\SearchResponse
     *
     * @throws Exception\NoLicenseException
     */
    public function __call($functionName, $arguments)
    {
        $soapResponse = parent::__call($functionName, $arguments);

        $positions = [];
        $companies = [];
        if (isset($soapResponse->Resultaten->Resultaat)) {
            $results = $soapResponse->Resultaten->Resultaat;
            if (!is_array($results)) {
                $results = [$results];
            }

            foreach ($results as $result) {
                if (isset($result->LeerplaatsId)) {
                    $position = [
                        'id' => $result->LeerplaatsId,
                        'company' => [
                            'id' => $result->CodeLeerbedrijf,
                            'address' => [
                                'address' => $result->Vestigingsadres->Straat,
                                'postcode' => $result->Vestigingsadres->Postcode,
                                'city' => $result->Vestigingsadres->Plaats,
                                'country' => $this->stagemarktClient()->convertCountry($result->Vestigingsadres->Land)
                            ],
                            'name' => $result->LeerbedrijfNaam
                        ],
                        'study_program' => [
                            'id' => $result->Opleidingen->Opleiding->Crebonummer,
                            'description' => $result->Opleidingen->Opleiding->Omschrijving
                        ],
                        'learning_pathway' => $result->Leerweg,
                        'kind' => $result->LeerplaatsSoort,
                        'description' => ($result->VacatureLeerplaatsOmschrijving) ? $result->VacatureLeerplaatsOmschrijving : null,
                        'amount' => $result->LeerplaatsAantal
                    ];

                    $positions[] = $position;
                } else {
                    if (isset($companies[$result->CodeLeerbedrijf])) {
                        $companies[$result->CodeLeerbedrijf]['accreditation'][] = [
                            'study_program_id' => $result->Erkenning->Crebonummer,
                        ];
                        continue;
                    }



                    $company = [
                        'id' => $result->CodeLeerbedrijf,
                        'address' => [
                            'address' => $result->Vestigingsadres->Straat,
                            'postcode' => $result->Vestigingsadres->Postcode,
                            'city' => $result->Vestigingsadres->Plaats,
                            'country' => $this->stagemarktClient()->convertCountry($result->Vestigingsadres->Land),
                        ],
                        'accreditation' => [
                            [
                                'study_program_id' => $result->Erkenning->Crebonummer,
                            ]
                        ],
                        'name' => $result->LeerbedrijfNaam,
                        'description' => ($result->VacatureLeerplaatsOmschrijving) ? $result->VacatureLeerplaatsOmschrijving : null,
                        'learning_pathway' => $result->Leerweg
                    ];

                    $companies[$company['id']] = $company;
                }
            }

            $companies = array_values($companies);
        }

        $response = new SearchResponse();
        $response->setCode($soapResponse->Signaalcode)
            ->positions($positions)
            ->companies($companies)
            ->total($soapResponse->AantalResultatenTotaal);

        return $response;
    }
}
