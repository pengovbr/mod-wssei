<?

class MdWsSeiProtocoloDTO extends ProtocoloDTO{
    const SIN_TIPO_BUSCA_R = 'R';
    const SIN_TIPO_BUSCA_G = 'G';

  public function montar(){
      parent::montar();

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
          'IdGrupoAcompanhamentoProcedimento',
          'id_grupo_acompanhamento',
          'acompanhamento');


      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
          'IdUsuarioGeradorAcompanhamento',
          'id_usuario_gerador',
          'acompanhamento');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTH,
          'GeracaoAcompanhamento',
          'dth_geracao',
          'acompanhamento');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
          'IdUsuarioAtribuicaoAtividade',
          'id_usuario_atribuicao',
          'atividade');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
          'SinCienciaProcedimento',
          'p2.sin_ciencia',
          'procedimento p2');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTH,
          'AberturaAtividade',
          'dth_abertura',
          'atividade');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
          'IdUnidadeAtividade',
          'id_unidade',
          'atividade');

      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'SinTipoBusca');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'SinApenasMeus');
        
      $this->configurarFK('IdProtocolo', 'procedimento p2', 'p2.id_procedimento', InfraDTO::$TIPO_FK_OBRIGATORIA);
      $this->configurarFK('IdProtocolo', 'acompanhamento', 'id_protocolo');
  }

}
