<?php

namespace NetglueTwilio\Service;

use Twilio\Rest\Client as TwilioClient;
use Zend\Cache\Storage\StorageInterface as Cache;
use NetglueTwilio\Filter\E164;
use Webmozart\Assert\Assert;

class NumberLookup
{

    /**
     * @var TwilioClient
     */
    private $client;

    /**
     * @var E164
     */
    private $e164;

    public function __construct(TwilioClient $client, Cache $cache = null)
    {
        $this->client = $client;
    }

    public function setCache(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    public function getCache()
    {
        return $this->cache;
    }

    public function lookup($number, $countryCode = null)
    {
        $lookup = $this->normaliseNumber($number, $countryCode);
        if ($this->cache) {
            $cacheKey = $this->cacheKey($lookup);
            if ($this->cache->hasItem($cacheKey)) {
                $result = $this->cache->getItem($cacheKey, $hit);
                if ($hit === true) {
                    return $result;
                }
            }
        }
        $result = $this->apiLookup($number, $countryCode);
        var_dump($result);

    }

    private function apiLookup($number, $countryCode)
    {
        return $this->client->api->v2010->lookups->phoneNumber($number, [
            'countryCode' => $countryCode
        ]);
    }

    /**
     * Generate a cache key with a phone number
     *
     * As not all cache adapters are good at anything other than [a-z0-9]+, the key is an md5
     * Sorry if that's no good for you…
     */
    private function cacheKey($number)
    {
        return md5($number);
    }

    /**
     * Normalise number string
     * @param string $number
     * @param string $country 2 Letter ISO Country Code
     * @return string
     * @throws InvalidArgumentException
     */
    private function normaliseNumber($number, $country)
    {
        Assert::scalar($number, 'Phone numbers must be scalar values in order to be normalised. Received %s');
        /**
         * Strip spaces to perform a small amount of normalisation regardless
         * as the number is used for cache key generation so spaces can be ignored
         */
        $number = trim(str_replace(' ', '', $number));
        $filter = $this->getFilter();
        $filter->setCountry($country);
        return $filter->filter($number);
    }

    /**
     * @return E164
     */
    public function getFilter()
    {
        if (!$this->e164) {
            $this->e164 = new E164;
        }

        return $this->e164;
    }



}
