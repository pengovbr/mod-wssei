<?php

/**
 * Teste que cadastra um processo via fixture, loga no sistema e arquiva esse processo, em seguida manipula as datas de guarda antes de executar o agendamento de verificar
 * se o tempo de guarda venceu e em seguida navega até a tela de Avaliacao de Processo para validar que o processo consta nessa tela
 *
 */
class ExecutaAgendamentoWsSeiTest extends CenarioBaseTestCase
{
    public static $remetente;

    /**
     * @return void
     */
    public function test_executa_agendamento_wssei()
    {
        self::$remetente = $this->definirContextoTeste(CONTEXTO_SEI);
        
        // Acessar sistema do this->REMETENTE do processo e conclui processo
        $this->acessarSistema(self::$remetente['URL'], self::$remetente['SIGLA_UNIDADE'], self::$remetente['LOGIN'], self::$remetente['SENHA']);

        $this->paginaAgendamentos->navegarAgendamento();
        $bolExecutouAgendamento = $this->paginaAgendamentos->executarAgendamento('MdWsSeiAgendamentoRN :: notificacaoAtividades');
        sleep(5);
        $this->assertTrue($bolExecutouAgendamento);
    }

}
