<?php

use PHPUnit\Extensions\Selenium2TestCase\Keys as Keys;

/**
 * Page Object base com rotinas comuns de navegacao na interface do SEI/SUPER.
 */
class PaginaTeste
{
    protected $test;

    public function __construct($test)
    {
        $this->test = $test;
        $this->test->timeouts()->implicitWait(5000);
    }

    public function titulo()
    {
        return $this->test->title();
    }

    public function unidadeContexto($unidadeContexto)
    {
        $this->test->frame(null);
        $this->test->byXPath("(//a[@id='lnkInfraUnidade'])[2]")->click();
        $this->test->byXPath("//td[contains(.,'" . $unidadeContexto . "')]")->click();
    }

    public function navegarParaControleProcesso()
    {
        $this->test->frame(null);
        $this->test->byXPath("//img[@title='Controle de Processos']")->click();
    }

    public function sairSistema()
    {
        $this->test->frame(null);
        $this->test->byXPath("//a[@id='lnkInfraSairSistema'] | //a[@id='lnkSairSistema']")->click();
    }

    public static function selecionarUnidadeContexto($test, $unidadeContexto)
    {
        $paginaTeste = new PaginaTeste($test);
        $paginaTeste->unidadeContexto($unidadeContexto);
    }

    public function refresh()
    {
        $this->test->refresh();
    }

    public function alertTextAndClose($confirm = true)
      {
        sleep(2);
        $result = $this->test->alertText();
        $result = (!is_array($result) ? $result : null);
  
      if(isset($confirm) && $confirm) {
          $this->test->acceptAlert();
      } else {
            $this->dismissAlert();
      }
  
        #var_dump($result);
        return $result;
    }
}
