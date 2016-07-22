<?php

namespace NetglueTwilio\Form\Element;

use Zend\Form\Element\Select as SelectElement;
use League\ISO3166\ISO3166;
use libphonenumber\PhoneNumberUtil;
use Locale;

class DialingCode extends SelectElement
{

    /**
     * Default values in memory to save some work
     * @var array
     */
    private $defaultValues;

    /**
     * Reference to phone number util so we don't have to continually call getInstance
     * @var PhoneNumberUtil
     */
    private $numberUtil;

    /**
     * Reference to ISO3166
     * @var ISO3166
     */
    private $countries;


    public function __construct($name = null, $options = [])
    {
        $this->numberUtil = PhoneNumberUtil::getInstance();
        $this->countries = new ISO3166;
        $this->setValueOptions($this->getDefaultValues());
        parent::__construct($name, $options);
    }

    /**
     * Return an array of option values for all known countries
     * @return array
     */
    public function getDefaultValues()
    {
        if (is_array($this->defaultValues)) {
            return $this->defaultValues;
        }
        $countries = $this->countries->getAll();
        $this->defaultValues = [];
        array_walk($countries, function($country) {
            $dial = $this->numberUtil->getCountryCodeForRegion($country['alpha2']);
            $name = Locale::getDisplayRegion(sprintf('-%s', $country['alpha2']));
            $this->defaultValues[$country['alpha2']] = [
                'label' => sprintf('+%d', $dial),
                'value' => $dial,
                'attributes' => [
                    'data-country-alpha2' => $country['alpha2'],
                    'data-country-alpha3' => $country['alpha3'],
                    'data-country-number' => $country['numeric'],
                    'data-country-name'   => $name,
                ],
            ];
        });
        return $this->defaultValues;
    }


}
