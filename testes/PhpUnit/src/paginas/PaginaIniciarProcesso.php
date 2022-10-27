<?php

use \utilphp\util;
use PHPUnit_Extensions_Selenium2TestCase_Keys as Keys;

class PaginaIniciarProcesso extends PaginaTeste
{
    const STA_NIVEL_ACESSO_PUBLICO  = 0;
    const STA_NIVEL_ACESSO_RESTRITO = 1;
    const STA_NIVEL_ACESSO_SIGILOSO = 2;

	public function __construct($test)
    {
        parent::__construct($test);
    }

    public function selecionarTipoProcesso($tipoProcesso)
    {
        try{
            $this->test->byId('txtFiltro')->value($tipoProcesso);
            sleep(ConfiguracaoSEI::getInstance()->getValor('WSSEI', 'Sleep', false, 2));
            $this->test->byLinkText($tipoProcesso)->click();
        }
        catch (Exception $e){
            $this->test->byId("imgExibirTiposProcedimento")->click();    
            $this->test->byId('txtFiltro')->value($tipoProcesso);
            sleep(ConfiguracaoSEI::getInstance()->getValor('WSSEI', 'Sleep', false, 2));
            $this->test->byLinkText($tipoProcesso)->click();
        }
    }

    public function descricao($value = null)
    {
    	$input = $this->test->byId("txtDescricao");
        if(isset($value)) $input->value($value);
        return $input->value();
    }

    public function observacoes($value = null)
    {
    	$input = $this->test->byId("txaObservacoes");
        if(isset($value)) $input->value($value);
        return $input->value();
    }

    public function selecionarProtocoloManual()
    {
    	$this->test->byId("optProtocoloManual")->click();
    }

    public function protocoloInformado($value = null)
    {
    	$input = $this->test->byId("txtProtocoloInformar");
        if(isset($value)) $input->value($value);
        return $input->value();
    }

    public function dataGeracaoProtocolo($value = null)
    {
    	$input = $this->test->byId("txtDtaGeracaoInformar");
        if(isset($value)) $input->value($value);
        return $input->value();
    }

    public function restricao($staNivelRestricao = null)
    {
        if(isset($staNivelRestricao))
        {
            if($staNivelRestricao === self::STA_NIVEL_ACESSO_PUBLICO) {
                $this->test->byId("optPublico")->click();
            }
            else if($staNivelRestricao === self::STA_NIVEL_ACESSO_RESTRITO) {               
                $this->test->byId("optRestrito")->click();
            }
            else if($staNivelRestricao === self::STA_NIVEL_ACESSO_SIGILOSO) {
                $this->test->byId("optSigiloso")->click();
            }
        }

        if($this->test->byId("optPublico")->selected())
            return self::STA_NIVEL_ACESSO_PUBLICO;
        else if($this->test->byId("optRestrito")->selected())
            return self::STA_NIVEL_ACESSO_RESTRITO; 
        else if($this->test->byId("optSigiloso")->selected())
            return self::STA_NIVEL_ACESSO_SIGILOSO;

    }

    public function adicionarInteressado($arrayNomeInteressado)
    {
        $arrayNomeInteressado = array($arrayNomeInteressado);

        if(isset($arrayNomeInteressado)){
            foreach ($arrayNomeInteressado as $nomeInteressado) {
                $input = $this->test->byId("txtInteressadoProcedimento");
                $input->value($nomeInteressado);
                $this->test->keys(Keys::ENTER);
                $this->test->acceptAlert();
                sleep(ConfiguracaoSEI::getInstance()->getValor('WSSEI', 'Sleep', false, 2));
            }
        }
    }

    public function listarInteressados() 
    {
        return  $this->test->select($this->test->byId('selInteressadosProcedimento'))->selectedLabels();
    }

    public function salvarProcesso()
    {
		$this->test->byId("btnSalvar")->click();
    }

    public function selecionarRestricao($staNivelRestricao, $strHipoteseLegal = '', $strGrauSigilo = '')
    {
    	if(isset($staNivelRestricao))
    	{
            $this->restricao($staNivelRestricao);
            
            if($staNivelRestricao === self::STA_NIVEL_ACESSO_RESTRITO)
	    	{
				$select = $this->test->select($this->test->byId('selHipoteseLegal'));
				$select->selectOptionByLabel($strHipoteseLegal);
	    	}
	    	else if($staNivelRestricao === self::STA_NIVEL_ACESSO_SIGILOSO)
	    	{
				$select = $this->test->select($this->test->byId('selHipoteseLegal'));
				$select->selectOptionByLabel($strHipoteseLegal);

				$select = $this->test->select($this->test->byId('selGrauSigilo'));
				$select->selectOptionByLabel($strGrauSigilo);
	    	}
    	}
    }

    public function gerarProtocolo()
    {
    	$strSequencia = str_pad(rand(1, 999999), 6, "0", STR_PAD_LEFT);
		return '999990.' . $strSequencia . '/2015-00';
    }

    public static function gerarProcessoTeste($test, array $dadosProcesso = null)
    {
        $test->byLinkText("Iniciar Processo")->click();

        $dadosProcesso = $dadosProcesso ?: array();
        $dadosProcesso["TIPO_PROCESSO"] = @$dadosProcesso["TIPO_PROCESSO"] ?: "Licitação: Pregão Eletrônico";
        $dadosProcesso["DESCRICAO"] = @$dadosProcesso["DESCRICAO"] ?: util::random_string(20);
        $dadosProcesso["OBSERVACOES"] = @$dadosProcesso["OBSERVACOES"] ?: util::random_string(100);
        $dadosProcesso["INTERESSADOS"] = @$dadosProcesso["INTERESSADOS"] ?: util::random_string(40);
        $dadosProcesso["RESTRICAO"] = @$dadosProcesso["RESTRICAO"] ?: PaginaIniciarProcesso::STA_NIVEL_ACESSO_PUBLICO;
        $dadosProcesso["HIPOTESE_LEGAL"] = @$dadosProcesso["HIPOTESE_LEGAL"] ?: "";

        $paginaIniciarProcesso = new PaginaIniciarProcesso($test);
        $paginaIniciarProcesso->selecionarTipoProcesso($dadosProcesso["TIPO_PROCESSO"]);
        $paginaIniciarProcesso->descricao($dadosProcesso["DESCRICAO"]);        
        $paginaIniciarProcesso->observacoes($dadosProcesso["OBSERVACOES"]);        
        $paginaIniciarProcesso->selecionarRestricao($dadosProcesso["RESTRICAO"], $dadosProcesso["HIPOTESE_LEGAL"]);
        $paginaIniciarProcesso->adicionarInteressado($dadosProcesso["INTERESSADOS"]);

        $paginaIniciarProcesso->salvarProcesso();

        $test->frame(NULL);
        $test->frame("ifrArvore");
        $protocoloProcesso = trim($test->byXPath("//span[@title='". $dadosProcesso["TIPO_PROCESSO"] ."']")->text());

        return $protocoloProcesso;
    }
}