<?php

namespace NetglueTwilio;

use Zend\ServiceManager\Factory\InvokableFactory;
use Twilio\Rest\Client as TwilioClient;
use NetglueTwilio\Service;

return [

    'twilio' => [
        // Your Account SID from https://www.twilio.com/console
        'sid' => null,
        // Your Auth Token from https://www.twilio.com/console
        'token' => null,
    ],

    'service_manager' => [
        'factories' => [
            TwilioClient::class => Factory\TwilioClientFactory::class,
            Service\NumberLookup::class => Service\Factory\NumberLookupFactory::class,
        ],
    ],

];

