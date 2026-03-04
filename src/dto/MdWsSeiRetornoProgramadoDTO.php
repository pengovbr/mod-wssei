<?

class MdWsSeiRetornoProgramadoDTO extends RetornoProgramadoDTO{

  public function montar(): void{
      parent::montar();

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
          'IdProtocolo',
          'e.id_protocolo',
          'atividade e');

      $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTH,
          'Conclusao',
          'e.dth_conclusao',
          'atividade e');
  }

}
