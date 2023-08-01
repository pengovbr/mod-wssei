<?
require_once dirname(__FILE__) . '/../../web/SEI.php';

try {
        
  class MdWsSeiVersaoRN extends InfraScriptVersao
    {
        const PARAMETRO_VERSAO_MODULO = 'VERSAO_MODULO_WSSEI';
        const NOME_MODULO = 'Módulo de Web Service REST SEI';
        
        protected $objInfraBanco;
        protected $objMetaBD;
        protected $objInfraSequencia;
        protected $objInfraParametro;
        
    public function __construct()
        {
      parent::__construct();
      ini_set('max_execution_time', '0');
      ini_set('memory_limit', '-1');
        
      SessaoSEI::getInstance(false);
        
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->setBolEcho(true);
      InfraDebug::getInstance()->limpar();
    }
        
    protected function inicializarObjInfraIBanco()
        {
      return BancoSEI::getInstance();
    }
        
    protected function verificarVersaoInstaladaControlado()
        {
        $objInfraParametroDTO = new InfraParametroDTO();
        $objInfraParametroDTO->setStrNome(MdWsSeiVersaoRN::PARAMETRO_VERSAO_MODULO);
        $objInfraParametroBD = new InfraParametroBD(BancoSEI::getInstance());
      if ($objInfraParametroBD->contar($objInfraParametroDTO) == 0) {
          $objInfraParametroDTO->setStrValor('0.0.0');
          $objInfraParametroBD->cadastrar($objInfraParametroDTO);
      }
    }
        
    public function versao_0_0_0($strVersaoAtual)
        {
        $this->logar("VERSÃO 0.0.0 atualizada.");
    }
        
    public function versao_0_8_12($strVersaoAtual)
        {
        $this->logar("CRIANDO TABELA PARA NOTIFICACAO DE ATIVIDADES.");
        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
        BancoSEI::getInstance()->executarSql(
            'CREATE TABLE md_wssei_notificacao_ativ (
                        id_notificacao_atividade ' . $objInfraMetaBD->tipoNumero() . '  NOT NULL ,
                        id_atividade ' . $objInfraMetaBD->tipoNumero() . '  NOT NULL ,
                        titulo ' . $objInfraMetaBD->tipoTextoFixo(150) . '  NOT NULL ,
                        mensagem ' . $objInfraMetaBD->tipoTextoGrande() . '  NOT NULL ,
                        dth_notificacao ' . $objInfraMetaBD->tipoDataHora() . '  NOT NULL)'
        );
        BancoSEI::getInstance()->criarSequencialNativa('seq_md_wssei_notificacao_ativ', 1);
        $objInfraMetaBD->criarIndice('md_wssei_notificacao_ativ', 'i01_md_wssei_notificacao_ativ', array('id_notificacao_atividade'));
        $objInfraMetaBD->criarIndice('md_wssei_notificacao_ativ', 'i02_md_wssei_notificacao_ativ', array('id_atividade'));
        $objInfraMetaBD->criarIndice('md_wssei_notificacao_ativ', 'i03_md_wssei_notificacao_ativ', array('id_notificacao_atividade','id_atividade'));
        BancoSEI::getInstance()->executarSql('alter table md_wssei_notificacao_ativ add constraint fk_md_wssei_not_ativ_id_ativ foreign key (id_atividade) references atividade (id_atividade) on delete cascade');
        
        $infraAgemdanemtoTarefaDTO = new InfraAgendamentoTarefaDTO();
        $infraAgemdanemtoTarefaDTO->setStrDescricao('Agendamento para notificacao de atividades.');
        $infraAgemdanemtoTarefaDTO->setStrComando('MdWsSeiAgendamentoRN::notificacaoAtividades');
        
        //Obtem valor do SEI.php
        $numVersaoAtualSEI = explode('.', SEI_VERSAO);
        $numVersaoAtualSEI = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT);
        }, $numVersaoAtualSEI);
        $numVersaoAtualSEI = intval(join($numVersaoAtualSEI));
        $numVersaoMudancaAgendamento = explode('.', '3.1.0');
        $numVersaoMudancaAgendamento = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT);
        }, $numVersaoMudancaAgendamento);
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
              $this->logar("VERSÃO 0.8.12 atualizada.");
    }
        
    public function versao_1_0_0($strVersaoAtual)
        {
        $this->logar("VERSÃO 1.0.0 atualizada.");
    }
        
    public function versao_1_0_1($strVersaoAtual)
        {
        $this->logar("VERSÃO 1.0.1 atualizada.");
    }
        
    public function versao_1_0_2($strVersaoAtual)
        {
        $this->logar("VERSÃO 1.0.2 atualizada.");
    }
        
    public function versao_1_0_3($strVersaoAtual)
        {
        $this->logar("VERSÃO 1.0.3 atualizada.");
    }
        
    public function versao_1_0_4($strVersaoAtual)
        {
        $this->logar("VERIFICANDO SE A CHAVE: TokenSecret ESTA PRESENTE NO ARQUIVO DE CONFIGURACOES.");
                
        $token = ConfiguracaoMdWSSEI::getInstance()->getValor('WSSEI', 'TokenSecret', false);
      if((!$token) || (strlen($token)<25)){
            $msg = 'Token Secret inexistente ou tamanho menor que o permitido! Verifique o manual de instalacao do módulo. ';
            $msg = $msg . 'O script de instalacao foi interrompido. Módulo nao instalado corretamente. ';
            $msg = $msg . 'Ajuste a chave e rode novamente o script.';
            $this->logar($msg);
            throw new InfraException($msg);
      }
        
        $this->logar("VERSÃO 1.0.4 atualizada.");
    }
        
    public function versao_2_0_0($strVersaoAtual)
        {
        $this->logar("VERSÃO 2.0.0 atualizada.");
    }

    public function versao_2_1_0($strVersaoAtual)
        {
        $this->logar("VERSÃO $strVersaoAtual atualizada.");
    }
            
    public function versao_2_1_1($strVersaoAtual)
        {
        $this->logar("VERSÃO $strVersaoAtual atualizada.");
    } 

    public function versao_2_1_2($strVersaoAtual)
        {
        $this->logar("VERSÃO $strVersaoAtual atualizada.");
    } 
  }

    session_start();
    SessaoSEI::getInstance(false);
    BancoSEI::getInstance()->setBolScript(true);

    $objVersaoSeiRN = new MdWsSeiVersaoRN();
    $objVersaoSeiRN->verificarVersaoInstalada();
    $objVersaoSeiRN->setStrNome(MdWsSeiVersaoRN::NOME_MODULO);
    $objVersaoSeiRN->setStrVersaoAtual(MdWsSeiRest::VERSAO_MODULO);
    $objVersaoSeiRN->setStrParametroVersao(MdWsSeiVersaoRN::PARAMETRO_VERSAO_MODULO);
    $objVersaoSeiRN->setArrVersoes(
        array(
            '0.0.0' => 'versao_0_0_0',
            '0.8.12' => 'versao_0_8_12',
            '1.0.0' => 'versao_1_0_0',
            '1.0.1' => 'versao_1_0_1',
            '1.0.2' => 'versao_1_0_2',
            '1.0.3' => 'versao_1_0_3',
            '1.0.4' => 'versao_1_0_4',
            '2.0.0' => 'versao_2_0_0',
            '2.1.0' => 'versao_2_1_0',
            '2.1.1' => 'versao_2_1_1',
            '2.1.2' => 'versao_2_1_2',
        )
    );

    $objVersaoSeiRN->setStrVersaoInfra('1.595.1');
    $objVersaoSeiRN->setBolMySql(true);
    $objVersaoSeiRN->setBolOracle(true);
    $objVersaoSeiRN->setBolSqlServer(true);
    $objVersaoSeiRN->setBolPostgreSql(true);
    $objVersaoSeiRN->setBolErroVersaoInexistente(true);
    $objVersaoSeiRN->atualizarVersao();
} catch (Exception $e) {
    echo (InfraException::inspecionar($e));
  try {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
  } catch (Exception $e) {
  }
          exit(1);
}