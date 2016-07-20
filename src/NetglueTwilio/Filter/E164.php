<?php

namespace NetglueTwilio\Filter;

use Zend\Filter\AbstractFilter;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;

class E164 extends AbstractFilter
{

    protected $options = [
        'country' => null,
    ];

    /**
     * Sets filter options
     *
     * @param array|\Traversable|null $options
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

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
     * Attempt to filter the given string, hopefully a phone number, to produce a number in E.164 format
     *
     * If for any reason, the value cannot be filtered/formatted, the original value will be returned
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }
        $filtered = (string) $value;
        $util = PhoneNumberUtil::getInstance();
        try {
            $number = $util->parse($filtered, $this->getCountry());
            return $util->format($number, PhoneNumberFormat::E164);
        } catch(NumberParseException $e) {
            return $value;
        }
    }

}
