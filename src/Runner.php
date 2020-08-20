<?php

namespace App;

require_once('vendor/autoload.php');//composer vendor
use GuzzleHttp\Exception\GuzzleException;

class Runner
{
    public static function apitest()
    {
        $test = new TestApi();
        try {
            $test->generateJWT();
            $test->run();
        } catch (GuzzleException $e) {
        }
    }
}