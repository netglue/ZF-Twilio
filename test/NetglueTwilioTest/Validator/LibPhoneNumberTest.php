<?php

namespace NetglueTwilioTest\Validator;

use NetglueTwilio\Validator\LibPhoneNumber;

class LibPhoneNumberTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetCountryCode()
    {
        $v = new LibPhoneNumber;
        $this->assertNull($v->getCountry());
        $v->setCountry('GB');
        $this->assertSame('GB', $v->getCountry());
    }

    public function testCountryIsSetViaConstructorOptions()
    {
        $v = new LibPhoneNumber(['country' => 'GB']);
        $this->assertSame('GB', $v->getCountry());
    }

    public function testCountryCodeValidationFailure()
    {
        $v = new LibPhoneNumber;
        $this->assertFalse($v->isValid('01395268367'));
        $messages = $v->getMessages();
        $this->assertArrayHasKey(LibPhoneNumber::ERROR_COUNTRY_CODE, $messages);
    }

    public function testUnparseableNumberFailure()
    {
        $v = new LibPhoneNumber;
        $this->assertFalse($v->isValid('FOO'));
        $messages = $v->getMessages();
        $this->assertArrayHasKey(LibPhoneNumber::ERROR_UNRECOGNIZED, $messages);
    }

    public function testTooShortFailureWithCountry()
    {
        $v = new LibPhoneNumber;
        $this->assertFalse($v->isValid('+44 1'));
        $messages = $v->getMessages();
        $this->assertArrayHasKey(LibPhoneNumber::ERROR_TOO_SHORT, $messages);
    }

    public function testTooLongFailureWithCountry()
    {
        $v = new LibPhoneNumber;
        $this->assertFalse($v->isValid('+44 11234567890123123456'));
        $messages = $v->getMessages();
        $this->assertArrayHasKey(LibPhoneNumber::ERROR_TOO_LONG, $messages);
    }

    public function testPhoneNumberInstanceAvailableAfterValidation()
    {
        $v = new LibPhoneNumber;
        $this->assertNull($v->getPhoneNumber());
        $v->isValid('+441395268367');
        $this->assertInstanceOf('libphonenumber\PhoneNumber', $v->getPhoneNumber());
    }

    public function testPhoneNumberInstancesCanBeValidated()
    {
        $v = new LibPhoneNumber;
        $this->assertFalse($v->isValid(1));
        $messages = $v->getMessages();
        $this->assertArrayHasKey(LibPhoneNumber::ERROR_NOT_STRING, $messages);

        $v = new LibPhoneNumber(['country' => 'GB']);
        $this->assertTrue($v->isValid('01234 567 890'));
        $number = $v->getPhoneNumber();

        $v = new LibPhoneNumber;
        $this->assertTrue($v->isValid($number));
    }
}
