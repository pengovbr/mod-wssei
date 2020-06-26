<?
/**
 * User: Eduardo Romão
 * E-mail: eduardo.romao@outlook.com
 */

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdWsSeiNotificacaoAtividadeDTO extends InfraDTO
{

    public function getStrNomeTabela()
    {
        return 'md_wssei_notificacao_ativ';
    }

    public function montar()
    {

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdNotificacaoAtividade',
            'id_notificacao_atividade');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdAtividade',
            'id_atividade');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Titulo',
            'titulo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'Mensagem',
            'mensagem');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            'Notificacao',
            'dth_notificacao');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DBL,
            'IdProtocolo',
            'p.id_protocolo',
            'protocolo p');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
            'IdUnidadeGeradora',
            'u.id_unidade',
            'unidade u');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
            'SiglaUnidadeGeradora',
            'u.sigla',
            'unidade u');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
            'IdTarefa',
            'a.id_tarefa',
            'atividade a');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTH,
            'ConclusaoAtividade',
            'a.dth_conclusao',
            'atividade a');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTH,
            'AberturaAtividade',
            'a.dth_abertura',
            'atividade a');


        $this->configurarPK('IdNotificacaoAtividade', InfraDTO::$TIPO_PK_NATIVA);

        $this->configurarFK('IdAtividade', 'atividade a', 'a.id_atividade');
        $this->configurarFK('IdProtocolo', 'protocolo p', 'p.id_protocolo');
        $this->configurarFK('IdUnidadeGeradora', 'protocolo p', 'p.id_unidade_geradora');
    }
}

?>

