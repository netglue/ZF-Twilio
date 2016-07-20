<?php

namespace NetglueTwilioTest\Factory;
use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;

class TwilioClientFactoryTest extends AbstractControllerTestCase
{

    public function setUp()
    {
        $this->setUseConsoleRequest(true);
        $this->setApplicationConfig(include __DIR__ . '/../../config/app-config.php');
        parent::setUp();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Invalid or empty credentials for the Twilio Rest Client
     */
    public function testExceptionThrownWhenNoCredentials()
    {
        $services = $this->getApplicationServiceLocator();
        $client = $services->get('Twilio\Rest\Client');
        try {

            $this->fail('No runtime exception was thrown');
        } catch(\Exception $e) {
            $this->assertInstanceOf('RuntimeException', $e);
        }
    }

}
