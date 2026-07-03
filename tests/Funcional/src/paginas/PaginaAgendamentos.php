<?php

use PHPUnit\Extensions\Selenium2TestCase\Keys as Keys;

class PaginaAgendamentos extends PaginaTeste
{
    /**
     * Método contrutor
     * 
     * @return void
     */
    public function __construct($test)
    {
        parent::__construct($test);
    }

    public function navegarAgendamento()
    {
        $this->test->byId("txtInfraPesquisarMenu")->value(utf8_encode('Agendamentos'));
        $this->test->byXPath("//a[@link='infra_agendamento_tarefa_listar']")->click();
    }

    public function executarAgendamento($agendamento)
    {
        $linhasAgendamentos = $this->test->elements($this->test->using('xpath')->value('//table[contains(@class, "infraTable")]/tbody/tr'));
        unset($linhasAgendamentos[0]);

        foreach($linhasAgendamentos as $idx => $linha) {
            $colunaComando = $linha->byXPath('./td[2]');

            if ($colunaComando->text() === $agendamento) {
                $this->test->byXPath("(//img[@title='Executar Agendamento'])[$idx]")->click();
                $bolExisteAlerta = $this->alertTextAndClose();
                if ($bolExisteAlerta != null) $this->test->keys(Keys::ENTER);
                return true;
            }
        }
        return false;
    }
}
