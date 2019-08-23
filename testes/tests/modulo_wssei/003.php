<?php

require_once __DIR__ . '/TesteUtils.php';


class TestWssei_Cenario003 extends PHPUnit_Framework_TestCase
{
    private $http;
    private $token;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://sei3.nuvem.gov.br/sei/modulos/mod-wssei/controlador_ws.php/api/v1/']);

        $this->token = TesteUtils::obterToken($this->http);
        //caso n esteja autenticado já finaliza

    }

    public function tearDown() {
        $this->http = null;
    }


    public function testCriarProcessos()
    {
        $ps = Array();
        for ($i=0; $i < 5; $i++) { 
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

    private function criarProcesso(){
        
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

        $h = ['token' => $this->token];
 
        $b = [
                'form_params' => [
                    'processo' => $idProcedimento,
                    'tipoDocumento' => '189',
                    'descricao' => $desc,
                    'nivelAcesso' => '0',
                    'assuntos' => '[{"id": 79}]',
                    'observacao' => 'Observacao teste',
                    
                ],
                'headers' => $h
            ];
        
        $r=array();
        try{
            $r = $this->http->request('POST', 'documento/interno/criar', $b);
            $r = json_decode($r->getBody());
            $r = $r->{"data"};
            
        }catch(Exception $e){
            print_r($e);
            $r = array();
        }        
        return $r;
        
    }

    private function criarDocumentoExterno($idProcedimento, $desc){

        $h = ['token' => $this->token];
 

        $b = [
                'form_params' => [
                    'processo' => $idProcedimento,
                    'tipoDocumento' => '46',
                    'descricao' => $desc,
                    'nivelAcesso' => '0',
                    'assuntos' => '[{"id": 79}]',
                    'observacao' => 'Observacao teste',
                    'nomeArquivo' => 'teste.html',
                    'conteudoDocumento' => '<html><body><font color=red>Anexo HTML de teste</font></body></html>'
                ],
                'headers' => $h,
                
            ];
        
        $r=array();
        try{
            $r = $this->http->request('POST', 'documento/externo/criar', $b);            
            $r = json_decode($r->getBody());
            $r = $r->{"data"};
            
        }catch(Exception $e){
            print_r($e);
            $r = array();
        }        
        return $r;
        
    }
    
}