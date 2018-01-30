<?php

/**
 * Class MdWsSeiDocumentoDTO
 * DTO somente para encapsulamento de dados.
 * OBS: Nao estou usando API pois em modulos do SEI o autoload nao carrega as API's.
 */
class MdWsSeiDocumentoDTO extends InfraDTO{

    public function getStrNomeTabela()
    {
        throw new InfraException('DTO nao utilizavel para consulta!');
    }





    public function montar(){
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdProcesso');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_DTA, 'DataGeracaoDocumento');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Numero');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdTipoDocumento');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Descricao');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'NomeArquivo');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'NivelAcesso');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'IdHipoteseLegal');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'GrauSigilo');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'Assuntos');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'Interessados');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'Destinatarios');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'Remetentes');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'ConteudoDocumento');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Observacao');
        
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'NomeTipoDocumento');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Favoritos');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'Aplicabilidade');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'Start');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM, 'Limit');
    }        

}

