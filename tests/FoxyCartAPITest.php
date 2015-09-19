<?php

use Pel\Helper\FoxyCartAPI;

class FoxyCartAPITest extends PHPUnit_Framework_TestCase
{
    protected static function newFoxyCartAPI(
        $endpoint_url = 'endpoint_url',
        $api_token = 'api_token',
        $curl_options = array()
    ) {
        return new FoxyCartAPI(
            $endpoint_url,
            $api_token,
            $curl_options
        );
    }

    protected static function callMethod($name, array $args, $obj = null)
    {
        if ($obj === null) {
            $obj = static::newFoxyCartAPI();
        }

        $class = new ReflectionClass('Pel\Helper\FoxyCartAPI');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /*************************************************************
     * parseParams tests
     *************************************************************/

    public function testParseParamsNonRecursive()
    {
        $params = array(
            'param1' => 60,
            'param2' => 'string'
        );
        $qs = static::callMethod('parseParams', array($params));
        $qs_expected = "param1=60&param2=string";
        $this->assertTrue($qs == $qs_expected);
    }

    public function testParseParamsRecursive()
    {
        $params = array(
            'param1' => 60,
            'param2' => 'string',
            'paramRecursive1' => array(
                'param3' => 50,
                'paramRecursive2' => array(
                    'param4' => 'string',
                    'param5' => 40
                )
            )
        );
        $qs = static::callMethod('parseParams', array($params));
        $qs_expected = "param1=60&param2=string&param3=50&param4=string&param5=40";
        $this->assertTrue($qs == $qs_expected);
    }
}
