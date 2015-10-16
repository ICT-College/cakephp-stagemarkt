<?php

namespace IctCollege\Stagemarkt\Soap;

use IctCollege\Stagemarkt\Response\SearchResponse;
use IctCollege\Stagemarkt\Model\Resource\Position;
use IctCollege\Stagemarkt\Model\Resource\Company;
use IctCollege\Stagemarkt\Model\Resource\StudyProgram;
use IctCollege\Stagemarkt\Model\Resource\AddressCompany;
use IctCollege\Stagemarkt\Model\Resource\Accreditation;
use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;

/**
 * Class Search
 * @package Stagemarkt\Soap
 *
 * @method SearchResponse Zoeken(array $parameters)
 */
class Search extends StagemarktService
{

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

    public function __call($function_name, $arguments)
    {
        $soapResponse = parent::__call($function_name, $arguments);

        $positions = [];
        $companies = [];
        if (isset($soapResponse->Resultaten->Resultaat)) {
            $results = $soapResponse->Resultaten->Resultaat;
            if (!is_array($results)) {
                $results = [$results];
            }

            foreach ($results as $result) {
                if (isset($result->LeerplaatsId)) {
                    $position = new Position([
                        'id' => $result->LeerplaatsId,
                        'company' => new Company([
                            'id' => $result->CodeLeerbedrijf,
                            'address' => new AddressCompany([
                                'address' => $result->Vestigingsadres->Straat,
                                'postcode' => $result->Vestigingsadres->Postcode,
                                'city' => $result->Vestigingsadres->Plaats,
                                'country' => $result->Vestigingsadres->Land,
                            ], [
                                'markClean' => true,
                                'markNew' => false,
                            ]),
                            'name' => $result->LeerbedrijfNaam
                        ], [
                            'markClean' => true,
                            'markNew' => false,
                        ]),
                        'study_program' => new StudyProgram([
                            'id' => $result->Opleidingen->Opleiding->Crebonummer,
                            'description' => $result->Opleidingen->Opleiding->Omschrijving,
                        ], [
                            'markClean' => true,
                            'markNew' => false,
                        ]),
                        'learning_pathway' => $result->Leerweg,
                        'kind' => $result->LeerplaatsSoort,
                        'description' => ($result->VacatureLeerplaatsOmschrijving) ? $result->VacatureLeerplaatsOmschrijving : null,
                        'amount' => $result->LeerplaatsAantal
                    ], [
                        'markClean' => true,
                        'markNew' => false,
                    ]);

                    $positions[] = $position;
                } else {
                    if (isset($companies[$result->CodeLeerbedrijf])) {
                        $companies[$result->CodeLeerbedrijf]->accreditation[] = new Accreditation([
                            'study_program_id' => $result->Erkenning->Crebonummer,
                        ], [
                            'markClean' => true,
                            'markNew' => false,
                        ]);
                        continue;
                    }

                    $company = new Company([
                        'id' => $result->CodeLeerbedrijf,
                        'address' => new AddressCompany([
                            'address_line' => $result->Vestigingsadres->Straat,
                            'postcode' => $result->Vestigingsadres->Postcode,
                            'city' => $result->Vestigingsadres->Plaats,
                            'country' => $result->Vestigingsadres->Land,
                        ], [
                            'markClean' => true,
                            'markNew' => false,
                        ]),
                        'accreditation' => [
                            new Accreditation([
                                'study_program_id' => $result->Erkenning->Crebonummer,
                            ], [
                                'markClean' => true,
                                'markNew' => false,
                            ])
                        ],
                        'name' => $result->LeerbedrijfNaam,
                    ], [
                        'markClean' => true,
                        'markNew' => false,
                    ]);

                    $companies[$company->id] = $company;
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

    public function execute(Query $query, array $options = [])
    {
        if ($query->action() !== Query::ACTION_READ) {
            throw new \BadMethodCallException;
        }

        if (isset($query->where()['id'])) {
            return $this->stagemarktClient()->detailsClient()->execute($query);
        }

        $options = [];
        if ($query->page()) {
            $options['page'] = $query->page();
        }
        if ($query->limit()) {
            $options['limit'] = $query->limit();
        }
        $response = $this->search(
            $query->where(), $options
        );

        switch ($query->where()['type']) {
            case 'company':
                $entities = $response->companies();

                break;
            case 'position':
                $entities = $response->positions();

                break;
        }

        return new ResultSet($entities, $response->total());
    }
}
