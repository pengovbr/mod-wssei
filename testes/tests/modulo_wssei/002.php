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



class TestWssei_Cenario002 extends PHPUnit_Framework_TestCase
{
    private $http;
    private $token;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://org4.sei-ci.seges.intra.planejamento/sei/modulos/mod-wssei/controlador_ws.php/api/v1/']);

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