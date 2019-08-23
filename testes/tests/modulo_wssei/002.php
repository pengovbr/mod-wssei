<?php

require_once __DIR__ . '/TesteUtils.php';


class TestWssei_Cenario002 extends PHPUnit_Framework_TestCase
{
    private $http;
    private $token;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://sei3.nuvem.gov.br/sei/modulos/mod-wssei/controlador_ws.php/api/v1/']);

        $this->token = obterToken($this->http);
        //caso n esteja autenticado já finaliza

    }

    public function tearDown() {
        $this->http = null;
    }

    
    public function testVersion()
    {
        $h = ['token' => $this->token];
        $b = $this->http->request('GET', 'versao', $h);
        try{
            $b = json_decode($b->getBody());
            $b = $b->{"data"}->{"versao"};
        }catch(Exception $e){
            $b = '';
        }
        
        $this->assertNotEmpty($b);       

    }
    
}