<?php

use PHPUnit_Extensions_Selenium2TestCase_Keys as Keys;

class PaginaEnviarProcesso extends PaginaTeste
{
    public function __construct($test)
    {
        parent::__construct($test);
        $this->unidadeInput = $test->byId('txtUnidade');
        $this->manterAbertoCheck = $test->byId('chkSinManterAberto');
        $this->removerAnotacoesCheck = $test->byId('chkSinRemoverAnotacoes');
        $this->enviarNotificacaoCheck = $test->byId('chkSinEnviarEmailNotificacao');
        $this->dataCertaOption = $test->byId('optDataCerta');
        $this->prazoInput = $test->byId('txtPrazo');
		$this->diasOption = $test->byId('optDias');
		$this->diasInput = $test->byId('txtDias');
		$this->diasUteisInput = $test->byId('chkSinDiasUteis');
		$this->enviarButton = $test->byId('sbmEnviar');
    }

    public function adicionarUnidade($nomeUnidade)
    {
		$this->unidadeInput->value($nomeUnidade);
		$this->test->waitUntil(function($testCase) {
	    	$nomeUnidade = $testCase->byId('txtUnidade')->value();
	        $testCase->byLinkText($nomeUnidade)->click();
	        return true;
        }, 8000);
	}

	public function salvar()
	{
		$this->enviarButton->click();
	}
}