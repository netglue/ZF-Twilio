<?php

namespace NetglueTwilioTest\Service;
use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use NetglueTwilio\Service\NumberLookup;
use Twilio\Tests\HolodeckTestCase;
use Twilio\Http\Response;
use Zend\Cache\Storage\Adapter\Memory as MemoryCache;

class NumberLookupTest extends HolodeckTestCase
{

    public function testConstruct()
    {
        $service = new NumberLookup($this->twilio);
        $this->assertNull($service->getCache());

        $cache = new MemoryCache;
        $service = new NumberLookup($this->twilio, $cache);
        $this->assertSame($cache, $service->getCache());
    }

    public function testGetFilterReturnsFilter()
    {
        $service = new NumberLookup($this->twilio);
        $filter = $service->getFilter();
        $this->assertInstanceOf('Zend\Filter\FilterInterface', $filter);
        $this->assertSame($filter, $service->getFilter());
    }

    public function testSetGetCache()
    {
        $service = new NumberLookup($this->twilio);
        $this->assertNull($service->getCache());
        $cache = new MemoryCache;
        $service->setCache($cache);
        $this->assertSame($cache, $service->getCache());
    }

    public function getNormaliseData()
    {
        return [
            [ '01234567890', 'GB', '+441234567890' ],
            [ 'abc def ghi', null, 'abcdefghi' ],
        ];
    }

    /**
     * @dataProvider getNormaliseData
     */
    public function testNormaliseNumber($number, $country, $expect)
    {
        $service = new NumberLookup($this->twilio);
        $this->assertSame($expect, $service->normaliseNumber($number, $country));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Phone numbers must be scalar
     */
    public function testNormaliseThrowsExceptionNonScalarNumber()
    {
        $service = new NumberLookup($this->twilio);
        $service->lookup(null);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Country code should be a string
     */
    public function testNormaliseThrowsExceptionCountryNotString()
    {
        $service = new NumberLookup($this->twilio);
        $service->lookup('01234567890', 1);
    }

    public function testLookupReturnsCachedResult()
    {
        $data = 'The Result';
        $number = '+441234567890';
        $key = md5($number);
        $cache = new MemoryCache;
        $cache->setItem($key, $data);
        $service = new NumberLookup($this->twilio, $cache);

        $result = $service->lookup($number);
        $this->assertSame($data, $result);
    }

    public function testLookupCachesResult()
    {
        $cache = new MemoryCache;
        $service = new NumberLookup($this->twilio, $cache);
        $number = '+441234567890';
        $key = md5($number);
        $this->assertFalse($cache->hasItem($key));
        $result = $service->lookup($number);
        $this->assertFalse($result);
        $this->assertTrue($cache->hasItem($key));
    }

    public function testValidLookupReturnsArray()
    {
        $this->holodeck->mock(new Response(
            200,
            '
            {
                "carrier": {
                    "error_code": null,
                    "mobile_country_code": "310",
                    "mobile_network_code": "456",
                    "name": "verizon",
                    "type": "mobile"
                },
                "country_code": "US",
                "national_format": "(510) 867-5309",
                "phone_number": "+15108675309",
                "add_ons": {
                    "status": "successful",
                    "message": null,
                    "code": null,
                    "results": {}
                },
                "url": "https://lookups.twilio.com/v1/PhoneNumbers/phone_number"
            }
            '
        ));
        $cache = new MemoryCache;
        $service = new NumberLookup($this->twilio, $cache);
        $number = '+441234567890';
        $key = md5($number);
        $result = $service->lookup($number);
        $this->assertInternalType('array', $result);
        $this->assertSame('US', $result['countryCode']);
        $this->assertSame('+15108675309', $result['phoneNumber']);
        $this->assertSame('(510) 867-5309', $result['nationalFormat']);
        $this->assertNotNull($result['carrier']);
        $this->assertNotNull($result['addOns']);
    }

}
