<?php

/**
 * Class MdWsSeiProcedimentoDTO
 * DTO somente para encapsulamento de dados.
 * OBS: Nao estou usando API pois em modulos do SEI o autoload nao carrega as API's.
 */
class MdWsSeiProcedimentoDTO extends InfraDTO{

    public function getStrNomeTabela()
    {
        throw new InfraException('DTO nao utilizavel para consulta!');
    }

    public function montar(){
        
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdProcedimento');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdTipoProcedimento');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Especificacao');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjAssunto');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjInteressado');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Observacao');
        // 0 publico
        // 1 restrito
        // 2 sigiloso
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'NivelAcesso');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdHipoteseLegal');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'StaGrauSigilo');
    }

}
