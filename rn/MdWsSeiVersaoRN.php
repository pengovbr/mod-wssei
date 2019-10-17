<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdWsSeiVersaoRN extends InfraRN
{
    CONST ATRIBUTO_VERSAO_INFRA_PARAMETRO = 'VERSAO_MODULO_WSSEI';
    private $numSeg = 0;

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    private function logar($strMsg)
    {
        InfraDebug::getInstance()->gravar($strMsg);
    }

    private function inicializar($strTitulo)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');
        ini_set('mssql.timeout', '0');

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(true);
        InfraDebug::getInstance()->setBolEcho(true);
        InfraDebug::getInstance()->limpar();

        $this->numSeg = InfraUtil::verificarTempoProcessamento();

        $this->logar($strTitulo);
    }

    private function finalizar($strMsg = null, $bolErro)
    {

        if (!$bolErro) {
            $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
            $this->logar('TEMPO TOTAL DE EXECUCAO: ' . $this->numSeg . ' s');
        } else {
            $strMsg = 'ERRO: ' . $strMsg;
        }

        if ($strMsg != null) {
            $this->logar($strMsg);
        }

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        $this->numSeg = 0;
        die;
    }

    protected function atualizarVersaoConectado()
    {
        try {
            $this->inicializar('INICIANDO ATUALIZACAO VERSAO MÓDULO ('. strtoupper(MdWsSeiRest::getNome()). ') ' . MdWsSeiRest::getVersao());

            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $strVersaoBanco = $objInfraParametro->getValor(self::ATRIBUTO_VERSAO_INFRA_PARAMETRO, '');

            switch($strVersaoBanco) {
                case '': $this->atualizaVersao_0_8_12();
                //case '1.0.0': $this->atualizaVersao_1_0_1();
                    break;
                default:
                    if($strVersaoBanco == MdWsSeiRest::getVersao()){
                        $this->finalizar('VERSAO JA CONSTA COMO ATUALIZADA', false);
                    } else {
                        $this->finalizar('VERSAO NAO IDENTIFICADA. VERIFIQUE COM OS RESPONSAVEIS', false);
                    }
            }
            $this->finalizar('FIM', false);

        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);
            throw new InfraException('Erro atualizando versão.', $e);
        }

    }

    private function atualizaVersaoInfraParametro($strVersao)
    {
        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $objInfraParametro->setValor(self::ATRIBUTO_VERSAO_INFRA_PARAMETRO, $strVersao);
    }

    private function atualizaVersao_0_8_12()
    {
        $this->logar("CRIANDO TABELA PARA NOTIFICAÇÃO DE ATIVIDADES.");
        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
        BancoSEI::getInstance()->executarSql(
            'CREATE TABLE md_wssei_notificacao_atividade (
                id_notificacao_atividade ' . $objInfraMetaBD->tipoNumero() . '  NOT NULL ,
                id_atividade ' . $objInfraMetaBD->tipoNumero() . '  NOT NULL ,
                titulo ' . $objInfraMetaBD->tipoTextoFixo(150) . '  NOT NULL ,
                mensagem ' . $objInfraMetaBD->tipoTextoGrande() . '  NOT NULL ,
                dth_notificacao ' . $objInfraMetaBD->tipoDataHora() . '  NOT NULL)'
        );
        BancoSEI::getInstance()->criarSequencialNativa('seq_md_wssei_notificacao_atividade',1);
        $objInfraMetaBD->criarIndice('md_wssei_notificacao_atividade','i01_md_wssei_notificacao_atividade',array('id_notificacao_atividade'));
        $objInfraMetaBD->criarIndice('md_wssei_notificacao_atividade','i02_md_wssei_notificacao_atividade',array('id_atividade'));
        $objInfraMetaBD->criarIndice('md_wssei_notificacao_atividade','i03_md_wssei_notificacao_atividade',array('id_notificacao_atividade','id_atividade'));
        BancoSEI::getInstance()->executarSql('alter table md_wssei_notificacao_atividade add constraint fk_md_wssei_not_ativ_id_atividade foreign key (id_atividade) references atividade (id_atividade) on delete cascade');

        $infraAgemdanemtoTarefaDTO = new InfraAgendamentoTarefaDTO();
        $infraAgemdanemtoTarefaDTO->setStrDescricao('Agendamento para notificação de atividades.');
        $infraAgemdanemtoTarefaDTO->setStrComando('MdWsSeiAgendamentoRN::notificacaoAtividades');

        $numVersaoAtualSEI = explode('.', SEI_VERSAO);
        $numVersaoAtualSEI = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT); }, $numVersaoAtualSEI);
        $numVersaoAtualSEI = intval(join($numVersaoAtualSEI));
        $numVersaoMudancaAgendamento = explode('.', '3.1.0');
        $numVersaoMudancaAgendamento = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT); }, $numVersaoMudancaAgendamento);
        $numVersaoMudancaAgendamento = intval(join($numVersaoMudancaAgendamento));
        if($numVersaoMudancaAgendamento >= $numVersaoAtualSEI){
            $infraAgemdanemtoTarefaDTO->setStrStaPeriodicidadeExecucao('N');
            $infraAgemdanemtoTarefaDTO->setStrPeriodicidadeComplemento('0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55');
        } else {
            $infraAgemdanemtoTarefaDTO->setStrStaPeriodicidadeExecucao('D');
            $infraAgemdanemtoTarefaDTO->setStrPeriodicidadeComplemento('0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23');
        }

        $infraAgemdanemtoTarefaDTO->setStrSinAtivo('S');
        $infraAgemdanemtoTarefaDTO->setStrSinSucesso('S');

        $infraAgemdanemtoTarefaBD = new InfraAgendamentoTarefaBD(BancoSEI::getInstance());
        $infraAgemdanemtoTarefaBD->cadastrar($infraAgemdanemtoTarefaDTO);

        $this->atualizaVersaoInfraParametro('0.8.12');
    }

}