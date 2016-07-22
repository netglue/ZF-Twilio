<?php

namespace NetglueTwilio\Service;

use Twilio\Rest\Client as TwilioClient;
use Zend\Cache\Storage\StorageInterface as Cache;
use NetglueTwilio\Filter\E164;
use Webmozart\Assert\Assert;
use Twilio\Exceptions\TwilioException;

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

    /**
     * @var Cache|null
     */
    private $cache;

    /**
     * @param TwilioClient $client
     * @param Cache $cache
     */
    public function __construct(TwilioClient $client, Cache $cache = null)
    {
        $this->client = $client;
        if ($cache) {
            $this->setCache($cache);
        }
    }

    /**
     * @param Cache $cache
     * @return void
     */
    public function setCache(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * @return Cache|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Use the twilio api to determine whether a number is theoretically valid
     *
     * @param string $number
     * @param string $countryCode ISO 2 letter country code
     * @return array|false
     */
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
        if ($this->cache) {
            $this->cache->setItem($cacheKey, $result);
        }

        return $result;
    }

    /**
     * Perform the actual lookup at the Twilio API
     * @param string $number
     * @param string $countryCode
     * @return array|false
     */
    private function apiLookup($number, $countryCode)
    {
        try {
            // Assuming $instance, it will have these props:
            // countryCode, phoneNumber, nationalFormat, carrier, addOns
            $instance = $this->client->lookups->v1->phoneNumbers($number)->fetch([
                'countryCode' => $countryCode
            ]);
            return [
                'countryCode' => $instance->countryCode,
                'phoneNumber' => $instance->phoneNumber,
                'nationalFormat' => $instance->nationalFormat,
                'carrier' => $instance->carrier,
                'addOns' => $instance->addOns,
            ];
        } catch(TwilioException $e) {
            /**
             * It appears that a 404, i.e. an invalid number, will throw this
             * sort of exception. There's no documentation…
             */
            return false;
        }
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
    public function normaliseNumber($number, $country)
    {
        Assert::scalar($number, 'Phone numbers must be scalar values in order to be normalised. Received %s');
        Assert::nullOrString($country, 'Country code should be a string, received %s');
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
