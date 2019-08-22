<?php

function obterToken($http){

    $t = $GLOBALS['token'];

    if(!$t){

        echo 'autenticar';
        $t = autenticar($http);
        $GLOBALS['token'] = $t;

    }else{

        echo 'autenticado';

    }

    return $GLOBALS['token'];
}

function autenticar($http, $user='teste', $pass='teste'){

    $p = ['form_params' => ['usuario' => $user, 'senha' => $pass]];
    $body = $http->request('POST', 'autenticar', $p);
    $r='';

    try{
        
        $r = json_decode($body->getBody())->{"data"}->{"token"};

    } catch(Exception $e){
        $r = '';
    }

    return $r;
}


class UserAgentTest extends PHPUnit_Framework_TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://org4.sei-ci.seges.intra.planejamento/sei/modulos/mod-wssei/controlador_ws.php/api/v1/']);
    }

    public function tearDown() {
        $this->http = null;
    }

    public function testAutenticar()
    {
        $t = obterToken($this->http);
        $this->assertNotEmpty($t);
    }

    public function testVersion()
    {
        
    }

    public function atestaGet2()
        {
            $response = $this->http->request('GET', 'user-agent');

            $this->assertEquals(200, $response->getStatusCode());

            $contentType = $response->getHeaders()["Content-Type"][0];
            $this->assertEquals("application/json", $contentType);

            $userAgent = json_decode($response->getBody())->{"user-agent"};
            $this->assertRegexp('/Guzazle/', $userAgent);
        }

}