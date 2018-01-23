<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiDocumentoRN extends DocumentoRN {

    CONST NOME_ATRIBUTO_ANDAMENTO_DOCUMENTO = 'DOCUMENTO';

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Método que retorna os documentos de um processo
     * @param DocumentoDTO $documentoDTOParam
     * @return array
     */
    protected function listarDocumentosProcessoConectado(DocumentoDTO $documentoDTOParam){
        try{
            $arrDocHtml = array(
                DocumentoRN::$TD_EDITOR_EDOC,
                DocumentoRN::$TD_FORMULARIO_AUTOMATICO,
                DocumentoRN::$TD_FORMULARIO_GERADO,
                DocumentoRN::$TD_EDITOR_INTERNO
            );
            $result = array();
            $relProtocoloProtocoloDTOConsulta = new RelProtocoloProtocoloDTO();
            if(!$documentoDTOParam->isSetDblIdProcedimento()){
                throw new InfraException('O procedimento deve ser informado.');
            }
            $relProtocoloProtocoloDTOConsulta->setDblIdProtocolo1($documentoDTOParam->getDblIdProcedimento());
            $relProtocoloProtocoloDTOConsulta->setStrStaProtocoloProtocolo2(
                array(ProtocoloRN::$TP_DOCUMENTO_GERADO, ProtocoloRN::$TP_DOCUMENTO_RECEBIDO),
                InfraDTO::$OPER_IN
            );
            $relProtocoloProtocoloDTOConsulta->retStrSinCiencia();
            $relProtocoloProtocoloDTOConsulta->retDblIdProtocolo1();
            $relProtocoloProtocoloDTOConsulta->retDblIdProtocolo2();
            $relProtocoloProtocoloDTOConsulta->retNumSequencia();
            $relProtocoloProtocoloDTOConsulta->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);
            if($documentoDTOParam->getNumMaxRegistrosRetorno()){
                $relProtocoloProtocoloDTOConsulta->setNumMaxRegistrosRetorno($documentoDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $relProtocoloProtocoloDTOConsulta->setNumMaxRegistrosRetorno(10);
            }
            if(is_null($documentoDTOParam->getNumPaginaAtual())){
                $relProtocoloProtocoloDTOConsulta->setNumPaginaAtual(0);
            }else{
                $relProtocoloProtocoloDTOConsulta->setNumPaginaAtual($documentoDTOParam->getNumPaginaAtual());
            }

            $relProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            $ret = $relProtocoloProtocoloRN->listarRN0187($relProtocoloProtocoloDTOConsulta);
            $arrDocumentos = array();
            if($ret){
                $unidadeDTO = new UnidadeDTO();
                $unidadeDTO->setBolExclusaoLogica(false);
                $unidadeDTO->retStrSinProtocolo();
                $unidadeDTO->retStrSinOuvidoria();
                $unidadeDTO->retStrSinArquivamento();
                $unidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

                $unidadeRN = new UnidadeRN();
                $unidadeDTO = $unidadeRN->consultarRN0125($unidadeDTO);
                $bolFlagProtocolo = ($unidadeDTO->getStrSinProtocolo() == 'S');

                $documentoDTOConsulta = new DocumentoDTO();
                $documentoDTOConsulta->retStrStaNivelAcessoLocalProtocolo();
                $documentoDTOConsulta->retDblIdDocumento();
                $documentoDTOConsulta->retStrStaProtocoloProtocolo();
                $documentoDTOConsulta->retDblIdProcedimento();
                $documentoDTOConsulta->retStrProtocoloDocumentoFormatado();
                $documentoDTOConsulta->retStrNumero();
                $documentoDTOConsulta->retStrStaDocumento();
                $documentoDTOConsulta->retNumIdSerie();
                $documentoDTOConsulta->retStrNomeSerie();
                $documentoDTOConsulta->retStrSiglaUnidadeGeradoraProtocolo();
                $documentoDTOConsulta->retStrSiglaUnidadeGeradoraProtocolo();
                $documentoDTOConsulta->retNumIdUnidadeGeradoraProtocolo();
                $documentoDTOConsulta->retStrCrcAssinatura();
                $documentoDTOConsulta->retStrStaEstadoProtocolo();
                $documentoDTOConsulta->setDblIdDocumento(array_keys(InfraArray::indexarArrInfraDTO($ret,'IdProtocolo2')), InfraDTO::$OPER_IN);
                $documentoBD = new DocumentoBD($this->getObjInfraIBanco());
                $retDocumentos = $documentoBD->listar($documentoDTOConsulta);
                /** @var DocumentoDTO $documentoDTOOrd */
                foreach ($retDocumentos as $documentoDTOOrd){
                    $arrDocumentos[$documentoDTOOrd->getDblIdDocumento()] = $documentoDTOOrd;
                }
            }

            $anexoRN = new AnexoRN();
            $observacaoRN = new ObservacaoRN();
            $publicacaoRN = new PublicacaoRN();
            /** @var RelProtocoloProtocoloDTO $relProtocoloProtocoloDTO */
            foreach($ret as $relProtocoloProtocoloDTO){
                $documentoDTO = $arrDocumentos[$relProtocoloProtocoloDTO->getDblIdProtocolo2()];
                $mimetype = null;
                $nomeAnexo = null;
                $informacao = null;
                $tamanhoAnexo = null;
                $ciencia = 'N';
                $documentoCancelado = $documentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_DOCUMENTO_CANCELADO
                    ? 'S' : 'N';

                if(!in_array($documentoDTO->getStrStaDocumento(), $arrDocHtml)){
                    $anexoDTOConsulta = new AnexoDTO();
                    $anexoDTOConsulta->retStrNome();
                    $anexoDTOConsulta->retNumTamanho();
                    $anexoDTOConsulta->setDblIdProtocolo($documentoDTO->getDblIdDocumento());
                    $anexoDTOConsulta->setStrSinAtivo('S');
                    $anexoDTOConsulta->setNumMaxRegistrosRetorno(1);
                    $resultAnexo = $anexoRN->listarRN0218($anexoDTOConsulta);
                    if($resultAnexo){
                        /** @var AnexoDTO $anexoDTO */
                        $anexoDTO = $resultAnexo[0];
                        $mimetype = $anexoDTO->getStrNome();
                        $mimetype = substr($mimetype, strrpos($mimetype, '.')+1);
                        $nomeAnexo = $anexoDTO->getStrNome();
                        $tamanhoAnexo = $anexoDTO->getNumTamanho();
                    }
                }
                $observacaoDTOConsulta = new ObservacaoDTO();
                $observacaoDTOConsulta->setNumMaxRegistrosRetorno(1);
                $observacaoDTOConsulta->setOrdNumIdObservacao(InfraDTO::$TIPO_ORDENACAO_DESC);
                $observacaoDTOConsulta->retStrDescricao();
                $resultObservacao = $observacaoRN->listarRN0219($observacaoDTOConsulta);
                if($resultObservacao){
                    /** @var ObservacaoDTO $observacaoDTO */
                    $observacaoDTO = $resultObservacao[0];
                    $informacao = substr($observacaoDTO->getStrDescricao(), 0, 250);
                }
                $publicacaoDTOConsulta = new PublicacaoDTO();
                $publicacaoDTOConsulta->setDblIdDocumento($documentoDTO->getDblIdDocumento());
                $publicacaoDTOConsulta->retDblIdDocumento();
                $publicacaoDTOConsulta->setNumMaxRegistrosRetorno(1);
                $resultPublicacao = $publicacaoRN->listarRN1045($publicacaoDTOConsulta);
                $documentoPublicado = $resultPublicacao ? 'S' : 'N';
                $ciencia = $relProtocoloProtocoloDTO->getStrSinCiencia();
                $podeVisualizarDocumento = $this->podeVisualizarDocumento($documentoDTO, $bolFlagProtocolo);

                $result[] = array(
                    'id' => $documentoDTO->getDblIdDocumento(),
                    'atributos' => array(
                        'idProcedimento' => $documentoDTO->getDblIdProcedimento(),
                        'idProtocolo' => $documentoDTO->getDblIdDocumento(),
                        'protocoloFormatado' => $documentoDTO->getStrProtocoloDocumentoFormatado(),
                        'nome' => $nomeAnexo,
                        'titulo' => $documentoDTO->getStrNumero(),
                        'tipo' => $documentoDTO->getStrNomeSerie(),
                        'mimeType' => $mimetype ? $mimetype : 'html',
                        'informacao' => $informacao,
                        'tamanho' => $tamanhoAnexo,
                        'idUnidade' => $documentoDTO->getNumIdUnidadeGeradoraProtocolo(),
                        'siglaUnidade' => $documentoDTO->getStrSiglaUnidadeGeradoraProtocolo(),
                        'status' => array(
                            'sinBloqueado' => $documentoDTO->getStrStaNivelAcessoLocalProtocolo() == 1 ? 'S' : 'N',
                            'documentoSigiloso' => $documentoDTO->getStrStaNivelAcessoLocalProtocolo() == 2 ? 'S' : 'N',
                            'documentoRestrito' => $documentoDTO->getStrStaNivelAcessoLocalProtocolo() == 1 ? 'S' : 'N',
                            'documentoPublicado' => $documentoPublicado,
                            'documentoAssinado' =>  $documentoDTO->getStrCrcAssinatura() ? 'S' : 'N',
                            'ciencia' => $ciencia,
                            'documentoCancelado' => $documentoCancelado,
                            'podeVisualizarDocumento' => $podeVisualizarDocumento ? 'S' : 'N'
                        )
                    )
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $relProtocoloProtocoloDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo simplificado (abstraido) de assinatura de documentos
     * @param string $arrIdDocumento
     * @param int $idOrgao
     * @param string $strCargoFuncao
     * @param string $siglaUsuario
     * @param string $senhaUsuario
     * @param int $idUsuario
     * @return array
     */
        public function apiAssinarDocumentos($arrIdDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario){
        //transforma os dados no array
        if(strpos($arrIdDocumento, ',') !== false) {
            $arrDocs = explode(',', $arrIdDocumento);
        } else {
            $arrDocs = array($arrIdDocumento);
        }
        
        foreach($arrDocs as $dblIdDocumento){
            $documentoDTO = new DocumentoDTO();
            $documentoDTO->setDblIdDocumento($dblIdDocumento);
            $arrDocumentoDTO[] = $documentoDTO;
        }
        $assinaturaDTO = new AssinaturaDTO();
        $assinaturaDTO->setStrSiglaUsuario($siglaUsuario);
        $assinaturaDTO->setStrSenhaUsuario($senhaUsuario);
        $assinaturaDTO->setNumIdUsuario($idUsuario);
        $assinaturaDTO->setNumIdOrgaoUsuario($idOrgao);
        $assinaturaDTO->setStrCargoFuncao($strCargoFuncao);
        $assinaturaDTO->setArrObjDocumentoDTO($arrDocumentoDTO);
        return $this->assinarDocumento($assinaturaDTO);
    }

    /**
     * Metodo simplificado (abstraido) de assinatura de documento
     * @param array $arrIdDocumento
     * @param $idOrgao
     * @param $strCargoFuncao
     * @param $siglaUsuario
     * @param $senhaUsuario
     * @param $idUsuario
     * @return array
     */
    public function apiAssinarDocumento($idDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario){
        $arrDocumentoDTO = array();
        $documentoDTO = new DocumentoDTO();
        $documentoDTO->setDblIdDocumento($idDocumento);
        $arrDocumentoDTO[] = $documentoDTO;
        $assinaturaDTO = new AssinaturaDTO();
        $assinaturaDTO->setStrSiglaUsuario($siglaUsuario);
        $assinaturaDTO->setStrSenhaUsuario($senhaUsuario);
        $assinaturaDTO->setNumIdUsuario($idUsuario);
        $assinaturaDTO->setNumIdOrgaoUsuario($idOrgao);
        $assinaturaDTO->setStrCargoFuncao($strCargoFuncao);
        $assinaturaDTO->setArrObjDocumentoDTO($arrDocumentoDTO);
        return $this->assinarDocumento($assinaturaDTO);
    }

    /**
     * Realizar Assinatura Eletr?nica
     * @param AssinaturaDTO $assinaturaDTO
     * @return array
     */
    public function assinarDocumentoControlado(AssinaturaDTO $assinaturaDTO){
        try{
            $assinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
            $assinaturaDTO->setNumIdContextoUsuario(null);
            $documentoRN = new DocumentoRN();
            $documentoRN->assinarInterno($assinaturaDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Documento em bloco assinado com sucesso.');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * @param DocumentoDTO $documentoDTO
     *   id documento obrigatorio
     * @return array
     */
    protected function darCienciaControlado(DocumentoDTO $documentoDTO){
        try{
            $documentoRN = new DocumentoRN();
            if(!$documentoDTO->isSetDblIdDocumento()){
                throw new InfraException('O documento não foi informado.');
            }
            $documentoRN->darCiencia($documentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Ciência documento realizado com sucesso.');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    protected function downloadAnexoConectado(ProtocoloDTO $protocoloDTOParam){
        try{
            if(!$protocoloDTOParam->isSetDblIdProtocolo() || !$protocoloDTOParam->getDblIdProtocolo()){
                throw new InfraException('O protocolo deve ser informado!');
            }
            $documentoDTOConsulta = new DocumentoDTO();
            $documentoDTOConsulta->setDblIdProtocoloProtocolo($protocoloDTOParam->getDblIdProtocolo());
            $documentoDTOConsulta->retDblIdDocumento();
            $documentoDTOConsulta->retStrNomeSerie();
            $documentoDTOConsulta->retStrNumero();
            $documentoDTOConsulta->retStrSiglaUnidadeGeradoraProtocolo();
            $documentoDTOConsulta->retStrProtocoloDocumentoFormatado();
            $documentoDTOConsulta->retStrProtocoloProcedimentoFormatado();
            $documentoDTOConsulta->retStrStaProtocoloProtocolo();
            $documentoDTOConsulta->retStrStaDocumento();
            $documentoDTOConsulta->retDblIdDocumentoEdoc();
            $documentoRN = new DocumentoRN();
            $documentoDTO = $documentoRN->consultarRN0005($documentoDTOConsulta);

            if ($documentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_EDOC) {
                if ($documentoDTO->getDblIdDocumentoEdoc() == null) {
                    throw new InfraException('Documento sem conteúdo!');
                }
                $eDocRN = new EDocRN();
                $html = $eDocRN->consultarHTMLDocumentoRN1204($documentoDTO);

                return MdWsSeiRest::formataRetornoSucessoREST(null, array('html' => $html));
            }else if(in_array($documentoDTO->getStrStaDocumento(), array(DocumentoRN::$TD_FORMULARIO_AUTOMATICO, DocumentoRN::$TD_FORMULARIO_GERADO))) {
                $html = $documentoRN->consultarHtmlFormulario($documentoDTO);

                return MdWsSeiRest::formataRetornoSucessoREST(null, array('html' => $html));
            }else if($documentoDTO->getStrStaDocumento() == DocumentoRN::$TD_EDITOR_INTERNO){
                $editorDTOConsulta = new EditorDTO();
                $editorDTOConsulta->setDblIdDocumento($documentoDTO->getDblIdDocumento());
                $editorDTOConsulta->setNumIdBaseConhecimento(null);
                $editorDTOConsulta->setStrSinCabecalho('S');
                $editorDTOConsulta->setStrSinRodape('S');
                $editorDTOConsulta->setStrSinIdentificacaoVersao('S');
                $editorDTOConsulta->setStrSinProcessarLinks('S');

                if(MdWsSeiEditorRN::versaoCarimboPublicacaoObrigatorio()){
                    $editorDTOConsulta->setStrSinCarimboPublicacao('N');
                }

                $editorRN = new EditorRN();
                $html = $editorRN->consultarHtmlVersao($editorDTOConsulta);

                $auditoriaProtocoloDTO = new AuditoriaProtocoloDTO();
                $auditoriaProtocoloDTO->setStrRecurso('documento_visualizar');
                $auditoriaProtocoloDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                $auditoriaProtocoloDTO->setDblIdProtocolo($documentoDTO->getDblIdDocumento());
                $auditoriaProtocoloDTO->setNumIdAnexo(null);
                $auditoriaProtocoloDTO->setDtaAuditoria(InfraData::getStrDataAtual());
                $auditoriaProtocoloDTO->setNumVersao($editorDTOConsulta->getNumVersao());

                $auditoriaProtocoloRN = new AuditoriaProtocoloRN();
                $auditoriaProtocoloRN->auditarVisualizacao($auditoriaProtocoloDTO);

                return MdWsSeiRest::formataRetornoSucessoREST(null, array('html' => $html));
            }else{
                $anexoDTO = new AnexoDTO();
                $anexoDTO->retNumIdAnexo();
                $anexoDTO->retDthInclusao();
                $anexoDTO->retDthInclusao();
                $anexoDTO->retStrNome();
                $anexoDTO->retStrHash();
                $anexoDTO->setDblIdProtocolo($protocoloDTOParam->getDblIdProtocolo());
                $anexoDTO->setStrSinAtivo('S');
                $anexoRN = new AnexoRN();
                $resultAnexo = $anexoRN->listarRN0218($anexoDTO);
                if(empty($resultAnexo)){
                    throw new InfraException('Documento não encontrado!');
                }
                $anexo = $resultAnexo[0];
                SeiINT::download($anexo);
                exit;
            }
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Listar Ciencias realizadas em um Documento
     * @param MdWsSeiProcessoDTO $mdWsSeiProcessoDTOParam
     *   *valor = protocoloformatado?
     * @return array
     */
    protected function listarCienciaDocumentoConectado(MdWsSeiProcessoDTO $mdWsSeiProcessoDTOParam){
        try{
            if(!$mdWsSeiProcessoDTOParam->isSetStrValor()){
                throw new InfraException('Número do documento não informado.');
            }

            $result = array();
            $atributoAndamentoDTOConsulta = new AtributoAndamentoDTO();
            $atributoAndamentoDTOConsulta->retTodos();
            $atributoAndamentoDTOConsulta->retDthAberturaAtividade();
            $atributoAndamentoDTOConsulta->retStrSiglaUsuarioOrigemAtividade();
            $atributoAndamentoDTOConsulta->retStrSiglaUnidadeOrigemAtividade();
            $atributoAndamentoDTOConsulta->setNumIdTarefaAtividade(TarefaRN::$TI_DOCUMENTO_CIENCIA);
            $atributoAndamentoDTOConsulta->setStrValor($mdWsSeiProcessoDTOParam->getStrValor());
            $atributoAndamentoDTOConsulta->setStrNome(self::NOME_ATRIBUTO_ANDAMENTO_DOCUMENTO);
            $atributoAndamentoRN = new AtributoAndamentoRN();
            $ret = $atributoAndamentoRN->listarRN1367($atributoAndamentoDTOConsulta);
            $tarefaDTO = new TarefaDTO();
            $tarefaDTO->retStrNome();
            $tarefaDTO->setNumIdTarefa($atributoAndamentoDTOConsulta->getNumIdTarefaAtividade());
            $tarefaRN = new TarefaRN();
            $tarefaDTO = $tarefaRN->consultar($tarefaDTO);
            $mdWsSeiProcessoRN = new MdWsSeiProcessoRN();
            /** @var AtributoAndamentoDTO $atributoAndamentoDTO */
            foreach($ret as $atributoAndamentoDTO){
                $mdWsSeiProcessoDTO = new MdWsSeiProcessoDTO();
                $mdWsSeiProcessoDTO->setNumIdAtividade($atributoAndamentoDTO->getNumIdAtividade());
                $mdWsSeiProcessoDTO->setStrTemplate($tarefaDTO->getStrNome());
                $result[] = array(
                    'data' => $atributoAndamentoDTO->getDthAberturaAtividade(),
                    'unidade' => $atributoAndamentoDTO->getStrSiglaUnidadeOrigemAtividade(),
                    'nome' => $atributoAndamentoDTO->getStrSiglaUsuarioOrigemAtividade(),
                    'descricao' => $mdWsSeiProcessoRN->traduzirTemplate($mdWsSeiProcessoDTO)
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Listar assinaturas do documento
     * @param DocumentoDTO $documentoDTOParam
     * @return array
     */
    protected function listarAssinaturasDocumentoConectado(DocumentoDTO $documentoDTOParam){
        try{
            if(!$documentoDTOParam->isSetDblIdDocumento()){
                throw new InfraException('O documento não foi informado.');
            }

            $result = array();
            $assinaturaDTOConsulta = new AssinaturaDTO();
            $assinaturaDTOConsulta->retTodos();
            $assinaturaDTOConsulta->retStrSiglaUnidade();
            $assinaturaDTOConsulta->setDblIdDocumento($documentoDTOParam->getDblIdDocumento());
            $assinaturaRN = new AssinaturaRN();
            $ret = $assinaturaRN->listarRN1323($assinaturaDTOConsulta);
            /** @var AssinaturaDTO $assinaturaDTO */
            foreach($ret as $assinaturaDTO){
                $result[] = array(
                    'nome' => $assinaturaDTO->getStrNome(),
                    'cargo' => $assinaturaDTO->getStrTratamento(),
                    'unidade' => $assinaturaDTO->getStrSiglaUnidade()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Verifica se o documento pode ser visualizado
     * @param DocumentoDTO $documentoDTO
     * @param bool $bolFlagProtocolo
     * @return bool
     */
    protected function podeVisualizarDocumento(DocumentoDTO $documentoDTO, $bolFlagProtocolo = false)
    {
        $podeVisualizar = false;
        $pesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $pesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_TODOS);
        $pesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_TODOS);
        $pesquisaProtocoloDTO->setDblIdProtocolo($documentoDTO->getDblIdDocumento());
        $protocoloRN = new ProtocoloRN();
        $arrProtocoloDTO = InfraArray::indexarArrInfraDTO($protocoloRN->pesquisarRN0967($pesquisaProtocoloDTO), 'IdProtocolo');
        $protocoloDTODocumento = $arrProtocoloDTO[$documentoDTO->getDblIdDocumento()];

        $numCodigoAcesso = $protocoloDTODocumento->getNumCodigoAcesso();
        if ($numCodigoAcesso > 0) {
            $podeVisualizar = true;
        }
        if ($documentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_DOCUMENTO_CANCELADO) {
            $podeVisualizar = false;
        }

        return $podeVisualizar;
    }
}