<?
require_once dirname(__FILE__) . '/../../../SEI.php';

class MdWsSeiAcompanhamentoRN extends InfraRN
{

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    public function encapsulaAcompanhamento(array $post)
    {
        $acompanhamentoDTO = new AcompanhamentoDTO();

        if (!empty($post['protocolo'])) {
            $acompanhamentoDTO->setDblIdProtocolo($post['protocolo']);
        }

        $acompanhamentoDTO->setNumIdGrupoAcompanhamento($post['grupo']);
        $acompanhamentoDTO->setStrObservacao($post['observacao']);

        $acompanhamentoDTO->setNumIdUsuarioGerador(SessaoSEI::getInstance()->getNumIdUsuario());
        $acompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $acompanhamentoDTO->setDthGeracao(InfraData::getStrDataHoraAtual());
        $acompanhamentoDTO->setNumTipoVisualizacao(AtividadeRN::$TV_VISUALIZADO);
        $acompanhamentoDTO->setNumIdAcompanhamento(null);

        return $acompanhamentoDTO;

    }

    /**
     * Método que realiza o cadastro de um acompanhamento especial
     * @param AcompanhamentoDTO $acompanhamentoDTO
     * @return array
     */
    protected function cadastrarAcompanhamentoControlado(AcompanhamentoDTO $acompanhamentoDTO)
    {
        try {
            if ($acompanhamentoDTO->isSetDblIdProtocolo()) {
                $protocoloRN = new ProtocoloRN();
                $protocoloDTO = new ProtocoloDTO();

                $protocoloDTO->setDblIdProtocolo($acompanhamentoDTO->getDblIdProtocolo());
                $protocoloDTO->retNumIdUnidadeGeradora();
                /** Consulta o componente SEI para retorno dos dados do protocolo para validação **/
                $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
                if (!$protocoloDTO || $protocoloDTO->getNumIdUnidadeGeradora() != $acompanhamentoDTO->getNumIdUnidade()) {
                    throw new Exception('Protocolo não encontrado.');
                }
            }
            $acompanhamentoRN = new AcompanhamentoRN();
            $acompanhamentoRN->cadastrar($acompanhamentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Acompanhamento realizado com sucesso!');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que altera um acompanhamento especial
     * @param AcompanhamentoDTO $acompanhamentoDTO
     * @return array
     */
    protected function alterarAcompanhamentoControlado(AcompanhamentoDTO $acompanhamentoDTO)
    {
        try {
            if (!$acompanhamentoDTO->isSetDblIdProtocolo()) {
                throw new Exception('Protocolo não encontrado.');
            }
            $acompanhamentoRN = new AcompanhamentoRN();

            $acompanhamentoConsultaDTO = new AcompanhamentoDTO();
            $acompanhamentoConsultaDTO->retNumIdAcompanhamento();
            $acompanhamentoConsultaDTO->setDblIdProtocolo($acompanhamentoDTO->getDblIdProtocolo());
            $acompanhamentoConsultaDTO->setNumIdUnidade($acompanhamentoDTO->getNumIdUnidade());

            $acompanhamentoConsultaDTO = $acompanhamentoRN->consultar($acompanhamentoConsultaDTO);

            if (!$acompanhamentoConsultaDTO) {
                throw new Exception('Acompanhamento não encontrado.');
            }

            $acompanhamentoDTO->setNumIdAcompanhamento($acompanhamentoConsultaDTO->getNumIdAcompanhamento());
            //Prevendo bug do SEI no arquivo AcompanhamentoRN::174
            $acompanhamentoDTO->unSetNumTipoVisualizacao();

            $acompanhamentoRN->alterar($acompanhamentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Acompanhamento alterado com sucesso!');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que excluir um acompanhamento especial
     * @param AcompanhamentoDTO $acompanhamentoDTO
     * @return array
     */
    protected function excluirAcompanhamentoControlado(AcompanhamentoDTO $acompanhamentoDTO)
    {
        try {
            if (!$acompanhamentoDTO->isSetNumIdAcompanhamento()) {
                throw new Exception('Acompanhamento não informado.');
            }
            $acompanhamentoRN = new AcompanhamentoRN();

            $acompanhamentoConsultaDTO = new AcompanhamentoDTO();
            $acompanhamentoConsultaDTO->retNumIdAcompanhamento();
            $acompanhamentoConsultaDTO->setNumIdAcompanhamento($acompanhamentoDTO->getNumIdAcompanhamento());
            $acompanhamentoConsultaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

            $acompanhamentoConsultaDTO = $acompanhamentoRN->consultar($acompanhamentoConsultaDTO);

            if (!$acompanhamentoConsultaDTO) {
                throw new Exception('Acompanhamento não encontrado.');
            }

            $acompanhamentoRN->excluir(array($acompanhamentoConsultaDTO));
            return MdWsSeiRest::formataRetornoSucessoREST('Acompanhamento excluido com sucesso!');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que consulta um acompanhamento de um processo por protocolo
     * @param AcompanhamentoDTO $acompanhamentoDTO
     * @return array
     */
    protected function consultarAcompanhamentoPorProtocoloControlado(AcompanhamentoDTO $acompanhamentoDTO)
    {
        try {
            $result = array();
            if (!$acompanhamentoDTO->isSetDblIdProtocolo()) {
                throw new Exception('Protocolo não informado.');
            }
            $acompanhamentoRN = new AcompanhamentoRN();

            $acompanhamentoDTO->retTodos(true);
            $acompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

            $acompanhamentoDTO = $acompanhamentoRN->consultar($acompanhamentoDTO);


            if ($acompanhamentoDTO) {
                $result = array(
                    'idAcompanhamento' => $acompanhamentoDTO->getNumIdAcompanhamento(),
                    'idProtocolo' => $acompanhamentoDTO->getDblIdProtocolo(),
                    'idUnidade' => $acompanhamentoDTO->getNumIdUnidade(),
                    'observacao' => $acompanhamentoDTO->getStrObservacao(),
                    'visualizacao' => $acompanhamentoDTO->getNumTipoVisualizacao(),
                    'idGrupoAcompanhamento' => $acompanhamentoDTO->getNumIdGrupoAcompanhamento(),
                    'nomeGrupo' => $acompanhamentoDTO->getStrNomeGrupo()
                );
            }


            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que retorna os acompanhamentos especiais da unidade
     * @param AcompanhamentoDTO $acompanhamentoDTOConsulta
     * @return array
     */
    protected function listaAcompanhamentosUnidadeConectado(AcompanhamentoDTO $acompanhamentoDTOConsulta)
    {
        try{
            $result = array();
            $acompanhamentoDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $acompanhamentoDTOConsulta->setOrd('IdProtocolo', InfraDTO::$TIPO_ORDENACAO_DESC);
            $acompanhamentoRN = new AcompanhamentoRN();
            /** Acessando componente SEI para retorna da lista de acompanhamentos da unidade **/
            $arrAcompanhamentoDTO = $acompanhamentoRN->listarAcompanhamentosUnidade($acompanhamentoDTOConsulta);

            foreach($arrAcompanhamentoDTO as $acompanhamentoDTO) {
                $result[] = array(
                    'idAcompanhamento' => $acompanhamentoDTO->getNumIdAcompanhamento(),
                    'idGrupoAcompanhamento' => $acompanhamentoDTO->getNumIdGrupoAcompanhamento(),
                    'nomeGrupoAcompanhamento' => $acompanhamentoDTO->getStrNomeGrupo(),
                    'idProtocolo' => $acompanhamentoDTO->retDblIdProtocolo(),
                    'idUsuarioGerador' => $acompanhamentoDTO->getNumIdUsuarioGerador(),
                    'dataGeracao' => $acompanhamentoDTO->getDthGeracao(),
                    'observacao' => $acompanhamentoDTO->getStrObservacao(),
                    'siglaUsuario' => $acompanhamentoDTO->getStrSiglaUsuario(),
                    'nomeUsuario' => $acompanhamentoDTO->getStrNomeUsuario(),
                    'siglaUsuario' => $acompanhamentoDTO->getStrSiglaUsuario(),
                    'tipoVisualizacao' => $acompanhamentoDTO->getNumTipoVisualizacao(),
                    'nomeTipoProcedimento' => $acompanhamentoDTO->getObjProcedimentoDTO()->getStrNomeTipoProcedimento(),
                    'protocoloFormatado' => $acompanhamentoDTO->getObjProcedimentoDTO()->getStrProtocoloProcedimentoFormatado(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $acompanhamentoDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
}