<?php

namespace IctCollege\Stagemarkt\Soap;

use Cake\I18n\Time;
use IctCollege\Stagemarkt\Soap\Response\DetailsResponse;

class Details extends SoapClient
{

    /**
     * @param array $conditions
     * @return DetailsResponse
     */
    public function details(array $conditions)
    {
        $parameters = [];

        if ($conditions['type'] === 'company') {
            $parameters['CodeLeerbedrijf'] = $conditions['id'];
        }
        if ($conditions['type'] === 'position') {
            $parameters['LeerplaatsId'] = $conditions['id'];
        }

        return $this->GeefDetails($parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function testingUrl()
    {
        return 'http://wl-acc.stagemarkt.nl/webservices/whitelabel/ws_vacaturedetails.asmx?WSDL';
    }

    /**
     * {@inheritDoc}
     */
    public function liveUrl()
    {
        return 'http://wl-acc.stagemarkt.nl/webservices/whitelabel/ws_vacaturedetails.asmx?WSDL';
    }

    /**
     * {@inheritDoc}
     */
    public function resultProperty()
    {
        return 'GeefDetailsResult';
    }

    public function __call($functionName, $arguments)
    {
        $soapResponse = parent::__call($functionName, $arguments);

        if (isset($arguments[0]['LeerplaatsId'])) {
            $position = [
                'id' => $arguments[0]['LeerplaatsId'],
                'company' => [
                    'id' => $soapResponse->CodeLeerbedrijf,
                    'address' => [
                        'address' => $soapResponse->Vestigingsadres->Straat,
                        'postcode' => $soapResponse->Vestigingsadres->Postcode,
                        'city' => $soapResponse->Vestigingsadres->Plaats,
                        'country' => $this->stagemarktClient()->convertCountry($soapResponse->Vestigingsadres->Land),
                    ],
                    'correspondence_address' => [
                        'address' => $soapResponse->Correspondentieadres->Straat,
                        'postcode' => $soapResponse->Correspondentieadres->Postcode,
                        'city' => $soapResponse->Correspondentieadres->Plaats,
                        'country' => $this->stagemarktClient()->convertCountry($soapResponse->Correspondentieadres->Land),
                    ],
                    'condactperson' => [
                        'name' => $soapResponse->Contactpersoon->Naam,
                        'email' => $soapResponse->Contactpersoon->Email,
                        'telephone' => $soapResponse->Contactpersoon->Telefoonnummer,
                    ],
                    'name' => $soapResponse->Naam,
                    'website' => @$soapResponse->WebsiteUrl,
                    'email' => @$soapResponse->Email,
                    'telephone' => @$soapResponse->Telefoonnummer,
                    'branch' => @$soapResponse->Branche,
                ],
                'study_program' => [
                    'id' => $soapResponse->Opleidingen->Opleiding->Crebonummer,
                    'description' => $soapResponse->Opleidingen->Opleiding->Omschrijving,
                ],
                'description' => ($soapResponse->Omschrijving) ? $soapResponse->Omschrijving : null,
                'start' => new Time($soapResponse->Startdatum, new \DateTimeZone('Europe/Amsterdam')),
                'end' => new Time($soapResponse->Einddatum, new \DateTimeZone('Europe/Amsterdam')),
            ];

            $qualificationParts = [];
            foreach ($soapResponse->Kwalificatieonderdelen->Kwalificatieonderdeel as $qualificationPart) {
                $index = substr($qualificationPart->Omschrijving, 0, 2);
                $description = $qualificationPart->Omschrijving;
                if (!is_numeric($index)) {
                    $index = mt_rand(1, 9999);
                } else {
                    $description = substr($description, 3);
                }

                $qualificationParts[(int)$index] = [
                    'type' => $qualificationPart->Type,
                    'description' => $description
                ];
            }
            ksort($qualificationParts);

            $position['qualification_parts'] = array_values($qualificationParts);

            $response = new DetailsResponse();
            $response->setCode($soapResponse->Signaalcode)
                ->position($position);
        } elseif (isset($arguments[0]['CodeLeerbedrijf'])) {
            $company = [
                'address' => $soapResponse->Vestigingsadres->Straat,
                'postcode' => $soapResponse->Vestigingsadres->Postcode,
                'city' => $soapResponse->Vestigingsadres->Plaats,
                'country' => $this->stagemarktClient()->convertCountry($soapResponse->Vestigingsadres->Land),
                'correspondence_address' => $soapResponse->Correspondentieadres->Straat,
                'correspondence_postcode' => $soapResponse->Correspondentieadres->Postcode,
                'correspondence_city' => $soapResponse->Correspondentieadres->Plaats,
                'correspondence_country' => $this->stagemarktClient()->convertCountry($soapResponse->Correspondentieadres->Land),
            ];

            $fields = [
                'id' => 'CodeLeerbedrijf',
                'name' => 'Naam',
                'email' => 'Email',
                'website' => 'WebsiteUrl',
                'telephone' => 'Telefoonnummer',
                'branch' => 'Branche'
            ];
            foreach ($fields as $field => $remoteField) {
                if (!isset($soapResponse->{$remoteField})) {
                    continue;
                }
                if (!trim($soapResponse->{$remoteField})) {
                    continue;
                }

                $company[$field] = trim($soapResponse->{$remoteField});
            }

            if (!empty($company['website'])) {
                $website = $company['website'];

                if ((substr($website, 0, 7) !== 'http://') && (substr($website, 0, 8) !== 'https://')) {
                    $website = 'http://' . $website;
                }

                $company['website'] = $website;
            }

            $response = new DetailsResponse();
            $response->setCode($soapResponse->Signaalcode)
                ->company($company);
        } else {
            return false;
        }

        return $response;
    }
}
