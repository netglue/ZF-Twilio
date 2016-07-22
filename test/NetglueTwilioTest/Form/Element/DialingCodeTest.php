<?php

namespace NetglueTwilioTest\Form\Element;

use NetglueTwilio\Form\Element\DialingCode;
use Locale;
class DialingCodeTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDefaultValues()
    {
        $select = new DialingCode;
        $defaults = $select->getDefaultValues();
        $this->assertInternalType('array', $defaults);
        $this->assertArrayHasKey('GB', $defaults);
        $gb = $defaults['GB'];
        $this->assertArrayHasKey('value', $gb);
        $this->assertSame(44, $gb['value']);
        $this->assertArrayHasKey('label', $gb);
        $this->assertSame('United Kingdom', $gb['attributes']['data-country-name']);
    }

    public function testCountryNamesWithDefaultLocale()
    {
        $default = Locale::getDefault();
        Locale::setDefault('FR');
        $select = new DialingCode;
        $defaults = $select->getDefaultValues();
        Locale::setDefault($default);
        $gb = $defaults['GB'];
        $this->assertSame('Royaume-Uni', $gb['attributes']['data-country-name']);
    }

}
