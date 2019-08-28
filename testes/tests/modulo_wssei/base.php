<?php

require_once __DIR__ . '/TesteUtils.php';


class UserAgentTest extends PHPUnit_Framework_TestCase
{
    protected $http;
    protected $token;

    public function setUp(){

        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://sei3.nuvem.gov.br/sei/modulos/mod-wssei/controlador_ws.php/api/v1/']);
        $this->auth();
    }

    public function tearDown() {
        $this->http = null;
    }

    public function auth(){

        $t = TesteUtils::obterToken($this->http);
        $this->token = $t;        
    }


    public function criarProcDocInterno(){

        $r = Array('Procedimento' => '', 'Documento' => '');

        $p = $this->criarProcesso();
        if($p){
            $r['Procedimento'] = $p->{'IdProcedimento'};

            $d = $this->criarDocumentoInterno($r['Procedimento'], 'Doc1');
            if($d) $r['Documento'] = $d->{'IdDocumento'};
        }
        
        return $r;
    }

    protected function criarProcesso(){
        
        $c = [
                'tipoProcesso' => '100000349',
                'especificacao' => 'descricao teste',                    
                'observacoes' => 'observacao teste',
                'nivelAcesso' => '1',
                'hipoteseLegal' => '1',
                'grauSigilo' => '',
                'assuntos' => '[{"id": 79}]'
            ];
        $c = TesteUtils::montar_cabecalho_geral($this->token, $c);
        
        try{
            $r = $this->http->request('POST', 'processo/criar', $c);            
            $r = json_decode($r->getBody());
            $r = $r->{"data"};
            
        }catch(Exception $e){
            print_r($e);
            $r = array();
        }        
        return $r;
    }

    protected function criarDocumentoInterno($idProcedimento, $desc){

        $c = [
                'processo' => $idProcedimento,
                'tipoDocumento' => '189',
                'descricao' => $desc,
                'nivelAcesso' => '0',
                'assuntos' => '[{"id": 79}]',
                'observacao' => 'Observacao teste'                    
            ];
        $c = TesteUtils::montar_cabecalho_geral($this->token, $c);        
        
        try{
            $r = $this->http->request('POST', 'documento/interno/criar', $c);
            //echo $r->getBody(); die;
            $r = json_decode($r->getBody());
            $r = $r->{"data"};
            
        }catch(Exception $e){
            print_r($e);
            $r = array();
        }        
        return $r;
        
    }

    protected function criarDocumentoExterno($idProcedimento, $desc){

       $c = [
                'processo' => $idProcedimento,
                'tipoDocumento' => '46',
                'descricao' => $desc,
                'nivelAcesso' => '0',
                'assuntos' => '[{"id": 79}]',
                'observacao' => 'Observacao teste',
                'nomeArquivo' => 'teste.html',
                'conteudoDocumento' => '<html><body><font color=red>Anexo HTML de teste</font></body></html>'
            ];
        $c = TesteUtils::montar_cabecalho_geral($this->token, $c); 
        
        try{
            $r = $this->http->request('POST', 'documento/externo/criar', $c);            
            $r = json_decode($r->getBody());
            $r = $r->{"data"};
            
        }catch(Exception $e){
            print_r($e);
            $r = array();
        }        
        return $r;
        
    }
    
    protected function listarProcedimentos($idProcedimento){
        
        $c = TesteUtils::montar_cabecalho_geral($this->token, []);
        
        try{
            $r = $this->http->request('GET', 'documento/listar/' . $idProcedimento, $c);
            $r = $r->getBody()->getContents();

        }catch(Exception $e){
            print_r($e);
            $r = '';
        }        
        return $r;
    }

    protected function AssinarDocumentoInterno($idDocumento){

        $c = [
                'orgao' => '0',
                'documento' => $idDocumento,
                'cargo' => 'Chefe de Gabinete da Presidência',
                'login' => 'teste',
                'senha' => 'teste',
                'usuario' => '100000001',                    
            ];
        $c = TesteUtils::montar_cabecalho_geral($this->token, $c);
        
        try{
            $r = $this->http->request('POST', 'documento/assinar', $c);
            $r = $r->getBody()->getContents();

        }catch(Exception $e){
            print_r($e);
            $r = '';
        }        
        return $r;
        
    }
    
}


