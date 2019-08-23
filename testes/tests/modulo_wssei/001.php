<?php

require_once __DIR__ . '/TesteUtils.php';

class UserAgentTest extends PHPUnit_Framework_TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://sei3.nuvem.gov.br/sei/modulos/mod-wssei/controlador_ws.php/api/v1/']);
    }

    public function tearDown() {
        $this->http = null;
    }

    public function testAutenticar()
    {
        $t = obterToken($this->http);
        $this->assertNotEmpty($t);
    }

    public function atestVersion()
    {
        
    }

}