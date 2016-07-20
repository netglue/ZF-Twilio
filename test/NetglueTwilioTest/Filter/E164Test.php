<?php

namespace NetglueTwilioTest\Filter;

use NetglueTwilio\Filter\E164;

class E164Test extends \PHPUnit_Framework_TestCase
{

    public function getNonNumbers()
    {
        return [
            ['foo'],
            [['foo' => 'bar']],
            [new \stdClass],
            [1],
        ];
    }

    public function getValidNumbers()
    {
        return [
            ['GB', '01234 567 890', '+441234567890'],
            ['CH', '021 653 81 37', '+41216538137'],
            [null, '+44 01234 567 890', '+441234567890'],
        ];
    }

    /**
     * @dataProvider getNonNumbers
     */
    public function testFilterReturnsUnfilteredValueForNonString($value)
    {
        $filter = new E164;
        $this->assertSame($value, $filter->filter($value));
    }

    public function testSetGetCountry()
    {
        $filter = new E164;
        $this->assertNull($filter->getCountry());
        $filter->setCountry('GB');
        $this->assertSame('GB', $filter->getCountry());
    }

    public function testCountryIsSetViaConstructorOptions()
    {
        $filter = new E164(['country' => 'GB']);
        $this->assertSame('GB', $filter->getCountry());
    }

    /**
     * @dataProvider getValidNumbers
     */
    public function testValidNumber($country = null, $input, $expect)
    {
        $filter = new E164(['country' => $country]);
        $this->assertSame($expect, $filter->filter($input));
    }
}
