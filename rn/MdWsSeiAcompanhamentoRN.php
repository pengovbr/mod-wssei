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
                $protocoloDTO->retStrStaEstadoProtocolo();
                $protocoloDTO->retStrStaNivelAcessoGlobalProtocolo();

                /** Consulta o componente SEI para retorno dos dados do protocolo para validação **/
                $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
                if (!$protocoloDTO) {
                    throw new Exception('Protocolo não encontrado.');
                }
                $bolAcaoAcompanhamentoCadastrar = SessaoSEI::getInstance()->verificarPermissao('acompanhamento_cadastrar');
                if(!$bolAcaoAcompanhamentoCadastrar){
                    throw new InfraException('O usuário não possuí permissão para realizar acompanhamento.');
                }
                if($protocoloDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_PROCEDIMENTO_ANEXADO){
                    throw new InfraException('Não é possível acompanhar um Processo anexado.');
                }
                if($protocoloDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_SIGILOSO){
                    throw new InfraException('Não é possível acompanhar um Processo sigiloso.');
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

            $bolAcaoRegistrarAnotacao = SessaoSEI::getInstance()->verificarPermissao('anotacao_registrar');

            foreach($arrAcompanhamentoDTO as $acompanhamentoDTO) {
                $arrAnotacao = array();
                $arrRetornoProgramado = array();
                $arrAndamentoSituacao = array();
                $arrAndamentoMarcador = array();

                $procedimentoDTO = $acompanhamentoDTO->getObjProcedimentoDTO();
                $anotacaoDTO = $procedimentoDTO->getObjAnotacaoDTO();
                $andamentoSituacaoDTO = $procedimentoDTO->getObjAndamentoSituacaoDTO();
                $andamentoMarcadorDTO = $procedimentoDTO->getObjAndamentoMarcadorDTO();



                if($anotacaoDTO && $bolAcaoRegistrarAnotacao){
                    $arrAnotacao = array(
                        'descricao' => $anotacaoDTO->getStrDescricao(),
                        'siglaUsuario' => $anotacaoDTO->getStrSiglaUsuario(),
                        'prioridade' => $anotacaoDTO->getStrSinPrioridade() == 'N' ? false : true
                    );
                }

                if($procedimentoDTO->getArrObjRetornoProgramadoDTO() != null){
                    $arrRetornoProgramado['atrasado'] = false;
                    $strDataAtual = InfraData::getStrDataAtual();

                    foreach($procedimentoDTO->getArrObjRetornoProgramadoDTO() as $retornoProgramadoDTO) {
                        $descricaoPrazo = '';
                        $numPrazo = InfraData::compararDatas($strDataAtual,$retornoProgramadoDTO->getDtaProgramada());

                        if ($numPrazo < 0){
                            $arrRetornoProgramado['atrasado'] = true;
                        }

                        if ($numPrazo==0){
                            $descricaoPrazo .= 'até hoje';
                        }else if ($numPrazo==1){
                            $descricaoPrazo .= '1 dia';
                        }else if ($numPrazo > 1){
                            $descricaoPrazo .= $numPrazo.' dias';
                        }else if ($numPrazo == -1){
                            $descricaoPrazo .= 'atrasado 1 dia';
                        }else if ($numPrazo < -1){
                            $descricaoPrazo .= 'atrasado '.abs($numPrazo).' dias';
                        }

                        $arrRetornoProgramado['lista'][] = array(
                            'siglaUnidade' => $retornoProgramadoDTO->getStrSiglaUnidadeOrigemAtividadeEnvio(),
                            'dataProgramada' => $retornoProgramadoDTO->getDtaProgramada(),
                            'descriaoPrazo' => $descricaoPrazo
                        );
                    }
                }

                if($andamentoSituacaoDTO){
                    $arrAndamentoSituacao = array(
                        'situacao' => SituacaoINT::formatarSituacaoDesativada(
                            $andamentoSituacaoDTO->getStrNomeSituacao(),
                            $andamentoSituacaoDTO->getStrSinAtivoSituacao()
                        ),
                        'gerenciarSituacao' => SessaoSEI::getInstance()->verificarPermissao('andamento_situacao_gerenciar')
                    );
                }
                if($andamentoMarcadorDTO){
                    $arrAndamentoMarcador = array(
                        'marcador' => MarcadorINT::formatarMarcadorDesativado(
                            $andamentoMarcadorDTO->getStrNomeMarcador(),
                            $andamentoMarcadorDTO->getStrSinAtivoMarcador()
                        ),
                        'gerenciarMarcador' => SessaoSEI::getInstance()->verificarPermissao('andamento_marcador_gerenciar')
                    );
                }

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
                    'atributos' => array(
                        'anotacao' => $arrAnotacao,
                        'processoBloquado' => $procedimentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO ? true : false,
                        'remocaoSobrestamento' => ($acompanhamentoDTO->getNumTipoVisualizacao() == AtividadeRN::$TV_REMOCAO_SOBRESTAMENTO) ? true : false,
                        'documentoAssinadoProcesso' => ($acompanhamentoDTO->getNumTipoVisualizacao() == AtividadeRN::$TV_ATENCAO) ? true : false,
                        'documentoPublicadoProcesso' => ($acompanhamentoDTO->getNumTipoVisualizacao() == AtividadeRN::$TV_PUBLICACAO) ? true : false,
                        'retornoProgramado' => $arrRetornoProgramado,
                        'andamentoSituacao' => $arrAndamentoSituacao,
                        'andamentoMarcador' => $arrAndamentoMarcador,

                    )
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $acompanhamentoDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
}