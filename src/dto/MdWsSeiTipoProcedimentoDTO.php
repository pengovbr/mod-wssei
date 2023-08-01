<?php

/**
 * Class MdWsSeiTipoProcedimentoDTO
 * DTO somente para encapsulamento de dados.
 * OBS: Nao estou usando API pois em modulos do SEI o autoload nao carrega as API's.
 */
class MdWsSeiTipoProcedimentoDTO extends InfraDTO{

  public function getStrNomeTabela()
    {
      throw new InfraException('DTO nao utilizavel para consulta!');
  }

  public function montar(){
      $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdTipoProcedimento');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Nome');
//        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'SinInterno');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Favoritos');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'Start');
      $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'Limit');
  }        

}

