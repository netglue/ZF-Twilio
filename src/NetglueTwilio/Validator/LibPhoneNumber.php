<?php

namespace NetglueTwilio\Validator;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use Zend\Validator\AbstractValidator;

class LibPhoneNumber extends AbstractValidator
{

    const ERROR_NOT_STRING   = 'errorNotString';
    const ERROR_UNRECOGNIZED = 'errorUnrecognized';
    const ERROR_COUNTRY_CODE = 'errorCountryCode';
    const ERROR_TOO_SHORT    = 'errorTooShort';
    const ERROR_TOO_LONG     = 'errorTooLong';

    protected $options = [
        'country' => null,
    ];

    protected $messageTemplates = [
        self::ERROR_NOT_STRING   => 'The phone number should be a string',
        self::ERROR_UNRECOGNIZED => 'The phone number provided was not recognised as a phone number in any known format',
        self::ERROR_COUNTRY_CODE => 'Please provide a country dialing prefix, i.e. +44',
        self::ERROR_TOO_SHORT    => 'The number provided is too short to be a valid phone number',
        self::ERROR_TOO_LONG     => 'The number provided is too long to be a valid phone number',
    ];

    protected $messageVariables = [
        'country' => ['options' => 'country'],
    ];

    /**
     * After validation, return the phone number proto if available
     * @var \libphonenumber\PhoneNumber|null
     */
    protected $phoneNumber;

    /**
     * @param string $code
     * @return self
     */
    public function setCountry($code)
    {
        $this->options['country'] = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry()
    {
        return $this->options['country'];
    }

    /**
     * After validation, return the phone number proto if available
     * @return \libphonenumber\PhoneNumber|null
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Whether the given number is valid according to libphonenumber
     * @param string|PhoneNumber $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if (!is_string($value) && ! $value instanceof PhoneNumber) {
            $this->error(self::ERROR_NOT_STRING);
            return false;
        }

        $phone = is_string($value) ? $this->getPhoneNumberProto($value) : $value;
        if (false === $phone) {
            return false;
        }

        /**
         * Provide access to the number for further inspection if desired
         */
        $this->phoneNumber = $phone;

        $util = PhoneNumberUtil::getInstance();
        return $util->isValidNumber($phone);
    }

    /**
     * @return libphonenumber\PhoneNumber|false
     */
    protected function getPhoneNumberProto($number)
    {
        try {
            $util = PhoneNumberUtil::getInstance();
            return $util->parse($number, $this->getCountry());
        } catch(NumberParseException $e) {
            switch ($e->getCode()) {

                /**
                 * Indicates that no default country code was set and could not
                 * be determined from the given number
                 */
                case NumberParseException::INVALID_COUNTRY_CODE:
                    $this->error(self::ERROR_COUNTRY_CODE);
                    return false;
                    break;

                /**
                 * Generally unparseable…
                 */
                default:
                case NumberParseException::NOT_A_NUMBER:
                    $this->error(self::ERROR_UNRECOGNIZED);
                    return false;
                    break;

                /**
                 * Not enough digits to be valid
                 */
                case NumberParseException::TOO_SHORT_AFTER_IDD:
                case NumberParseException::TOO_SHORT_NSN:
                    $this->error(self::ERROR_TOO_SHORT);
                    return false;
                    break;

                /**
                 * Too many digits
                 */
                case NumberParseException::TOO_LONG:
                    $this->error(self::ERROR_TOO_LONG);
                    return false;
                    break;

            }
        }
    }

}
