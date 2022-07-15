<?
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiNotificacaoAtividadeRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Método que cadastra uma notificação de atividade no banco de dados
     * @param MdWsSeiNotificacaoAtividadeDTO $mdWsSeiNotificacaoAtividadeDTO
     * @return array
     */
    protected function cadastrarNotificacaoControlado(MdWsSeiNotificacaoAtividadeDTO $mdWsSeiNotificacaoAtividadeDTO){
        try{
            $mdWsSeiNotificacaoAtividadeDB = new MdWsSeiNotificacaoAtividadeBD();
            /** Realiza a chamada ao banco de dados para armazenamento da notificação **/
            $mdWsSeiNotificacaoAtividadeDB->cadastrar($mdWsSeiNotificacaoAtividadeDTO);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que retonra a lista de notificações para agendamento
     * @return array
     * @throws InfraException
     */
    protected function listarNotificacoesParaAgendamentoConectado()
    {
        $notificacaoAtividadeBD = new MdWsSeiNotificacaoAtividadeBD(BancoSEI::getInstance());
        $notificacaoAtividadeDTO = new MdWsSeiNotificacaoAtividadeDTO();
        $notificacaoAtividadeDTO->retNumIdAtividade();
        $notificacaoAtividadeDTO->setDthNotificacao(
            /** Informando período de um dia pois no filtro abaixo retornam as atividades dos últimos 5 minutos */
            InfraData::calcularData(1, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ATRAS),
            InfraDTO::$OPER_MAIOR_IGUAL
        );
        $dataAtual = InfraData::getStrDataHoraAtual();
        $atividadeDTO = new MdWsSeiAtividadeDTO();
        $atividadeDTO->retNumIdAtividade();
        $atividadeDTO->retDblIdProtocolo();
        $atividadeDTO->retNumIdUnidade();
        $atividadeDTO->retNumIdOrgaoUnidade();
        $atividadeDTO->retStrSiglaUnidade();
        $atividadeDTO->retStrDescricaoUnidade();
        $atividadeDTO->retNumIdUsuarioAtribuicao();
        $atividadeDTO->retStrSiglaUsuarioAtribuicao();
        $atividadeDTO->retNumIdOrgaoUsuarioAtribuicao();
        $atividadeDTO->retStrSiglaOrgaoUsuarioAtribuicao();
        $atividadeDTO->retStrNomeUsuarioAtribuicao();
        $atividadeDTO->retStrProtocoloFormatadoProtocolo();
        $atividadeDTO->setNumIdTarefa(TarefaRN::$TI_PROCESSO_ATRIBUIDO);
        $atividadeDTO->setDthConclusao(null);
        $atividadeDTO->adicionarCriterio(array('Abertura','Abertura'),
            array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_MENOR_IGUAL),
            array(InfraData::calcularData(50, InfraData::$UNIDADE_MINUTOS, InfraData::$SENTIDO_ATRAS, $dataAtual),$dataAtual),
            InfraDTO::$OPER_LOGICO_AND
        );
        /** Realiza chamada ao banco de dados para retorno das notificações realizadas no último dia */
        $arrAtividadesNegar = $notificacaoAtividadeBD->listar($notificacaoAtividadeDTO);
        if($arrAtividadesNegar){
            $atividadeDTO->setNumIdAtividade(
                array_keys(InfraArray::indexarArrInfraDTO($arrAtividadesNegar, 'IdAtividade')),
                InfraDTO::$OPER_NOT_IN
            );
        }

        $atividadeRN = new AtividadeRN();
        /** Chama o componente SEI para retorno da lista de atividades a notificar */
        return $atividadeRN->listarRN0036($atividadeDTO);
    }

    /**
     * Serviço que registra a notificação enviada
     * @param MdWsSeiNotificacaoAtividadeDTO $notificacaoAtividadeDTO
     * @return array|mixed|string|null
     * @throws InfraException
     */
    protected function cadastrarControlado(MdWsSeiNotificacaoAtividadeDTO $notificacaoAtividadeDTO)
    {
        $notificacaoAtividadeBD = new MdWsSeiNotificacaoAtividadeBD(BancoSEI::getInstance());
        /** Acessa o banco de dados para armazenar a informação */
        return $notificacaoAtividadeBD->cadastrar($notificacaoAtividadeDTO);
    }

}