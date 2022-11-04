<?php

require_once __DIR__ . '/base.php';


class TestWssei_Cenario003 extends UserAgentTest
{

    public function testCriarProcessos()
    {

        $ps = Array();
        for ($i=0; $i < 1; $i++) {
            $p = $this->criarProcesso();
            if($p) $ps[] = $p;
        }

        foreach ($ps as $v) {
            

            $p = $v->{'IdProcedimento'};
            $this->assertNotEmpty($p);
            
            $r='';
            if($p){
                $r=$this->criarDocumentoInterno($p, 'Doc1');
                $r = $r->{'IdDocumento'};                
            }
            $this->assertNotEmpty($r);
            if($p){
                $r=$this->criarDocumentoExterno($p, 'Doc2');
                $r = $r->{'IdDocumento'};                
            }
            $this->assertNotEmpty($r);            
        }

    }
        
}