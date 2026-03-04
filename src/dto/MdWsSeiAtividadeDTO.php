<?

class MdWsSeiAtividadeDTO extends AtividadeDTO{

  public function montar(): void{
      parent::montar();

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
          'ProtocoloFormatadoPesquisaProtocolo',
          'protocolo_formatado_pesquisa',
          'protocolo');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
          'IdGrupoAcompanhamentoProcedimento',
          'id_grupo_acompanhamento',
          'acompanhamento');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
          'IdOrgaoUsuarioAtribuicao',
          'uat.id_orgao',
          'usuario uat');
      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
          'SiglaOrgaoUsuarioAtribuicao',
          'ouat.sigla',
          'orgao ouat');

      $this->configurarFK('IdOrgaoUsuarioAtribuicao', 'orgao ouat', 'ouat.id_orgao');
      $this->configurarFK('IdProtocolo', 'acompanhamento', 'id_protocolo');
  }

}
