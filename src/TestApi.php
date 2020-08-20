<?php

namespace App;

use App\Api\DnslinqApi;
use App\Api\Domain;
use App\Api\PersonHandle;
use App\Api\User;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;


class TestApi
{
    protected $dnslinq_url = 'https://api.dnslinq.de';
    protected $dnslinq_bearer_token = 'INSERT-YOUR-JWT-TOKEN-HERE';

    /**
     * this method will generate a bearer token for your account
     * @throws Exception
     */
    public function generateJWT()
    {
        $client = new DnslinqApi(null, $this->dnslinq_url, null);
        $this->dnslinq_bearer_token = $client->generateJWT('your@email.com','yourpassword','youridentifier')->accessToken;
    }

    public function run()
    {
        // Let's create an Domain Object for Test Purpose
        $domain = new Domain();
        $domain->domain_sld = 'example';
        $domain->domain_tld = 'de';

        // Also create an Person Handle Object for Test Purpose
        $personHandle = new PersonHandle();
        $personHandle->email = 'max@mustermann.de';
        $personHandle->firstname = 'Max';
        $personHandle->lastname = 'Mustermann';
        $personHandle->organisation = 'Musterfirma';
        $personHandle->street = 'Musterstraße';
        $personHandle->number = '1';
        $personHandle->postcode = '12345';
        $personHandle->city = 'Musterstadt';
        $personHandle->region = 'Musterbundesland';
        $personHandle->country = 'Musterland';
        $personHandle->phone = '+49.09876543210';
        $personHandle->fax = '+49.09876543210';

        // Just for specific Domains like .es .it .ru
        $personHandle->idcard = 'MUSTERPERSO';
        $personHandle->idcardissuedate = '01.01.2020';
        $personHandle->idcardauthority = 'Musterverwaltung Musterstadt';

        // Just for specific Domains like .ru
        $personHandle->countryofbirth = 'DE';
        $personHandle->dateofbirth = '2000-01-18';
        $personHandle->regionofbirth = 'Musterbundesland';
        $personHandle->placeofbirth = 'Mustergeburtsort';
        $personHandle->registrationnumber = 'registrationnumber';

        // Then create a New DNSLINQ API CLIENT
        $client = new DnslinqApi($domain, $this->dnslinq_url, $this->dnslinq_bearer_token);
        print("DNSLINQ API TEST CLASS \n");
        /*
         * check if a domain is available for registration
         */
        print("\nCheck Availability for " . $domain->getDomainName() . ":");
        var_dump($client->checkAvailability($domain->domain_sld, $domain->domain_tld));

        /**
         * ! Pricelist Section !
         */
        // print("\nList of AvailableTLDs :")
        // var_dump($client->getAvailableTldsAndPrices());


        /**
         * ! PersonHandle Section !
         */
        // Create Person Handle
        // $client->createHandlePers($personHandle);


        /**
         * !!! ATTENTION - PAY SECTION !!!
         * !!! CREATE DOMAIN COST REAL MONEY !!!
         */
        // $client->create($domain->domain_sld,$domain->domain_tld,$personID);


        /**
         *  User Section - Create an User
         *  Uncomment the Parts you want to Test
         */
        $user = new User();
        $user->id = 1;
        $user->email = 'max@mustermann.de';
        $user->username = 'm.mustermann';
        $password = base64_encode(random_bytes(16));
        // check if user already exists, we expect 404 to continue with creation, meaning that the user does not yet exist in dnslinq
        try {
            print("\nUser-ID:");
            var_dump($userId = $client->getUserByExternalId($user->id)->id);
        } catch (ClientException $e) {
            if ($e->getCode() == 404) {
                // user does not exist yet, create!
                $client->createUser($user, $password);
            }
            // rethrow if not 404
            // throw $e;
        } catch (GuzzleException $e) {
            throw $e;
        }

        /**
         *  User Section - Login as User
         *  Uncomment the parts you want to test
         */
        try {
            $userId = $client->getUserByExternalId($user->id)->id;
            $loginResponse = $client->loginAsUser($userId);
            print("\nAccessToken:");
            var_dump($loginResponse->accessToken);
        } catch (GuzzleException $e) {
            throw $e;
        }

    }

}