<?php

class PaginaDocumento extends PaginaTeste
{

    const STA_NIVEL_ACESSO_PUBLICO  = 0;
    const STA_NIVEL_ACESSO_RESTRITO = 1;
    const STA_NIVEL_ACESSO_SIGILOSO = 2;

	public function __construct($test)
    {
        parent::__construct($test);
    }

    public function navegarParaAssinarDocumento()
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");        
    	$this->test->byXPath("//img[@alt='Assinar Documento']")->click();
    }

    public function navegarParaConsultarDocumento()
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");        
        $this->test->byXPath("//img[contains(@alt, 'Consultar/Alterar Documento')]")->click();
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

    public function observacoesNaTabela($value = null)
    {   
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
        return $this->test->byXPath("//table[@class='infraTable']//tr[2]/td[2]")->text();
    }

    public function dataElaboracao($value = null)
    {
        $input = $this->test->byId("txtDataElaboracao");
        if(isset($value)) $input->value($value);
        return $input->value();
    }

    public function nomeAnexo() 
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
        return $this->test->byXPath("//table[@id='tblAnexos']//tr[2]/td[2]")->text();
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
        $options = $this->test->byId('selInteressadosProcedimento')->elements($this->test->using('css selector')->value('option'));
        return array_map(function($opt) {return $opt->text();}, $options);
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

    public function selecionarRestricao($staNivelRestricao, $strHipoteseLegal = '', $strGrauSigilo = '')
    {
        if(isset($staNivelRestricao))
        {
            $this->restricao($staNivelRestricao);
            
            if($staNivelRestricao === self::STA_NIVEL_ACESSO_RESTRITO)
            {
                $select = $this->test->select($this->byId('selHipoteseLegal'));
                $select->selectOptionByLabel($strHipoteseLegal);
            }
            else if($staNivelRestricao === self::STA_NIVEL_ACESSO_SIGILOSO)
            {
                $select = $this->test->select($this->byId('selHipoteseLegal'));
                $select->selectOptionByLabel($strHipoteseLegal);

                $select = $this->test->select($this->byId('selGrauSigilo'));
                $select->selectOptionByLabel($strGrauSigilo);
            }
        }
    }

    public function recuperarHipoteseLegal()
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
        $select = $this->test->select($this->test->byId('selHipoteseLegal'));
        return $select->selectedLabel();
    }

    public function salvarDocumento()
    {
        $this->test->byId("btnSalvar")->click();
    }    
}