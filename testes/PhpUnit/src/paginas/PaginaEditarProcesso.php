<?php

use \utilphp\util;
use PHPUnit_Extensions_Selenium2TestCase_Keys as Keys;

class PaginaEditarProcesso extends PaginaTeste
{
    const STA_NIVEL_ACESSO_PUBLICO  = 0;
    const STA_NIVEL_ACESSO_RESTRITO = 1;
    const STA_NIVEL_ACESSO_SIGILOSO = 2;

    public function __construct($test)
    {
        parent::__construct($test);
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
                sleep(2);
            }
        }
    }

    public function listarInteressados() 
    {
        $options = $this->test->byId('selInteressadosProcedimento')->elements($this->test->using('css selector')->value('option'));
        return array_map(function($opt) {return $opt->text();}, $options);
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

    public function gerarProtocolo()
    {
        $strSequencia = str_pad(rand(1, 999999), 6, "0", STR_PAD_LEFT);
        return '999990.' . $strSequencia . '/2015-00';
    }
}