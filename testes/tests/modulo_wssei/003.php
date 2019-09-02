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

    protected function acriarProcesso(){
        
        $h = ['token' => $this->token];
 
        $b = [
                'form_params' => [
                    'tipoProcesso' => '100000349',
                    'especificacao' => 'descricao teste',                    
                    'observacoes' => 'observacao teste',
                    'nivelAcesso' => '1',
                    'hipoteseLegal' => '1',
                    'grauSigilo' => '',
                    'assuntos' => '[{"id": 79}]'
                ],
                'headers' => $h
            ];
        
        $r=array();
        try{
            $r = $this->http->request('POST', 'processo/criar', $b);
            
            $r = json_decode($r->getBody());            
            $r = $r->{"data"};
            
        }catch(Exception $e){
            print_r($e);
            $r = array();
        }        
        return $r;
    }

    private function criarDocumentoInterno($idProcedimento, $desc){
            //listar procedimento
            $r = $this->listarProcedimentos($p);
            $this->assertContains('"total":"2"', $r);
        
    }
        
}