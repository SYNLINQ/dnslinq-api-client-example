<?php
namespace App\Api;
class Domain
{
    /**
     *  The domain sld part --> domain.com --> domain would be sld
     * @var string
     */
    public $domain_sld ;

    /**
     *  The domain tld part --> domain.com --> com would be tld
     * @var string
     */
    public $domain_tld ;
    /**
     *  The domain id in dnslinq
     * @var string
     */
    public $id_in_dnslinq;

    /**
     * returns sld and tld combined as domain name
     * @return string
     */
    public function getDomainName()
    {
        return $this->domain_sld . '.' . $this->domain_tld;
    }
}
