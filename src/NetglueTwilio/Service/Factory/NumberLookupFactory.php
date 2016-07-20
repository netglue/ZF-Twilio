<?php

namespace NetglueTwilio\Service\Factory;

use Interop\Container\ContainerInterface;
use NetglueTwilio\Service\NumberLookup;

class NumberLookupFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @todo Implement cache injection from config
         */
        $client = $container->get('Twilio\Rest\Client');
        return new NumberLookup($client);
    }

}
