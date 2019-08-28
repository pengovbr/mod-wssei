<?php

require_once __DIR__ . '/base.php';


class TestWssei_Cenario002 extends UserAgentTest
{
    
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