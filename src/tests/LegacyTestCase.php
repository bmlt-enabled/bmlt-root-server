<?php

namespace Tests;

abstract class LegacyTestCase extends TestCase
{
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $_SERVER['REQUEST_URI'] = $uri;
        try {
            return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
        } finally {
            unset($_SERVER['REQUEST_URI']);
        }
    }
}
