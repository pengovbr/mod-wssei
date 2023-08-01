<?

class MdWsSeiPesquisaProtocoloSolrDTO extends InfraDTO{

  public function getStrNomeTabela() {
      return null;
  }

  public function montar() {
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'PalavrasChave');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Descricao');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Observacao');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'ProtocoloPesquisa');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdTipoProcedimento');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdSerie');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Numero');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'StaTipoData');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_DTA, 'Inicio');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_DTA, 'Fim');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_DBL, 'IdProcedimento');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdGrupoAcompanhamentoProcedimento');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdUnidadeGeradora');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdAssunto');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'buscaRapida');
  }
}

?>
