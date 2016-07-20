<?php

namespace NetglueTwilio\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Twilio\Rest\Client;
use Twilio\Exceptions\ConfigurationException;

class TwilioClientFactory // implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        /**
         * Currently, no way of overriding HTTP Client,
         * What's the point anyhow when the Twilio libs use their own interface to typehint with?
         */

        $config = $container->get('Config');
        $config = isset($config['twilio']) ? $config['twilio'] : [];

        /**
         * Don't bother checking credential config.
         * This is dealt with by Client::__construct, furthermore, credentials
         * could be stored in ENV vars
         */
        try {
            return new Client($config['sid'], $config['token']);
        } catch(ConfigurationException $e) {
            throw new \RuntimeException('Invalid or empty credentials for the Twilio Rest Client', null, $e);
        }
    }

}
