<?

class MdWsSeiAtividadeDTO extends AtividadeDTO{

    public function montar(){
        parent::montar();

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
            'ProtocoloFormatadoPesquisaProtocolo',
            'protocolo_formatado_pesquisa',
            'protocolo');

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
            'IdGrupoAcompanhamentoProcedimento',
            'id_grupo_acompanhamento',
            'acompanhamento');


        $this->configurarFK('IdProtocolo', 'acompanhamento', 'id_protocolo');
    }

}
