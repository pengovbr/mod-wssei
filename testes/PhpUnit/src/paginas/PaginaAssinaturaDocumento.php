<?php

use PHPUnit_Extensions_Selenium2TestCase_Keys as Keys;

class PaginaAssinaturaDocumento extends PaginaTeste
{
	const JANELA_ASSINATURA = "janelaAssinatura";

	function __construct($test)
	{
		parent::__construct($test);
	}

    public function assinante($strAssinante)
    {
        $this->test->window(self::JANELA_ASSINATURA);
    	$input = $this->test->byId("txtUsuario");

    	if(isset($strAssinante)){
			$input->value($strAssinante);
			$this->test->waitUntil(function($testCase) {
		    	$nomeAssinante = $testCase->byId("txtUsuario")->value();
		        $testCase->byLinkText($nomeAssinante)->click();
		        return true;
	        }, 8000);
    	}

        return $input->value();
    }

    public function senha($value)
    {
        $this->test->window(self::JANELA_ASSINATURA);
    	$input = $this->test->byId("pwdSenha");
    	return $input->value($value);
    }

    public function selecionarOrgaoAssinante($strOrgaoAssinante)
    {
        $this->test->window(self::JANELA_ASSINATURA);
    	$input = $this->test->byId("selOrgao");
    	$this->test->select($input)->selectOptionByLabel($strOrgaoAssinante);
		return $this->test->select($input)->selectedLabel();
    }

    public function selecionarCargoAssinante($strCargoAssinante)
    {
        $this->test->window(self::JANELA_ASSINATURA);
    	$input = $this->test->byId("selCargoFuncao");
    	$this->test->select($input)->selectOptionByLabel($strCargoAssinante);
		return $this->test->select($input)->selectedLabel();
    }

	public function assinarComLoginSenha($pwdSenha)
	{
        $this->test->window(self::JANELA_ASSINATURA);
		$input = $this->test->byId("pwdSenha");
        $input->value($pwdSenha);
        $this->test->keys(Keys::ENTER);
    }
}