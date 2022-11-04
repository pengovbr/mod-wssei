<?php

use PHPUnit_Extensions_Selenium2TestCase_Keys as Keys;

class PaginaTramitarProcesso extends PaginaTeste
{
    public function __construct($test)
    {
        parent::__construct($test);
            
    }

    public function repositorio($siglaRepositorio) 
    {
        $this->repositorioSelect = $this->test->select($this->test->byId('selRepositorioEstruturas'));

        if(isset($siglaRepositorio)) 
            $this->repositorioSelect->selectOptionByLabel($siglaRepositorio);

        return $this->test->byId('selRepositorioEstruturas')->value();
    }


    public function unidade($nomeUnidade, $hierarquia = null) 
    {
        $this->unidadeInput =$this->test->byId('txtUnidade');
        $this->unidadeInput->value($nomeUnidade);
        $this->test->keys(Keys::ENTER);
        $this->test->waitUntil(function($testCase) use($hierarquia) {
            $nomeUnidade = $testCase->byId('txtUnidade')->value();
            if(isset($hierarquia)){
                $nomeUnidade .= ' - ' . $hierarquia;
            }

            $testCase->byPartialLinkText($nomeUnidade)->click();
            return true;
        }, PEN_WAIT_TIMEOUT);

        return $this->unidadeInput->value();
    }

    public function urgente($urgente) 
    {
        $this->urgenteCheck = $this->test->byId('chkSinUrgente');
        if(isset($urgente) && ((!$urgente && $this->urgenteCheck->selected()) || ($urgente && !$this->urgenteCheck->selected())))
            $this->urgenteCheck->click();

        return $this->urgenteCheck->selected();
    }

    public function tramitar()
    {
        $this->tramitarButton = $this->test->byXPath("//button[@value='Enviar']");
        $this->tramitarButton->click();
    }
}