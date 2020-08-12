<?php

require_once __DIR__ . '/base.php';


class TestWssei_Cenario004 extends UserAgentTest
{
    public function testAssinar()
    {
        
        $r = $this->criarProcDocInterno();
        $r = $this->AssinarDocumentoInterno($r['Documento']);
        $this->assertContains('sucesso', $r);

    }

}