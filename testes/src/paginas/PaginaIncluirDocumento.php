<?php

use \utilphp\util;
use PHPUnit_Extensions_Selenium2TestCase_Keys as Keys;

class PaginaIncluirDocumento extends PaginaTeste
{
    const STA_NIVEL_ACESSO_PUBLICO = 1;
    const STA_NIVEL_ACESSO_RESTRITO = 2;
    const STA_NIVEL_ACESSO_SIGILOSO = 3;

    const STA_FORMATO_NATO_DIGITAL = 1;

	public function __construct($test)
    {
        parent::__construct($test);
        $test->frame(NULL);
        $test->frame("ifrVisualizacao");
    }

    public function selecionarTipoDocumento($tipoDocumento)
    {
        try{
            $this->test->byId('txtFiltro')->value($tipoDocumento);
            sleep(2);
            $this->test->byLinkText($tipoDocumento)->click();
        }
        catch (Exception $e){
            $this->test->byId("imgExibirSeries")->click();
            $this->test->byId('txtFiltro')->value($tipoDocumento);
            sleep(2);
            $this->test->byLinkText($tipoDocumento)->click();
        }
    }

    public function selecionarTipoDocumentoExterno()
    {
        $this->selecionarTipoDocumento('Externo');
    }

    public function descricao($value)
    {
    	$input = $this->test->byId("txtDescricao");
        return $input->value($value);
    }

    public function tipoDocumento($value)
    {                
        $input = $this->test->byId("selSerie");        
        $this->test->select($input)->selectOptionByLabel($value);
        //TODO: Necessário corrigir retorno do função
        //return $this->test->select($input)->selectedLabel();
    }

    public function formato($value)
    {
        if($value != self::STA_FORMATO_NATO_DIGITAL)
            throw new Exception("Outros formatos não implementados em PaginaIncluirDocumento");
            
        $this->test->byId("divOptNato")->click();
    }

    #return $this->test->byXPath("//table[@id='tblAnexos']//tr[2]/td[2]")->text();
    public function anexo($arquivo)
    {   
        $input = $this->test->byId("filArquivo"); 
        $input->value($arquivo);
        $this->test->waitUntil(function($testCase) use($arquivo) {
            $testCase->assertContains(basename($arquivo), $testCase->byCssSelector('body')->text());
            return true;
        }, PEN_WAIT_TIMEOUT);
    }

    public function dataElaboracao($value)
    {
        $input = $this->test->byId("txtDataElaboracao");
        return $input->value($value);
    }

    public function observacoes($value)
    {
        $input = $this->test->byId("txaObservacoes");
        return $input->value($value);
    }

    public function adicionarInteressado($nomeInteressado)
    {
		$input = $this->test->byId("txtInteressado");
		$input->value($nomeInteressado);
		$this->test->keys(Keys::ENTER);
		$this->test->acceptAlert();

		sleep(2);
    }

    public function salvarDocumento()
    {
		$this->test->byId("btnSalvar")->click();
    }

    public function selecionarRestricao($staNivelRestricao, $strHipoteseLegal = '', $strGrauSigilo = '')
    {
    	if(isset($staNivelRestricao)) {
	    	if($staNivelRestricao === self::STA_NIVEL_ACESSO_PUBLICO) {
				$input = $this->test->byId("optPublico")->click();
	    	}
	    	else if($staNivelRestricao === self::STA_NIVEL_ACESSO_RESTRITO) {
				$input = $this->test->byId("optRestrito")->click();
				$select = $this->test->select($this->test->byId('selHipoteseLegal'));
				$select->selectOptionByLabel($strHipoteseLegal);
	    	}
	    	else if($staNivelRestricao === self::STA_NIVEL_ACESSO_SIGILOSO) {
				$input = $this->test->byId("optSigiloso")->click();
				$select = $this->test->select($this->test->byId('selHipoteseLegal'));
				$select->selectOptionByLabel($strHipoteseLegal);
				$select = $this->test->select($this->test->byId('selGrauSigilo'));
				$select->selectOptionByLabel($strGrauSigilo);
	    	}
    	}
    }

    public function gerarDocumentoTeste(array $dadosDocumento = null)
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
        $this->test->byXPath("//img[@alt='Incluir Documento']")->click();
        sleep(2);

        $dadosDocumento = $dadosDocumento ?: array();
        $dadosDocumento["TIPO_DOCUMENTO"] = @$dadosDocumento["TIPO_DOCUMENTO"] ?: "Ofício";
        $dadosDocumento["DESCRICAO"] = @$dadosDocumento["DESCRICAO"] ?: util::random_string(20);
        $dadosDocumento["OBSERVACOES"] = @$dadosDocumento["OBSERVACOES"] ?: util::random_string(100);
        $dadosDocumento["INTERESSADOS"] = @$dadosDocumento["INTERESSADOS"] ?: util::random_string(40);
        $dadosDocumento["RESTRICAO"] = @$dadosDocumento["RESTRICAO"] ?: PaginaIncluirDocumento::STA_NIVEL_ACESSO_PUBLICO;
        $dadosDocumento["HIPOTESE_LEGAL"] = @$dadosDocumento["HIPOTESE_LEGAL"] ?: "";

        //$paginaIncluirDocumento = new PaginaIncluirDocumento($test);
        $this->selecionarTipoDocumento($dadosDocumento["TIPO_DOCUMENTO"]);
        $this->descricao($dadosDocumento["DESCRICAO"]);
        $this->observacoes($dadosDocumento["OBSERVACOES"]);
        $this->selecionarRestricao($dadosDocumento["RESTRICAO"], $dadosDocumento["HIPOTESE_LEGAL"]);
        $this->salvarDocumento();

        $url = parse_url($this->test->byId("ifrArvoreHtml")->attribute("src"));
        parse_str($url['query'], $query);
        $dadosDocumento["ID_DOCUMENTO"] = $query["id_documento"];
        
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");                
        $this->test->window($this->test->windowHandles()[1]);        
        $this->test->closeWindow();
        $this->test->window('');

        $this->test->frame(NULL);
        $this->test->frame("ifrArvore");
        
        return trim($this->test->byId('anchor' . $query["id_documento"])->text());
    }    

    public function gerarDocumentoExternoTeste(array $dadosDocumento)
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
        $this->test->byXPath("//img[@alt='Incluir Documento']")->click();
        sleep(2);

        $dadosDocumento = $dadosDocumento ?: array();
        $dadosDocumento["TIPO_DOCUMENTO"] = @$dadosDocumento["TIPO_DOCUMENTO"] ?: "Ofício";
        $dadosDocumento["DESCRICAO"] = @$dadosDocumento["DESCRICAO"] ?: util::random_string(20);
        $dadosDocumento["DATA_ELABORACAO"] = @$dadosDocumento["DATA_ELABORACAO"] ?: date("d/m/Y");
        $dadosDocumento["FORMATO_DOCUMENTO"] = @$dadosDocumento["FORMATO_DOCUMENTO"] ?: self::STA_FORMATO_NATO_DIGITAL;        
        $dadosDocumento["OBSERVACOES"] = @$dadosDocumento["OBSERVACOES"] ?: util::random_string(100);
        $dadosDocumento["INTERESSADOS"] = @$dadosDocumento["INTERESSADOS"] ?: util::random_string(40);
        $dadosDocumento["RESTRICAO"] = @$dadosDocumento["RESTRICAO"] ?: PaginaIncluirDocumento::STA_NIVEL_ACESSO_PUBLICO;
        $dadosDocumento["HIPOTESE_LEGAL"] = @$dadosDocumento["HIPOTESE_LEGAL"] ?: "";

        $this->selecionarTipoDocumentoExterno();
        sleep(2);
        $this->tipoDocumento($dadosDocumento["TIPO_DOCUMENTO"]);
        sleep(2);        
        
        $this->dataElaboracao($dadosDocumento["DATA_ELABORACAO"]);
        $this->formato($dadosDocumento["FORMATO_DOCUMENTO"]);
        $this->anexo($dadosDocumento["ARQUIVO"]);
        $this->observacoes($dadosDocumento["OBSERVACOES"]);
        $this->selecionarRestricao($dadosDocumento["RESTRICAO"], $dadosDocumento["HIPOTESE_LEGAL"]);
        $this->salvarDocumento();

        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
    }        
}