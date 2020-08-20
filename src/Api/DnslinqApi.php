<?php

namespace App\Api;


use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Httpful\Request;

class DnslinqApi
{
    private $product;
    private $client;

    public function __construct($domain, $dnslinq_url, $dnslinq_bearertoken)
    {
        $this->product = $domain;

        // prepare api client
        $this->client = new Client([
            'base_uri' => $dnslinq_url,
            'timeout' => 25.0,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'bearer ' . $dnslinq_bearertoken,
            ],
        ]);
    }


    /**
     * @param String $sld
     * @param String $tld
     * @return mixed
     * @throws GuzzleException
     * @throws Exception
     */
    public function checkAvailability(string $sld, string $tld)
    {
        $response = $this->client->request('POST', '/registrar/domaincheck', [
            'json' => [
                'sld' => $sld,
                'tld' => $tld,
            ],
        ]);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response->isAvailable;
    }

    /**
     * query the pricelist of the reseller --> get all available TLDs with their price
     * @return mixed
     * @throws GuzzleException
     * @throws Exception
     */
    public function getAvailableTldsAndPrices()
    {
        $response = $this->client->request('GET', '/price-lists/my');
        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response->__prices__;
    }

    /**
     * create user account in dnslinq
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function createUser(User $user, $password)
    {
        /**
         * {
         * "name": "string",
         * "email": "string",
         * "password": "string",
         * "company": 0,
         * "role": "string"
         * }
         */
        $response = $this->client->request('POST', '/users', [
            'json' => [
                'email' => $user->email,
                'name' => $user->username,
                'password' => $password,
                'externalId' => (int)$user->id, // convert to string, since backend allows strings as external id
            ],
        ]);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }

    /**
     * retrieve JWT token for an user
     * @throws Exception
     */
    public function loginAsUser($dnslinqUserId)
    {
        // /auth/login-as-user/{id}
        $response = $this->client->request('POST', '/auth/login-as-user/' . $dnslinqUserId, [
            'json' => [
                'expiresIn' => 3600 * 24 * 7,
            ],
        ]);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        /** RETURNS:
         * {
         * "id": 5,
         * "email": "email@domain.com",
         * "companyIdentifier": "LB",
         * "name": "test",
         * "roles": "RESELLER",
         * "accessToken": "blablabla",
         * "expiresIn": 3600
         * }
         */
        return $json_response;
    }
    /**
     * retrieve JWT token after Auth
     * @throws Exception
     */
    public function generateJWT($email,$password,$identifier)
    {
        // /auth/login/
        $response = $this->client->request('POST', '/auth/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
                'companyIdentifier' => $identifier,
                'expiresIn' => 3600 * 24 * 7, // Set this to 5 Years in Seconds for your API Client
            ],
        ]);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        /** RETURNS:
         * {
        "id": 1,
        "email": "your@account.com",
        "companyIdentifier": "yourreselleridentifer",
        "name": "yourname",
        "roles": "RESELLER",
        "accessToken": "YOUR-JWT-TOKEN",
        "expiresIn": 36000
        }
         */
        return $json_response;
    }

    /**
     * get user by external id from dnslinq database
     * @param $externalId
     * @return mixed
     * @throws GuzzleException
     * @throws Exception
     */
    public function getUserByExternalId($externalId)
    {
        $response = $this->client->request('GET', '/users/external-id/' . $externalId);
        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }

    /**
     * updates a user in dnslinq database
     * @param $dnslinqId
     * @param User $user
     * @param $password
     * @return mixed
     * @throws Exception
     */
    public function updateUser($dnslinqId, $user, $password)
    {
        $response = $this->client->request('PUT', '/users/' . $dnslinqId, [
            'json' => [
                'email' => $user->email,
                'name' => $user->username,
                'password' => $password,
            ],
        ]);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }

    /**
     * updates a user in dnslinq database
     * @param $dnslinqId
     * @param User $user
     * @return mixed
     * @throws Exception
     */
    public function updateEMail($dnslinqId, User $user)
    {
        $response = $this->client->request('PUT', '/users/' . $dnslinqId, [
            'json' => [
                'email' => $user->email,
                'name' => $user->username,
            ],
        ]);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }

    /**
     * deletes an user in dnslinq
     * @throws GuzzleException
     * @throws Exception
     */
    public function deleteUser($dnslinqid)
    {
        $response = $this->client->request('DELETE', '/users/' . $dnslinqid);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }

    /**
     * create the user's person handle in dnslinq
     * @throws GuzzleException
     * @throws Exception
     */
    public function createHandlePers(PersonHandle $personHandle)
    {
        $response = $this->client->request('POST', '/handle/pers', [
            'json' => [
                'email' => $personHandle->email,
                'firstname' => $personHandle->firstname,
                'lastname' => $personHandle->lastname,
                'organisation' => $personHandle->organisation,
                'street' => $personHandle->street,
                'number' => $personHandle->number,
                'postcode' => $personHandle->postcode,
                'city' => $personHandle->city,
                'region' => $personHandle->region,
                'country' => $personHandle->country,
                'countryofbirth' => $personHandle->countryofbirth ?? 'DE',
                'phone' => $personHandle->phone,
                'fax' => $personHandle->fax,
                'idcard' => $personHandle->idcard,
                'idcardissuedate' => $personHandle->idcardissuedate,
                'idcardauthority' => $personHandle->idcardauthority,
                'dateofbirth' => $personHandle->dateofbirth,
                'regionofbirth' => $personHandle->regionofbirth,
                'placeofbirth' => $personHandle->placeofbirth,
                'registrationnumber' => $personHandle->registrationnumber,
            ],
        ]);
        $json_response = json_decode((string)$response->getBody());

        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }

        return $json_response;
    }

    /**
     * this will register a new domain, if you want to transfer a domain from elsewhere, please specify the authcode --> leave empty for new registration
     * @param string $sld
     * @param string $tld
     * @param $handlePersId
     * @param null $authCode
     * @throws Exception
     */
    public function create(string $sld, string $tld, $handlePersId, $authCode = null)
    {
        if (!empty($authCode)) {
            $json_body = [
                'sld' => $sld,
                'tld' => $tld,
                'handlePersId' => $handlePersId,
                'authCode' => $authCode,
            ];
        } else {
            $json_body = [
                'sld' => $sld,
                'tld' => $tld,
                'handlePersId' => $handlePersId,
            ];
        }

        $response = $this->client->request('POST', '/registrar/register', [
            'json' => $json_body,
        ]);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }

        // save handleDomain id from received task to our product
        $this->product->id_in_dnslinq = $json_response->__handleDomain__->id;
        $this->product->save();
    }

    /**
     * get business logs for a certain time range
     * @param $dateStart
     * @param $dateEnd
     * @return mixed
     * @throws GuzzleException
     * @throws Exception
     *
     * {
     * "companyId": 0,
     * "dateStart": {},
     * "dateEnd": {}
     * }
     *
     */
    public function getBusinessLogsByInterval($dateStart, $dateEnd)
    {
        // if no start date was given, assume the day before at 00:00:01 (right in the beginning)
        if ($dateStart == null) {
            $dateStart = Carbon::now()->subDays(1)->startOfDay();
        }
        // if no end date was given, assume now
        if ($dateEnd == null) {
            $dateEnd = Carbon::now();
        }
        $response = $this->client->request('POST', '/business-log/query', [
            'json' => [
                'dateStart' => $dateStart,
                'dateEnd' => $dateEnd,
            ],
        ]);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }


    /**
     * get domain status
     * @return mixed
     * @throws GuzzleException
     * @throws Exception
     */
    public function getDomainStatus()
    {
        $response = $this->client->request('GET', '/handle/domain', [
            'json' => [

            ],
        ]);
        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }

    /**
     * create a new ownership object for a domain and thus give access to the domain to the user
     * @param Domain $domain
     * @param $dnslinqUserId
     * @return mixed
     * @throws Exception
     */
    public function createDomainOwnerhip(Domain $domain, $dnslinqUserId)
    {
        $response = $this->client->request('POST', '/domain-ownerships', [
            'json' => [
                'handleDomainId' => $domain->id_in_dnslinq,
                'userId' => $dnslinqUserId,
            ],
        ]);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }


    /**
     * This will delete a domain!
     * @throws GuzzleException
     * @throws Exception
     */
    public function delete(Domain $domain)
    {
        $response = $this->client->request('DELETE', '/registrar/' . $domain->id_in_dnslinq, [
            'json' => [
                'deleteImmediately' => true,
            ],
        ]);

        json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
    }


    /**
     * retrieve a dns zone by name
     * @param $zoneName
     * @return mixed
     * @throws Exception
     */
    public function getZoneByName($zoneName)
    {
        $response = $this->client->request('GET', '/domain-zones/by-name/' . $zoneName);

        try {
            $json_response = json_decode((string)$response->getBody());
            if (json_last_error() != 0) {
                throw new Exception('failed to decode json');
            }
            return $json_response->id;
        } catch (ClientException $e) {
            // catch unfound zones and create them
            if ($e->getCode() === 404) {
                throw new Exception('unable to get zone-id for zone-name ' . $zoneName);
            }
        }

    }

    /**
     * delete a record from a dns zone
     * @param $recordId
     * @return mixed
     * @throws Exception
     */
    public function deleteRecordFromZone($recordId)
    {
        $response = $this->client->request('DELETE', '/records/' . $recordId);

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }

    /**
     * retrieve all records from a zone
     * @param $dnslinqZoneId
     * @return mixed
     * @throws Exception
     */
    public function getRecordsInZone($dnslinqZoneId)
    {
        $response = $this->client->request('GET', '/domain-zones/' . $dnslinqZoneId . '/records');

        $json_response = json_decode((string)$response->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        return $json_response;
    }

    /**
     * create a new record in a zone
     * @param $recordValue
     * @return array
     * @throws Exception
     */
    public function createDNSRecords($recordValue)
    {
        $address = $this->product->getDomainName();
        try {
            $dnslinqZoneId = $this->getZoneByName($address);
        } catch (ClientException $e) {
            // if the zone is missing (404), then attempt to create it
            if ($e->getCode() === 404) {
                throw new Exception($address . ' zone not found ');

            }
        }
        $foundRecords = [];
        $existingRecords = ($this->getRecordsInZone($dnslinqZoneId));
        // loop through all existing zone records and search if we already have one for this ip
        foreach ($existingRecords as $record) {
            if ($record->type === 'CNAME' && $record->name === 'webmail' . '.' . $address) {
                // found old record --> delete first
                $foundRecords[] = $record;
                continue;
            }
            if ($record->type === 'MX' && $record->name === $address) {
                // found old record --> delete first
                $foundRecords[] = $record;
                continue;
            }
        }
        // if we have some old records that match --> delete them first
        if (!empty($foundRecords)) {
            foreach ($foundRecords as $oldRecord) {
                $this->deleteRecordFromZone($oldRecord->id);
            }
        }

        // finally create new record
        $response1 = $this->client->request('POST', '/domain-zones/' . $dnslinqZoneId . '/records', [
            'json' => [
                'name' => 'webmail',
                'type' => 'CNAME',
                'content' => $recordValue,
            ],
        ]);

        $json_response1 = json_decode((string)$response1->getBody());
        if (json_last_error() != 0) {
            throw new Exception('failed to decode json');
        }
        $record_ids = [];
        array_push($record_ids, $json_response1->id);
        return $record_ids;
    }
}
