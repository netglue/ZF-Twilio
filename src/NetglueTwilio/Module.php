<?php

namespace NetglueTwilio;

class Module
{

    public function getConfig()
    {
        return require_once __DIR__ . '/../../config/module.config.php';
    }

}
