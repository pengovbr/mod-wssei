<?php

require_once __DIR__ . '/base.php';


class TestWssei_Cenario001 extends UserAgentTest
{
    public function testAutenticar()
    {
        $t = $this->token;
        $this->assertNotEmpty($t);
    }

    
}