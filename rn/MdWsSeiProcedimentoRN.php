<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiProcedimentoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna o total de unidades do processo
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    protected function listarUnidadesProcessoConectado(ProtocoloDTO $protocoloDTO){
        try{
            if(!$protocoloDTO->getDblIdProtocolo()){
                throw new InfraException('Protocolo não informado.');
            }
            $result = array();

            $relProtocoloProtocoloDTOConsulta = new RelProtocoloProtocoloDTO();
            $relProtocoloProtocoloDTOConsulta->setDblIdProtocolo1($protocoloDTO->getDblIdProtocolo());
            $relProtocoloProtocoloDTOConsulta->retDblIdProtocolo1();
            $relProtocoloProtocoloDTOConsulta->setNumMaxRegistrosRetorno(1);
            $relProtocoloProtocoloDTOConsulta->setNumPaginaAtual(0);
            $relProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            $ret = $relProtocoloProtocoloRN->listarRN0187($relProtocoloProtocoloDTOConsulta);
            if($ret){
                /** @var RelProtocoloProtocoloDTO $relProtocoloProtocoloDTO */
                $relProtocoloProtocoloDTO = $ret[0];
                $result['processo'] = $relProtocoloProtocoloDTO->getDblIdProtocolo1();
                $result['unidades'] = $relProtocoloProtocoloDTOConsulta->getNumTotalRegistros();
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que lista o sobrestamento de um processo
     * @param AtividadeDTO $atividadeDTOParam
     * @return array
     */
    protected function listarSobrestamentoProcessoConectado(AtividadeDTO $atividadeDTOParam){
        try{
            if(!$atividadeDTOParam->isSetDblIdProtocolo()){
                throw new InfraException('Protocolo não informado.');
            }
            if(!$atividadeDTOParam->isSetNumIdUnidade()){
                $atividadeDTOParam->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            }

            $result = array();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->retTodos();
            $atividadeDTOConsulta->setDblIdProtocolo($atividadeDTOParam->getDblIdProtocolo());
            $atividadeDTOConsulta->setDthConclusao(null);
            $atividadeDTOConsulta->setNumIdTarefa(TarefaRN::$TI_SOBRESTAMENTO);
            $atividadeDTOConsulta->setNumMaxRegistrosRetorno(1);
            $atividadeRN = new AtividadeRN();
            $ret = $atividadeRN->listarRN0036($atividadeDTOConsulta);

            /** @var AtividadeDTO $atividadeDTO */
            foreach($ret as $atividadeDTO){
                $result[] = array(
                    'idAtividade' => $atividadeDTO->getNumIdAtividade(),
                    'idProtocolo' => $atividadeDTO->getDblIdProtocolo(),
                    'dthAbertura' => $atividadeDTO->getDthAbertura(),
                    'sinInicial' => $atividadeDTO->getStrSinInicial(),
                    'dtaPrazo' => $atividadeDTO->getDtaPrazo(),
                    'tipoVisualizacao' => $atividadeDTO->getNumTipoVisualizacao(),
                    'dthConclusao' => null,
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo de sobrestamento de processo
     * @param EntradaSobrestarProcessoAPI $entradaSobrestarProcessoAPI
     * @return array
     */
    protected function sobrestamentoProcessoControlado(EntradaSobrestarProcessoAPI $entradaSobrestarProcessoAPI){
        try{
            $seiRN = new SeiRN();
            $seiRN->sobrestarProcesso($entradaSobrestarProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo sobrestado com sucesso');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * @param $protocolo
     * @return array
     */
    protected function removerSobrestamentoProcessoControlado(ProcedimentoDTO $procedimentoDTOParam){
        try{
            if(!$procedimentoDTOParam->getDblIdProcedimento()){
                throw new InfraException('Procedimento n?o informado.');
            }
            $seiRN = new SeiRN();
            $entradaRemoverSobrestamentoProcessoAPI = new EntradaRemoverSobrestamentoProcessoAPI();
            $entradaRemoverSobrestamentoProcessoAPI->setIdProcedimento($procedimentoDTOParam->getDblIdProcedimento());

            $seiRN->removerSobrestamentoProcesso($entradaRemoverSobrestamentoProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Sobrestar cancelado com sucesso.');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que retorna os procedimentos com acompanhamento
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOConsulta
     * @return array
     */
    protected function listarProcedimentoAcompanhamentoConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam) {
        try{
            $usuarioAtribuicaoAtividade = null;
            $mdWsSeiProtocoloDTOConsulta = new MdWsSeiProtocoloDTO();
            if($mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()){
                $mdWsSeiProtocoloDTOConsulta->setNumIdGrupoAcompanhamentoProcedimento($mdWsSeiProtocoloDTOParam->getNumIdGrupoAcompanhamentoProcedimento());
            }

            if(!$mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioGeradorAcompanhamento()){
                $mdWsSeiProtocoloDTOConsulta->setNumIdUsuarioGeradorAcompanhamento(SessaoSEI::getInstance()->getNumIdUsuario());
            }else{
                $mdWsSeiProtocoloDTOConsulta->setNumIdUsuarioGeradorAcompanhamento($mdWsSeiProtocoloDTOParam->getNumIdUsuarioGeradorAcompanhamento());
            }

            if(is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())){
                $mdWsSeiProtocoloDTOConsulta->setNumPaginaAtual(0);
            }else{
                $mdWsSeiProtocoloDTOConsulta->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            }

            if(!$mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()){
                $mdWsSeiProtocoloDTOConsulta->setNumMaxRegistrosRetorno(10);
            }else{
                $mdWsSeiProtocoloDTOConsulta->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            }

            $protocoloRN = new ProtocoloRN();
            $mdWsSeiProtocoloDTOConsulta->retTodos();
            $mdWsSeiProtocoloDTOConsulta->retDblIdProtocolo();
            $mdWsSeiProtocoloDTOConsulta->retStrNomeTipoProcedimentoProcedimento();
            $mdWsSeiProtocoloDTOConsulta->retStrSiglaUnidadeGeradora();
            $mdWsSeiProtocoloDTOConsulta->retStrSinCienciaProcedimento();
            $mdWsSeiProtocoloDTOConsulta->setOrdDthGeracaoAcompanhamento(InfraDTO::$TIPO_ORDENACAO_ASC);
            $mdWsSeiProtocoloDTOConsulta->retStrNomeTipoProcedimentoProcedimento();

            $ret = $protocoloRN->listarRN0668($mdWsSeiProtocoloDTOConsulta);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $mdWsSeiProtocoloDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que retorna os procedimentos com acompanhamento com filtro opcional de grupo de acompanhamento e protocolo
     * formatado
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam
     * @return array
     */
    protected function pesquisarProcedimentoConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam) {
        try{
            $usuarioAtribuicaoAtividade = null;
            $mdWsSeiProtocoloDTOConsulta = new MdWsSeiProtocoloDTO();
            $mdWsSeiProtocoloDTOConsulta->retDblIdProtocolo();
            $mdWsSeiProtocoloDTOConsulta->retTodos();
            $mdWsSeiProtocoloDTOConsulta->retStrNomeTipoProcedimentoProcedimento();
            $mdWsSeiProtocoloDTOConsulta->retStrSiglaUnidadeGeradora();
            $mdWsSeiProtocoloDTOConsulta->retStrSinCienciaProcedimento();

            if($mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()){
                $mdWsSeiProtocoloDTOConsulta->setNumIdGrupoAcompanhamentoProcedimento(
                    $mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()
                );
            }
            if($mdWsSeiProtocoloDTOParam->isSetStrProtocoloFormatadoPesquisa()){
                $strProtocoloFormatado = InfraUtil::retirarFormatacao(
                        $mdWsSeiProtocoloDTOParam->getStrProtocoloFormatadoPesquisa(), false
                    );
                $mdWsSeiProtocoloDTOConsulta->setStrProtocoloFormatadoPesquisa(
                    '%'.$strProtocoloFormatado.'%',
                    InfraDTO::$OPER_LIKE
                );
            }

            if(is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())){
                $mdWsSeiProtocoloDTOConsulta->setNumPaginaAtual(0);
            }else{
                $mdWsSeiProtocoloDTOConsulta->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            }

            if(!$mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()){
                $mdWsSeiProtocoloDTOConsulta->setNumMaxRegistrosRetorno(10);
            }else{
                $mdWsSeiProtocoloDTOConsulta->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            }
            $protocoloRN = new ProtocoloRN();
            $ret = $protocoloRN->listarRN0668($mdWsSeiProtocoloDTOConsulta);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $mdWsSeiProtocoloDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que lista os processos
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTO
     * @return array
     */
    protected function listarProcessosConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam){
        try{
            $pesquisaPendenciaDTO = new PesquisaPendenciaDTO();

            $usuarioAtribuicaoAtividade = null;
            if($mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioAtribuicaoAtividade()){
                $usuarioAtribuicaoAtividade = $mdWsSeiProtocoloDTOParam->getNumIdUsuarioAtribuicaoAtividade();
            }

            if(!is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())){
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            }else{
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
            }

            if($mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()){
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno(10);
            }
            if($mdWsSeiProtocoloDTOParam->getStrSinApenasMeus() == 'S'){
                $pesquisaPendenciaDTO->setStrStaTipoAtribuicao('M');
            }

            $atividadeRN = new AtividadeRN();
            $pesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $pesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL,ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
            $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
            $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
            $pesquisaPendenciaDTO->setStrSinCredenciais('S');
            $pesquisaPendenciaDTO->setStrSinSituacoes('S');
            $pesquisaPendenciaDTO->setStrSinMarcadores('S');

            if($mdWsSeiProtocoloDTOParam->getStrSinTipoBusca() == MdWsSeiProtocoloDTO::SIN_TIPO_BUSCA_R){
                $pesquisaPendenciaDTO->setStrSinInicial('N');
                $ret = $atividadeRN->listarPendenciasRN0754($pesquisaPendenciaDTO);
            }else if($mdWsSeiProtocoloDTOParam->getStrSinTipoBusca() == MdWsSeiProtocoloDTO::SIN_TIPO_BUSCA_G){
                $pesquisaPendenciaDTO->setStrSinInicial('S');
                $ret = $atividadeRN->listarPendenciasRN0754($pesquisaPendenciaDTO);
            }else{
                throw new InfraException('O tipo de busca deve ser (R)ecebidos ou (G)erados');
            }
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $pesquisaPendenciaDTO->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }

    }

    /**
     * Metodo que monta o retorno da listagem do processo com base no retorno da consulta
     * @param array $ret
     * @param null $usuarioAtribuicaoAtividade
     * @return array
     */
    private function montaRetornoListagemProcessos(array $ret, $usuarioAtribuicaoAtividade = null){

        $result = array();
        $protocoloRN = new ProtocoloRN();
        foreach($ret as $dto){
            $usuarioAtribuido = null;
            $documentoNovo = 'N';
            $documentoPublicado = 'N';
            $possuiAnotacao = 'N';
            $possuiAnotacaoPrioridade = 'N';
            $usuarioVisualizacao = 'N';
            $tipoVisualizacao = 'N';
            $retornoProgramado = 'N';
            $retornoAtrasado = 'N';
            $protocoloDTO = new MdWsSeiProtocoloDTO();
            if($dto instanceof ProcedimentoDTO){
                $protocoloDTO = new MdWsSeiProtocoloDTO();
                $protocoloDTO->setDblIdProtocolo($dto->getDblIdProcedimento());
                $protocoloDTO->retDblIdProtocolo();
                $protocoloDTO->retNumIdUnidadeGeradora();
                $protocoloDTO->retStrStaProtocolo();
                $protocoloDTO->retStrProtocoloFormatado();
                $protocoloDTO->retStrNomeTipoProcedimentoProcedimento();
                $protocoloDTO->retStrDescricao();
                $protocoloDTO->retStrSiglaUnidadeGeradora();
                $protocoloDTO->retStrStaGrauSigilo();
                $protocoloDTO->retStrStaNivelAcessoLocal();
                $protocoloDTO->retStrStaNivelAcessoGlobal();
                $protocoloDTO->retStrSinCienciaProcedimento();
                $protocoloDTO->retStrStaEstado();
                $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
            }else{
                $protocoloDTO = $dto;
            }

            $processoBloqueado = $protocoloDTO->getStrStaEstado() == ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO ? 'S' : 'N';
            $processoRemocaoSobrestamento = 'N';
            $processoDocumentoIncluidoAssinado = 'N';
            $processoPublicado = 'N';

            $atividadeRN = new AtividadeRN();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $atividadeDTOConsulta->retDblIdProtocolo();
            $atividadeDTOConsulta->retNumIdTarefa();
            $atividadeDTOConsulta->retNumTipoVisualizacao();
            $atividadeDTOConsulta->retStrNomeUsuarioAtribuicao();
            $atividadeDTOConsulta->retNumIdUsuarioVisualizacao();
            $atividadeDTOConsulta->retNumIdAtividade();

            $atividadeDTOConsulta->setNumMaxRegistrosRetorno(1);
            $atividadeDTOConsulta->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);

            $arrAtividades = $atividadeRN->listarRN0036($atividadeDTOConsulta);
            if($arrAtividades){
                /** @var AtividadeDTO $atividadeDTO */
                $atividadeDTO = $arrAtividades[0];
                $documentoNovo = $atividadeDTO->getNumIdTarefa() == 1 ? 'S' : 'N';
                $usuarioAtribuido = $atividadeDTO->getStrNomeUsuarioAtribuicao();
                $tipoVisualizacao = $atividadeDTO->getNumTipoVisualizacao() == 0 ? 'S' : 'N';
                if($atividadeDTO->getNumIdUsuarioVisualizacao() == $usuarioAtribuicaoAtividade){
                    $usuarioVisualizacao = 'S';
                }
            }
            $arrAtividadePendenciaDTO = array();
            if($dto instanceof ProcedimentoDTO){
                $arrAtividadePendenciaDTO = $dto->getArrObjAtividadeDTO();
            }else{
                $pesquisaPendenciaDTO = new PesquisaPendenciaDTO();
                $pesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                $pesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL,ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
                $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
                $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
                $pesquisaPendenciaDTO->setStrSinCredenciais('S');
                $pesquisaPendenciaDTO->setStrSinSituacoes('S');
                $pesquisaPendenciaDTO->setStrSinMarcadores('S');
                $pesquisaPendenciaDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
                $arrProcedimentoDTO = $atividadeRN->listarPendenciasRN0754($pesquisaPendenciaDTO);
                if($arrProcedimentoDTO){
                    $arrAtividadePendenciaDTO = $arrProcedimentoDTO[0]->getArrObjAtividadeDTO();
                }
            }
            if($arrAtividadePendenciaDTO){
                $atividadePendenciaDTO = $arrAtividadePendenciaDTO[0];
                if($atividadePendenciaDTO->getNumTipoVisualizacao()  & AtividadeRN::$TV_REMOCAO_SOBRESTAMENTO){
                    $processoRemocaoSobrestamento = 'S';
                }
                if($atividadePendenciaDTO->getNumTipoVisualizacao()  & AtividadeRN::$TV_ATENCAO){
                    $processoDocumentoIncluidoAssinado = 'S';
                }
                if($atividadePendenciaDTO->getNumTipoVisualizacao()  & AtividadeRN::$TV_PUBLICACAO){
                    $processoPublicado = 'S';
                }
                $retornoProgramadoDTOConsulta = new RetornoProgramadoDTO();
                $retornoProgramadoDTOConsulta->retDblIdProtocoloAtividadeEnvio();
                $retornoProgramadoDTOConsulta->retStrSiglaUnidadeOrigemAtividadeEnvio();
                $retornoProgramadoDTOConsulta->retStrSiglaUnidadeAtividadeEnvio();
                $retornoProgramadoDTOConsulta->retDtaProgramada();
                $retornoProgramadoDTOConsulta->setNumIdUnidadeAtividadeEnvio(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $retornoProgramadoDTOConsulta->setDblIdProtocoloAtividadeEnvio(array_unique(InfraArray::converterArrInfraDTO($arrAtividadePendenciaDTO, 'IdProtocolo')), InfraDTO::$OPER_IN);
                $retornoProgramadoDTOConsulta->setNumIdAtividadeRetorno(null);
                $objRetornoProgramadoRN = new RetornoProgramadoRN();
                $arrRetornoProgramadoDTO = $objRetornoProgramadoRN->listar($retornoProgramadoDTOConsulta);
                if($arrRetornoProgramadoDTO){
                    $retornoProgramado = 'S';
                    $strDataAtual = InfraData::getStrDataAtual();
                    foreach($arrRetornoProgramadoDTO as $retornoProgramadoDTO){
                        $numPrazo = InfraData::compararDatas($strDataAtual,$retornoProgramadoDTO->getDtaProgramada());
                        if($numPrazo < 0){
                            $retornoAtrasado = 'S';
                            $retornoData = array(
                                'date' => $retornoProgramadoDTO->getDtaProgramada(),
                                'unidade' => $retornoProgramadoDTO->getStrSiglaUnidadeOrigemAtividadeEnvio()
                            );

                        }
                    }
                }
            }

            $documentoRN = new DocumentoRN();
            $documentoDTOConsulta = new DocumentoDTO();
            $documentoDTOConsulta->setDblIdProcedimento($protocoloDTO->getDblIdProtocolo());
            $documentoDTOConsulta->retDblIdDocumento();
            $arrDocumentos = $documentoRN->listarRN0008($documentoDTOConsulta);
            if($arrDocumentos){
                $arrIdDocumentos = array();
                /** @var DocumentoDTO $documentoDTO */
                foreach($arrDocumentos as $documentoDTO){
                    $arrIdDocumentos[] = $documentoDTO->getDblIdDocumento();
                }
                $publiacaoRN = new PublicacaoRN();
                $publicacaoDTO = new PublicacaoDTO();
                $publicacaoDTO->retNumIdPublicacao();
                $publicacaoDTO->setNumMaxRegistrosRetorno(1);
                $publicacaoDTO->adicionarCriterio(
                    array('IdDocumento'),
                    array(InfraDTO::$OPER_IN),
                    array($arrIdDocumentos)
                );
                $arrPublicacaoDTO = $publiacaoRN->listarRN1045($publicacaoDTO);
                $documentoPublicado = count($arrPublicacaoDTO) ? 'S' : 'N';
            }
            $anotacaoRN = new AnotacaoRN();
            $anotacaoDTOConsulta = new AnotacaoDTO();
            $anotacaoDTOConsulta->setNumMaxRegistrosRetorno(1);
            $anotacaoDTOConsulta->retDblIdProtocolo();
            $anotacaoDTOConsulta->retStrDescricao();
            $anotacaoDTOConsulta->retNumIdUnidade();
            $anotacaoDTOConsulta->retNumIdUsuario();
            $anotacaoDTOConsulta->retDthAnotacao();
            $anotacaoDTOConsulta->retStrSinPrioridade();
            $anotacaoDTOConsulta->retStrStaAnotacao();
            $anotacaoDTOConsulta->retNumIdAnotacao();
            $anotacaoDTOConsulta->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            //$anotacaoDTOConsulta->setNumIdUnidade($protocoloDTO->getNumIdUnidadeGeradora());
            $arrAnotacao = $anotacaoRN->listar($anotacaoDTOConsulta);
            $possuiAnotacao = count($arrAnotacao) ? 'S' : 'N';
            foreach($arrAnotacao as $anotacaoDTO){
                if($anotacaoDTO->getStrSinPrioridade() == 'S'){
                    $possuiAnotacaoPrioridade = 'S';
                    break;
                }
            }
            $resultAnotacao = array();
            /** @var AnotacaoDTO $anotacaoDTO */
            foreach($arrAnotacao as $anotacaoDTO){
                $resultAnotacao[] = array(
                    'idAnotacao' => $anotacaoDTO->getNumIdAnotacao(),
                    'idProtocolo' => $anotacaoDTO->getDblIdProtocolo(),
                    'descricao' => $anotacaoDTO->getStrDescricao(),
                    'idUnidade' => $anotacaoDTO->getNumIdUnidade(),
                    'idUsuario' => $anotacaoDTO->getNumIdUsuario(),
                    'dthAnotacao' => $anotacaoDTO->getDthAnotacao(),
                    'sinPrioridade' => $anotacaoDTO->getStrSinPrioridade(),
                    'staAnotacao' => $anotacaoDTO->getStrStaAnotacao()
                );
            }

            $result[] = array(
                'id' => $protocoloDTO->getDblIdProtocolo(),
                'status' => $protocoloDTO->getStrStaProtocolo(),
                'atributos' => array(
                    'idProcedimento' => $protocoloDTO->getDblIdProtocolo(),
                    'idProtocolo' => $protocoloDTO->getDblIdProtocolo(),
                    'numero' => $protocoloDTO->getStrProtocoloFormatado(),
                    'tipoProcesso' => $protocoloDTO->getStrNomeTipoProcedimentoProcedimento(),
                    'descricao' => $protocoloDTO->getStrDescricao(),
                    'usuarioAtribuido' => $usuarioAtribuido,
                    'unidade' => array(
                        'idUnidade' => $protocoloDTO->getNumIdUnidadeGeradora(),
                        'sigla' => $protocoloDTO->getStrSiglaUnidadeGeradora()
                    ),
                    'anotacoes' => $resultAnotacao,
                    'status' => array(
                        'documentoSigiloso' => $protocoloDTO->getStrStaGrauSigilo(),
                        'documentoRestrito' => $protocoloDTO->getStrStaNivelAcessoLocal() == 1 ? 'S' : 'N',
                        'documentoNovo' => $documentoNovo,
                        'documentoPublicado' => $documentoPublicado,
                        'anotacao' => $possuiAnotacao,
                        'anotacaoPrioridade' => $possuiAnotacaoPrioridade,//verificar
                        'ciencia' => $protocoloDTO->getStrSinCienciaProcedimento(),
                        'retornoProgramado' => $retornoProgramado,
                        'retornoData' => $retornoData,
                        'retornoAtrasado' => $retornoAtrasado,
                        'processoAcessadoUsuario' => $tipoVisualizacao,
                        // foi invertido o processoAcessadoUsuario e processoAcessadoUnidade,
                        // pois em todos os outros metodos e igual e somente neste era diferente...
                        'processoAcessadoUnidade' => $usuarioVisualizacao,
                        //Novos Status de Processo igual listagem
                        'processoRemocaoSobrestamento' => $processoRemocaoSobrestamento,
                        'processoBloqueado' => $processoBloqueado,
                        'processoDocumentoIncluidoAssinado' => $processoDocumentoIncluidoAssinado,
                        'processoPublicado' => $processoPublicado,
                        'nivelAcessoGlobal' => $protocoloDTO->getStrStaNivelAcessoGlobal()
                    )
                )
            );
        }

        return $result;
    }

    /**
     * Metodo que retorna as ciencias nos processos
     * @param ProtocoloDTO $protocoloDTOParam
     * @return array
     */
    protected function listarCienciaProcessoConectado(ProtocoloDTO $protocoloDTOParam){
        try{
            if(!$protocoloDTOParam->isSetDblIdProtocolo()){
                throw new InfraException('Protocolo não informado.');
            }

            $result = array();
            $mdWsSeiProcessoRN = new MdWsSeiProcessoRN();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->setDblIdProtocolo($protocoloDTOParam->getDblIdProtocolo());
            $atividadeDTOConsulta->setNumIdTarefa(TarefaRN::$TI_PROCESSO_CIENCIA);
            $atividadeDTOConsulta->retDthAbertura();
            $atividadeDTOConsulta->retStrSiglaUnidade();
            $atividadeDTOConsulta->retStrNomeTarefa();
            $atividadeDTOConsulta->retStrSiglaUsuarioOrigem();
            $atividadeDTOConsulta->retNumIdAtividade();
            $atividadeRN = new AtividadeRN();
            $ret = $atividadeRN->listarRN0036($atividadeDTOConsulta);
            /** @var AtividadeDTO $atividadeDTO */
            foreach($ret as $atividadeDTO){
                $mdWsSeiProcessoDTO = new MdWsSeiProcessoDTO();
                $mdWsSeiProcessoDTO->setStrTemplate($atividadeDTO->getStrNomeTarefa());
                $mdWsSeiProcessoDTO->setNumIdAtividade($atividadeDTO->getNumIdAtividade());
                $result[] = array(
                    'data' => $atividadeDTO->getDthAbertura(),
                    'unidade' => $atividadeDTO->getStrSiglaUnidade(),
                    'nome' => $atividadeDTO->getStrSiglaUsuarioOrigem(),
                    'descricao' => $mdWsSeiProcessoRN->traduzirTemplate($mdWsSeiProcessoDTO)
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }


    /**
     * Metodo que da ciencia ao processo/procedimento
     * @param ProcedimentoDTO $procedimentoDTO
     * @info E obrigatorio informar o id do procedimento
     * @return array
     */
    protected function darCienciaControlado(ProcedimentoDTO $procedimentoDTOParam){
        try{
            if(!$procedimentoDTOParam->isSetDblIdProcedimento()){
                throw new InfraException('E obrigatorio informar o procedimento!');
            }

            $procedimentoRN = new ProcedimentoRN();
            $procedimentoRN->darCiencia($procedimentoDTOParam);

            return MdWsSeiRest::formataRetornoSucessoREST('Ciência processo realizado com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que conclui o procedimento/processo
     * @param EntradaConcluirProcessoAPI $entradaConcluirProcessoAPI
     * @info ele recebe o número do ProtocoloProcedimentoFormatadoPesquisa da tabela protocolo
     * @return array
     */
    protected function concluirProcessoControlado(EntradaConcluirProcessoAPI $entradaConcluirProcessoAPI){
        try{
            if(!$entradaConcluirProcessoAPI->getProtocoloProcedimento()){
                throw new InfraException('E obrigtorio informar o protocolo do procedimento!');
            }

            $objSeiRN = new SeiRN();
            $objSeiRN->concluirProcesso($entradaConcluirProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo concluído com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que atribui o processo a uma pessoa
     * @param EntradaAtribuirProcessoAPI $entradaAtribuirProcessoAPI
     * @info Os parametros IdUsuario, ProtocoloProcedimento e SinReabrir sao obrigatorios. O parametro ProtocoloProcedimento
     * recebe o n?mero do ProtocoloProcedimentoFormatadoPesquisa da tabela protocolo
     * @return array
     */
    protected function atribuirProcessoControlado(EntradaAtribuirProcessoAPI $entradaAtribuirProcessoAPI){
        try{
            if(!$entradaAtribuirProcessoAPI->getProtocoloProcedimento()){
                throw new InfraException('E obrigatorio informar o protocolo do processo!');
            }
            if(!$entradaAtribuirProcessoAPI->getIdUsuario()){
                throw new InfraException('E obrigatorio informar o usu?rio do processo!');
            }

            $objSeiRN = new SeiRN();
            $objSeiRN->atribuirProcesso($entradaAtribuirProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo atribuído com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Encapsula o objeto ENtradaEnviarProcessoAPI para o metodo enviarProcesso
     * @param array $post
     * @return EntradaEnviarProcessoAPI
     */
    public function encapsulaEnviarProcessoEntradaEnviarProcessoAPI(array $post){
        $entradaEnviarProcessoAPI = new EntradaEnviarProcessoAPI();
        if(isset($post['numeroProcesso'])){
            $entradaEnviarProcessoAPI->setProtocoloProcedimento($post['numeroProcesso']);
        }
        if(isset($post['unidadesDestino'])){
            $entradaEnviarProcessoAPI->setUnidadesDestino(explode(',', $post['unidadesDestino']));
        }
        if(isset($post['sinManterAbertoUnidade'])){
            $entradaEnviarProcessoAPI->setSinManterAbertoUnidade($post['sinManterAbertoUnidade']);
        }
        if(isset($post['sinRemoverAnotacao'])){
            $entradaEnviarProcessoAPI->setSinRemoverAnotacao($post['sinRemoverAnotacao']);
        }
        if(isset($post['sinEnviarEmailNotificacao'])){
            $entradaEnviarProcessoAPI->setSinEnviarEmailNotificacao($post['sinEnviarEmailNotificacao']);
        }else{
            $entradaEnviarProcessoAPI->setSinEnviarEmailNotificacao('N');
        }
        if(isset($post['dataRetornoProgramado'])){
            $entradaEnviarProcessoAPI->setDataRetornoProgramado($post['dataRetornoProgramado']);
        }
        if(isset($post['diasRetornoProgramado'])){
            $entradaEnviarProcessoAPI->setDiasRetornoProgramado($post['diasRetornoProgramado']);
        }
        if(isset($post['sinDiasUteisRetornoProgramado'])){
            $entradaEnviarProcessoAPI->setSinDiasUteisRetornoProgramado($post['sinDiasUteisRetornoProgramado']);
        }
        if(isset($post['sinReabrir'])){
            $entradaEnviarProcessoAPI->setSinReabrir($post['sinReabrir']);
        }

        return $entradaEnviarProcessoAPI;
    }

    /**
     * Metodo que envia o processo para outra unidade
     * @param EntradaEnviarProcessoAPI $entradaEnviarProcessoAPI
     * @info Metodo auxiliar para encapsular dados encapsulaEnviarProcessoEntradaEnviarProcessoAPI
     * @return array
     */
    protected function enviarProcessoControlado(EntradaEnviarProcessoAPI $entradaEnviarProcessoAPI){
        try{
            $objSeiRN = new SeiRN();
            $objSeiRN->enviarProcesso($entradaEnviarProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo enviado com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }



}