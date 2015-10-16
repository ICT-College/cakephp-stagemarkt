<?php

namespace IctCollege\Stagemarkt\Soap;

use Cake\I18n\Time;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Query;
use IctCollege\Stagemarkt\Model\Resource\AddressCompany;
use IctCollege\Stagemarkt\Model\Resource\Company;
use IctCollege\Stagemarkt\Model\Resource\ContactpersonCompany;
use IctCollege\Stagemarkt\Model\Resource\Position;
use IctCollege\Stagemarkt\Model\Resource\QualificationPart;
use IctCollege\Stagemarkt\Model\Resource\StudyProgram;
use IctCollege\Stagemarkt\Response\DetailsResponse;

class Details extends StagemarktService
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

    public function __call($function_name, $arguments)
    {
        $soapResponse = parent::__call($function_name, $arguments);

        if (isset($arguments[0]['LeerplaatsId'])) {
            $position = new Position([
                'id' => $arguments[0]['LeerplaatsId'],
                'company' => new Company([
                    'id' => $soapResponse->CodeLeerbedrijf,
                    'address' => new AddressCompany([
                        'address' => $soapResponse->Vestigingsadres->Straat,
                        'postcode' => $soapResponse->Vestigingsadres->Postcode,
                        'city' => $soapResponse->Vestigingsadres->Plaats,
                        'country' => $soapResponse->Vestigingsadres->Land,
                    ], [
                        'markClean' => true,
                        'markNew' => false,
                    ]),
                    'correspondence_address' => new AddressCompany([
                        'address' => $soapResponse->Correspondentieadres->Straat,
                        'postcode' => $soapResponse->Correspondentieadres->Postcode,
                        'city' => $soapResponse->Correspondentieadres->Plaats,
                        'country' => $soapResponse->Correspondentieadres->Land,
                    ], [
                        'markClean' => true,
                        'markNew' => false,
                    ]),
                    'condactperson' => new ContactpersonCompany([
                        'name' => $soapResponse->Contactpersoon->Naam,
                        'email' => $soapResponse->Contactpersoon->Email,
                        'telephone' => $soapResponse->Contactpersoon->Telefoonnummer,
                    ], [
                        'markClean' => true,
                        'markNew' => false,
                    ]),
                    'name' => $soapResponse->Naam,
                    'website' => @$soapResponse->WebsiteUrl,
                    'email' => @$soapResponse->Email,
                    'telephone' => @$soapResponse->Telefoonnummer,
                    'branch' => @$soapResponse->Branche,
                ], [
                    'markClean' => true,
                    'markNew' => false,
                ]),
                'study_program' => new StudyProgram([
                    'id' => $soapResponse->Opleidingen->Opleiding->Crebonummer,
                    'description' => $soapResponse->Opleidingen->Opleiding->Omschrijving,
                ], [
                    'markClean' => true,
                    'markNew' => false,
                ]),
                'description' => ($soapResponse->Omschrijving) ? $soapResponse->Omschrijving : null,
                'start' => new Time($soapResponse->Startdatum, new \DateTimeZone('Europe/Amsterdam')),
                'end' => new Time($soapResponse->Einddatum, new \DateTimeZone('Europe/Amsterdam')),
            ], [
                'markClean' => true,
                'markNew' => false,
            ]);

            $qualificationParts = [];
            foreach ($soapResponse->Kwalificatieonderdelen->Kwalificatieonderdeel as $qualificationPart) {
                $index = substr($qualificationPart->Omschrijving, 0, 2);
                $description = $qualificationPart->Omschrijving;
                if (!is_numeric($index)) {
                    $index = mt_rand(1, 9999);
                } else {
                    $description = substr($description, 3);
                }

                $qualificationParts[(int) $index] = new QualificationPart([
                    'type' => $qualificationPart->Type,
                    'description' => $description
                ], [
                    'markClean' => true,
                    'markNew' => false,
                ]);
            }
            ksort($qualificationParts);

            $position['qualification_parts'] = array_values($qualificationParts);

            $response = new DetailsResponse();
            $response->setCode($soapResponse->Signaalcode)
                ->position($position);
        } elseif (isset($arguments[0]['CodeLeerbedrijf'])) {
            $company = new Company([
                'address' => $soapResponse->Vestigingsadres->Straat,
                'postcode' => $soapResponse->Vestigingsadres->Postcode,
                'city' => $soapResponse->Vestigingsadres->Plaats,
                'country' => $soapResponse->Vestigingsadres->Land,
                'correspondence_address' => $soapResponse->Correspondentieadres->Straat,
                'correspondence_postcode' => $soapResponse->Correspondentieadres->Postcode,
                'correspondence_city' => $soapResponse->Correspondentieadres->Plaats,
                'correspondence_country' => $soapResponse->Correspondentieadres->Land,
            ], [
                'markClean' => true,
                'markNew' => false
            ]);

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

                $company->set($field, trim($soapResponse->{$remoteField}));
            }

            if ($company->get('website')) {
                $website = $company->get('website');

                if ((substr($website, 0, 7) !== 'http://') && (substr($website, 0, 8) !== 'https://')) {
                    $website = 'http://' . $website;
                }

                $company->unsetProperty('website');

                $company->set('website', $website);
            }

            $company->clean();

            $response = new DetailsResponse();
            $response->setCode($soapResponse->Signaalcode)
                ->company($company);
        } else {
            return false;
        }

        return $response;
    }

    public function execute(Query $query, array $options = [])
    {
        if ($query->action() !== Query::ACTION_READ) {
            throw new \BadMethodCallException;
        }

        $response = $this->details($query->where(), $query->getOptions());

        switch ($query->where()['type']) {
            case 'company':
                $entity = $response->company();

                break;
            case 'position':
                $entity = $response->position();

                break;
        }

        return new ResultSet([$entity], 1);
    }
}
