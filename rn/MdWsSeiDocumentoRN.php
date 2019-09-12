<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdWsSeiDocumentoRN extends DocumentoRN
{

    CONST NOME_ATRIBUTO_ANDAMENTO_DOCUMENTO = 'DOCUMENTO';

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    /**
     * Retorna o documento interno no formato HTML
     * @param DocumentoDTO $documentoDTOParam
     * @return array
     */
    protected function visualizarInternoConectado(DocumentoDTO $documentoDTOParam)
    {
        try{
            $strAcaoSeiCorrespondente = 'documento_visualizar';
            $result = '';
            if(!$documentoDTOParam->isSetDblIdDocumento() || !$documentoDTOParam->getDblIdDocumento()){
                throw new Exception('Documento não informado.');
            }

            if(!$this->verificarAcessoProtocolo($documentoDTOParam->getDblIdDocumento())){
                throw new InfraException("Acesso ao documento " . $documentoDTOParam->getDblIdDocumento() . " não autorizado.");
            }

            $documentoDTOParam->retDblIdDocumento();
            $documentoDTOParam->retStrNomeSerie();
            $documentoDTOParam->retStrNumero();
            $documentoDTOParam->retStrSiglaUnidadeGeradoraProtocolo();
            $documentoDTOParam->retStrProtocoloDocumentoFormatado();
            $documentoDTOParam->retStrStaProtocoloProtocolo();
            $documentoDTOParam->retStrStaDocumento();
            $documentoDTOParam->retDblIdDocumentoEdoc();

            $documentoRN = new DocumentoRN();
            /** Chama o componente SEI para consulta do Documento */
            $documentoDTOParam = $documentoRN->consultarRN0005($documentoDTOParam);

            if(!$documentoDTOParam){
                throw new Exception('Documento não encontrado.');
            }

            if ($documentoDTOParam->getStrStaDocumento() == DocumentoRN::$TD_EDITOR_EDOC) {

                if ($documentoDTOParam->getDblIdDocumentoEdoc() == null) {
                    throw new Exception('Documento sem conteúdo.');
                }

                $objEDocRN = new EDocRN();
                /** Chama o componente SEI para retornar o conteúdo HTML do documento do tipo EDOC */
                $strResultado = $objEDocRN->consultarHTMLDocumentoRN1204($documentoDTOParam);
                $result = $strResultado;
            } else if ($documentoDTOParam->getStrStaDocumento() == DocumentoRN::$TD_EDITOR_INTERNO) {
                $editorDTO = new EditorDTO();
                $editorDTO->setDblIdDocumento($documentoDTOParam->getDblIdDocumento());
                $editorDTO->setNumIdBaseConhecimento(null);
                $editorDTO->setStrSinCabecalho('S');
                $editorDTO->setStrSinRodape('S');
                $editorDTO->setStrSinIdentificacaoVersao('S');
                $editorDTO->setStrSinProcessarLinks('S');

                if (MdWsSeiEditorRN::versaoCarimboPublicacaoObrigatorio()) {
                    $editorDTO->setStrSinCarimboPublicacao('N');
                }

                $editorRN = new EditorRN();
                /** Chamada ao componente SEI para retornar o conteúdo HTML do Documento do tipo interno */
                $result = $editorRN->consultarHtmlVersao($editorDTO);

                $auditoriaProtocoloDTO = new AuditoriaProtocoloDTO();
                $auditoriaProtocoloDTO->setStrRecurso($strAcaoSeiCorrespondente);
                $auditoriaProtocoloDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                $auditoriaProtocoloDTO->setDblIdProtocolo($documentoDTOParam->getDblIdDocumento());
                $auditoriaProtocoloDTO->setNumIdAnexo(null);
                $auditoriaProtocoloDTO->setDtaAuditoria(InfraData::getStrDataAtual());
                $auditoriaProtocoloDTO->setNumVersao($editorDTO->getNumVersao());

                $auditoriaProtocoloRN = new AuditoriaProtocoloRN();
                /** Chamada ao componente SEI para auditar a visualização do Documento */
                $auditoriaProtocoloRN->auditarVisualizacao($auditoriaProtocoloDTO);
            } else if ($documentoDTOParam->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) {
                throw new Exception('Para visualização do Anexo deve-se chamar o serviço correspondente.');
            }else{
                $documentoDTOConsulta = new DocumentoDTO();
                $documentoDTOConsulta->setDblIdDocumento($documentoDTOParam->getDblIdDocumento());
                $documentoDTOConsulta->setObjInfraSessao(SessaoSEI::getInstance());
                $documentoDTOConsulta->setStrLinkDownload('controlador.php?acao=documento_download_anexo');

                /** Chamada a componente SEI para retorno do HTML do documento */
                $result = $documentoRN->consultarHtmlFormulario($documentoDTOConsulta);

                $auditoriaProtocoloDTO = new AuditoriaProtocoloDTO();
                $auditoriaProtocoloDTO->setStrRecurso($strAcaoSeiCorrespondente);
                $auditoriaProtocoloDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                $auditoriaProtocoloDTO->setDblIdProtocolo($documentoDTOParam->getDblIdDocumento());
                $auditoriaProtocoloDTO->setNumIdAnexo(null);
                $auditoriaProtocoloDTO->setDtaAuditoria(InfraData::getStrDataAtual());
                $auditoriaProtocoloDTO->setNumVersao(null);
                $auditoriaProtocoloRN = new AuditoriaProtocoloRN();
                /** Chamada ao componente SEI para auditar a visualização do Documento */
                $auditoriaProtocoloRN->auditarVisualizacao($auditoriaProtocoloDTO);
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Alterar Seção do documento
     * @param DocumentoDTO DocumentoDTO
     * @return array
     */
    public function alterarSecaoDocumento($dados)
    {
        try {
            $idDocumento = $dados["documento"];
            $numVersao = $dados["versao"];
            $arrSecoes = $dados["secoes"];

            // Criação do DTO de editor que realiza a edição das seções. 
            $objEditorDTO = new EditorDTO();

            $objEditorDTO->setDblIdDocumento($idDocumento); // Informa o id do documento
            $objEditorDTO->setNumVersao($numVersao); // Número da versão
            $objEditorDTO->setNumIdBaseConhecimento(null);
            $objEditorDTO->setStrSinIgnorarNovaVersao('N');

            // Percorre as seções do documento alteradas 
            $arrObjSecaoDocumentoDTO = array();
            // var_dump($arrSecoes); die();

            if ($arrSecoes) {
                foreach ($arrSecoes as $secao) {
                    $objSecaoDocumentoDTO = new SecaoDocumentoDTO();
//                    $objSecaoDocumentoDTO->setNumIdSecaoModelo($secao['id']);
                    $objSecaoDocumentoDTO->setNumIdSecaoDocumento($secao['id']);
                    $objSecaoDocumentoDTO->setNumIdSecaoModelo($secao['idSecaoModelo']);
                    $objSecaoDocumentoDTO->setStrConteudo($secao['conteudo']);
                    $arrObjSecaoDocumentoDTO[] = $objSecaoDocumentoDTO;
                }
            }

            $objEditorDTO->setArrObjSecaoDocumentoDTO($arrObjSecaoDocumentoDTO);

            // Realiza a alteração das seções. 
            $objEditorRN = new EditorRN();
            $numVersao = $objEditorRN->adicionarVersao($objEditorDTO);


            return MdWsSeiRest::formataRetornoSucessoREST(null, $numVersao);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Listar Seção do documento
     * @param DocumentoDTO DocumentoDTO
     * @return array
     */
    protected function listarSecaoDocumentoConectado(DocumentoDTO $dto)
    {
        try {
            $id = $dto->getDblIdDocumento();

            //Consulta que retorna todas as se
            $objVersaoSecaoDocumentoDTO = new VersaoSecaoDocumentoDTO();
            $objVersaoSecaoDocumentoDTO->retNumIdSecaoModeloSecaoDocumento();
            $objVersaoSecaoDocumentoDTO->retStrSinAssinaturaSecaoDocumento();
            $objVersaoSecaoDocumentoDTO->retStrSinSomenteLeituraSecaoDocumento();

            $objVersaoSecaoDocumentoDTO->retStrSinAssinaturaSecaoDocumento();
            $objVersaoSecaoDocumentoDTO->retStrSinPrincipalSecaoDocumento();
            $objVersaoSecaoDocumentoDTO->retStrSinDinamicaSecaoDocumento();

            $objVersaoSecaoDocumentoDTO->retStrConteudo();
            $objVersaoSecaoDocumentoDTO->retNumVersao();
            $objVersaoSecaoDocumentoDTO->retNumIdSecaoDocumento();
            $objVersaoSecaoDocumentoDTO->setDblIdDocumentoSecaoDocumento($id);
            $objVersaoSecaoDocumentoDTO->setNumIdBaseConhecimentoSecaoDocumento(null);
            $objVersaoSecaoDocumentoDTO->setStrSinUltima('S');
            $objVersaoSecaoDocumentoDTO->setOrdNumOrdemSecaoDocumento(InfraDTO::$TIPO_ORDENACAO_ASC);

            $objVersaoSecaoDocumentoRN = new VersaoSecaoDocumentoRN();
            $arrObjVersaoSecaoDocumentoDTO = $objVersaoSecaoDocumentoRN->listar($objVersaoSecaoDocumentoDTO);

            $dadosSecaoDocumento = array();
            $numVersao = 0;


            //Monta as seções que precisam ser retornadas e resgata o número da última versão
            $arrayRetorno = array();
            if ($arrObjVersaoSecaoDocumentoDTO) {
                foreach ($arrObjVersaoSecaoDocumentoDTO as $obj) {
                    if ($obj->getStrSinAssinaturaSecaoDocumento() == 'N') {
                        $arrayRetorno["secoes"][] = array(
                            "id" => $obj->getNumIdSecaoDocumento(),
                            "idSecaoModelo" => $obj->getNumIdSecaoModeloSecaoDocumento(),
                            "conteudo" => $obj->getStrConteudo(),
                            "somenteLeitura" => $obj->getStrSinSomenteLeituraSecaoDocumento(),
                            "AssinaturaSecaoDocumento" => $obj->getStrSinAssinaturaSecaoDocumento(),
                            "PrincipalSecaoDocumento" => $obj->getStrSinPrincipalSecaoDocumento(),
                            "DinamicaSecaoDocumento" => $obj->getStrSinDinamicaSecaoDocumento()
                        );
                    }

                    if ($obj->getNumVersao() > $numVersao) {
                        $arrayRetorno["ultimaVersaoDocumento"] = $numVersao = $obj->getNumVersao();
                    } else {
                        $arrayRetorno["ultimaVersaoDocumento"] = $numVersao;
                    }
                }
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Realiza a pesquisa dos tipos de documento do SEI.
     * @param MdWsSeiDocumentoDTO $dto
     * @return array
     */
    protected function pesquisarTipoDocumentoConectado(MdWsSeiDocumentoDTO $dto)
    {
        try {

            $favoritos = $dto->getStrFavoritos();
            $id = $dto->getNumIdTipoDocumento();
            $nome = $dto->getStrNomeTipoDocumento();
            $aplicabilidade = $dto->getArrAplicabilidade();
            $start = $dto->getNumStart();
            $limit = $dto->getNumLimit();
//PARÂMETROS DE ENTRADA
//            $ID = 0;
//            $FILTER = '';
//            $START = 0;
//            $LIMIT = 5;
//            $favoritos = 'N';
            //REALIZA A BUSCA DE TODOS OS TIPOS DA UNIDADE FILTRANDO APENAS PELOS FAVORITOS. APÓS A BUSCA, OS FILTROS POR ID, NOME E APLICABILIDADE DEVERÃO SER FEITOS PERCORRENDO CADA UM DOS TIPOS. 

            $serieDTO = new SerieDTO();
            $serieDTO->setStrSinSomenteUtilizados($favoritos);
            $serieDTO->retNumIdSerie();
            $serieDTO->retStrNome();
            $serieDTO->retStrStaAplicabilidade();

            $serieRN = new SerieRN();
            $arrObjSerieDTO = $serieRN->listarTiposUnidade($serieDTO);

            $arrayRetorno = array();
            //FILTRA NOME, ID e APLICABILIDADE
            if ($arrObjSerieDTO) {
                foreach ($arrObjSerieDTO as $aux) {

                    setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

                    $objDtoFormatado = str_replace('?', '', strtolower(iconv('ISO-8859-1', 'ASCII//TRANSLIT', $aux->getStrNome())));
                    $nomeFormatado = str_replace('?', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome)));

                    if (
                        ($aux->getNumIdSerie() == $id || !$id) &&
                        (($nomeFormatado && strpos(utf8_encode($objDtoFormatado), $nomeFormatado) !== false) || !$nomeFormatado) &&
                        (in_array($aux->getStrStaAplicabilidade(), $aplicabilidade) == $aplicabilidade || !$aplicabilidade[0])
                    ) {
                        $arrayRetorno[] = array(
                            "id" => $aux->getNumIdSerie(),
                            "nome" => $aux->getStrNome()
                        );
                    }
                }
            }

            $total = 0;
            $total = count($arrayRetorno);


            if ($start)
                $arrayRetorno = array_slice($arrayRetorno, ($start - 1));
            if ($limit)
                $arrayRetorno = array_slice($arrayRetorno, 0, ($limit));

            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno, $total);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * O serviço de consulta de template de criação de processo informa ao client todas as variações existentes em um fomulário de criação de um documento. Entre essas variações estão: Assuntos Sugeridos , Existência de Destinatários_ e Existência de Interessados_ .
     * @param MdWsSeiDocumentoDTO $dto
     * @return array
     */
    protected function pesquisarTemplateDocumentoConectado(MdWsSeiDocumentoDTO $dto)
    {
        try {

            if (!$dto->getNumIdTipoDocumento()) {
                throw new InfraException('Tipo de documento é uma informação obrigatória.');
            }

            if (!$dto->getNumIdProcesso()) {
                throw new InfraException('O id do processo é obrigatório.');
            }

            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setDblIdProcedimento($dto->getNumIdProcesso());
            $objProcedimentoDTO->retNumIdTipoProcedimento();

            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->listarRN0278($objProcedimentoDTO);

            if (!$objProcedimentoDTO) {
                throw new Exception('Não foi encontrado processo com id ' . $dto->getNumIdProcesso());
            }

            // Consulta se o tipo de documento permite a inclusão de destinatários e interessados
            $serieDTO = new SerieDTO();
            $serieDTO->setNumIdSerie($dto->getNumIdTipoDocumento());
            $serieDTO->retStrSinDestinatario();
            $serieDTO->retStrSinInteressado();

            $serieRN = new SerieRN();
            $arrSerieDTO = $serieRN->listarRN0646($serieDTO);

            if (!$arrSerieDTO) {
                throw new Exception('Não foi encontrado processo um tipo de processo ' . $dto->getNumIdTipoDocumento());
            }

            $id_tipo_documento = $dto->getNumIdTipoDocumento();
            //$idTipoProcedimento = $dto->getNumIdTipoProcedimento();
            $idProcedimento = $dto->getNumIdProcesso();
            //$idProcedimento = $dto->getNumProcedimento();
            //Consulta os assuntos sugeridos para um tipo de documento
            $relSerieAssuntoDTO = new RelSerieAssuntoDTO();
            $relSerieAssuntoDTO->setNumIdSerie($id_tipo_documento); // FILTRO PELO TIPO DE DOCUMENTO
            $relSerieAssuntoDTO->retNumIdAssuntoProxy(); // ID DO ASSUNTO QUE DEVE SE RETORNADO
            $relSerieAssuntoDTO->retStrCodigoEstruturadoAssunto(); // CÓDIGO DO ASSUNTO QUE DEVE SE RETORNADO
            $relSerieAssuntoDTO->retStrDescricaoAssunto(); // DESCRIÇÃO DO ASSUNTO

            $relSerieAssuntoRN = new RelSerieAssuntoRN();
            $arrRelSerieAssuntoDTO = $relSerieAssuntoRN->listar($relSerieAssuntoDTO);

            $assuntos = array();
            if ($arrRelSerieAssuntoDTO) {
                foreach ($arrRelSerieAssuntoDTO as $obj) {
                    $assuntos[] = array(
                        "id" => $obj->getNumIdAssuntoProxy(),
                        "codigo" => $obj->getStrCodigoEstruturadoAssunto(),
                        "descricao" => $obj->getStrDescricaoAssunto()
                    );
                }
            }

            $serie = "";
            if ($arrSerieDTO) {
                $serie = $arrSerieDTO[0];
                $permiteInteressados = true;
                $permiteDestinatarios = true;
                if ($serie->getStrSinInteressado() == "N")
                    $permiteInteressados = false;
                if ($serie->getStrSinDestinatario() == "N")
                    $permiteDestinatarios = false;
            }

            $interessados = null;
            $arrayRetorno["nivelAcessoPermitido"] = null;

            if ($idProcedimento) {
                $objParticipanteDTO = new ParticipanteDTO();
                $objParticipanteDTO->setDblIdProtocolo($idProcedimento);
                $objParticipanteDTO->retStrNomeContato();
                $objParticipanteDTO->retNumIdContato();

                $objParticipanteRN = new ParticipanteRN();
                $arrParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);

                if ($arrParticipanteDTO) {
                    foreach ($arrParticipanteDTO as $obj) {
                        $interessados[] = array(
                            "id" => $obj->getNumIdContato(),
                            "nome" => $obj->getStrNomeContato()
                        );
                    }
                }


                $nivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
                $nivelAcessoPermitidoDTO->setNumIdTipoProcedimento($objProcedimentoDTO[0]->getNumIdTipoProcedimento()); // FILTRO PELO TIPO DE PROCESSO
                $nivelAcessoPermitidoDTO->retStrStaNivelAcesso(); // ID DO NÍVEL DE ACESSO - ProtocoloRN::$NA_PUBLICO, ProtocoloRN::$NA_RESTRITO ou ProtocoloRN::$NA_SIGILOSO


                $nivelAcessoPermitidoRN = new NivelAcessoPermitidoRN();
                $arrNivelAcessoPermitido = $nivelAcessoPermitidoRN->listar($nivelAcessoPermitidoDTO);
                if ($arrNivelAcessoPermitido) {
                    foreach ($arrNivelAcessoPermitido as $nivel) {
                        if ($nivel->getStrStaNivelAcesso() == ProtocoloRN::$NA_PUBLICO)
                            $publico = true;
                        if ($nivel->getStrStaNivelAcesso() == ProtocoloRN::$NA_RESTRITO)
                            $restrito = true;
                        if ($nivel->getStrStaNivelAcesso() == ProtocoloRN::$NA_SIGILOSO)
                            $sigiloso = true;
                    }
                }

                $arrayRetorno["nivelAcessoPermitido"] = array(
                    "publico" => $publico ? $publico : false,
                    "restrito" => $restrito ? $restrito : false,
                    "sigiloso" => $sigiloso ? $sigiloso : false,
                );
            }

            if (!$permiteInteressados)
                $interessados = null;

            $arrayRetorno = array(
                "assuntos" => $assuntos,
                "interessados" => empty($interessados) ? array() : $interessados,
                "nivelAcessoPermitido" => empty($arrayRetorno["nivelAcessoPermitido"]) ? array() : $arrayRetorno["nivelAcessoPermitido"],
                "permiteInteressados" => $permiteInteressados,
                "permiteDestinatarios" => $permiteDestinatarios
            );


            //CONSULTA NO PARÂMETRO QUE INFORMA SE A HIPÓTESE LEGAL É OBRIGATÓRIO PARA UM TIPO DE PROCESSO
            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $obrigatoriedadeHipoteseLegal = $objInfraParametro->getValor('SEI_HABILITAR_HIPOTESE_LEGAL');

            //CONSULTA NO PARÂMETRO QUE INFORMA SE UM GRAU DE SIGILO É OBRIGATÓRIO PARA UM TIPO DE PROCESSO
            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $obrigatoriedadeGrauSigilo = $objInfraParametro->getValor('SEI_HABILITAR_GRAU_SIGILO');

            $arrayRetorno["obrigatoriedadeHipoteseLegal"] = $obrigatoriedadeHipoteseLegal;
            $arrayRetorno["obrigatoriedadeGrauSigilo"] = $obrigatoriedadeGrauSigilo;


            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que retorna os documentos de um processo
     * @param DocumentoDTO $documentoDTOParam
     * @return array
     */
    protected function listarDocumentosProcessoConectado(DocumentoDTO $documentoDTOParam)
    {
        try {

            global $SEI_MODULOS;

            $arrDocHtml = array(
                DocumentoRN::$TD_EDITOR_EDOC,
                DocumentoRN::$TD_FORMULARIO_AUTOMATICO,
                DocumentoRN::$TD_FORMULARIO_GERADO,
                DocumentoRN::$TD_EDITOR_INTERNO
            );
            $result = array();
            $relProtocoloProtocoloDTOConsulta = new RelProtocoloProtocoloDTO();
            if (!$documentoDTOParam->isSetDblIdProcedimento()) {
                throw new InfraException('O procedimento deve ser informado.');
            }
            $relProtocoloProtocoloDTOConsulta->setDblIdProtocolo1($documentoDTOParam->getDblIdProcedimento());
            $relProtocoloProtocoloDTOConsulta->setStrStaProtocoloProtocolo2(
                array(ProtocoloRN::$TP_DOCUMENTO_GERADO, ProtocoloRN::$TP_DOCUMENTO_RECEBIDO), InfraDTO::$OPER_IN
            );
            $relProtocoloProtocoloDTOConsulta->retStrSinCiencia();
            $relProtocoloProtocoloDTOConsulta->retDblIdProtocolo1();
            $relProtocoloProtocoloDTOConsulta->retDblIdProtocolo2();
            $relProtocoloProtocoloDTOConsulta->retDblIdProtocolo2();
            $relProtocoloProtocoloDTOConsulta->retNumSequencia();
            $relProtocoloProtocoloDTOConsulta->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);
            if ($documentoDTOParam->getNumMaxRegistrosRetorno()) {
                $relProtocoloProtocoloDTOConsulta->setNumMaxRegistrosRetorno($documentoDTOParam->getNumMaxRegistrosRetorno());
            } else {
                $relProtocoloProtocoloDTOConsulta->setNumMaxRegistrosRetorno(10);
            }
            if (is_null($documentoDTOParam->getNumPaginaAtual())) {
                $relProtocoloProtocoloDTOConsulta->setNumPaginaAtual(0);
            } else {
                $relProtocoloProtocoloDTOConsulta->setNumPaginaAtual($documentoDTOParam->getNumPaginaAtual());
            }

            $relProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            /** Chama o componente SEI para consulta inicial dos documentos do processo */
            $ret = $relProtocoloProtocoloRN->listarRN0187($relProtocoloProtocoloDTOConsulta);


            $arrDocumentos = array();
            if ($ret) {
                $unidadeDTO = new UnidadeDTO();
                $unidadeDTO->setBolExclusaoLogica(false);
                $unidadeDTO->retStrSinProtocolo();
                $unidadeDTO->retStrSinOuvidoria();
                $unidadeDTO->retStrSinArquivamento();
                $unidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

                $unidadeRN = new UnidadeRN();
                /** Chamada ao componente SEI para verificação de parametros do documento */
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
                $documentoDTOConsulta->retNumIdTipoConferencia();
                $documentoDTOConsulta->setDblIdDocumento(array_keys(InfraArray::indexarArrInfraDTO($ret, 'IdProtocolo2')), InfraDTO::$OPER_IN);
                $documentoBD = new DocumentoBD($this->getObjInfraIBanco());
                /** Chama o componente SEI para retorno das informações dos documentos do processo */
                $retDocumentos = $documentoBD->listar($documentoDTOConsulta);

                /** @var DocumentoDTO $documentoDTOOrd */
                foreach ($retDocumentos as $documentoDTOOrd) {
                    $arrDocumentos[$documentoDTOOrd->getDblIdDocumento()] = $documentoDTOOrd;
                }
            }

            $anexoRN = new AnexoRN();
            $observacaoRN = new ObservacaoRN();
            $publicacaoRN = new PublicacaoRN();


            /** @var RelProtocoloProtocoloDTO $relProtocoloProtocoloDTO */
            foreach ($ret as $relProtocoloProtocoloDTO) {
                $documentoDTO = $arrDocumentos[$relProtocoloProtocoloDTO->getDblIdProtocolo2()];
                $mimetype = null;
                $nomeAnexo = null;
                $informacao = null;
                $tamanhoAnexo = null;
                $ciencia = 'N';
                $documentoCancelado = $documentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_DOCUMENTO_CANCELADO ? 'S' : 'N';


                if (!in_array($documentoDTO->getStrStaDocumento(), $arrDocHtml)) {
                    $anexoDTOConsulta = new AnexoDTO();
                    $anexoDTOConsulta->retStrNome();
                    $anexoDTOConsulta->retNumTamanho();
                    $anexoDTOConsulta->setDblIdProtocolo($documentoDTO->getDblIdDocumento());
                    $anexoDTOConsulta->setStrSinAtivo('S');
                    $anexoDTOConsulta->setNumMaxRegistrosRetorno(1);
                    /** Chama o componente SEI para verificação da existencia de anexo no documento */
                    $resultAnexo = $anexoRN->listarRN0218($anexoDTOConsulta);
                    if ($resultAnexo) {
                        /** @var AnexoDTO $anexoDTO */
                        $anexoDTO = $resultAnexo[0];
                        $mimetype = $anexoDTO->getStrNome();
                        $mimetype = substr($mimetype, strrpos($mimetype, '.') + 1);
                        $nomeAnexo = $anexoDTO->getStrNome();
                        $tamanhoAnexo = $anexoDTO->getNumTamanho();
                    }
                }

                $objProtocoloDTO = new ProtocoloDTO();
                $objProtocoloDTO->setDblIdProtocolo($relProtocoloProtocoloDTO->getDblIdProtocolo2());
                $objProtocoloDTO->retStrDescricao();
                $objTempProtocoloRN = new ProtocoloRN();
                /** Chamada ao componente SEI para retorno da descricao do protocolo */
                $objProtocoloDTO = $objTempProtocoloRN->consultarRN0186($objProtocoloDTO);
                $informacao = $objProtocoloDTO->getStrDescricao();

                $publicacaoDTOConsulta = new PublicacaoDTO();
                $publicacaoDTOConsulta->setDblIdDocumento($documentoDTO->getDblIdDocumento());
                $publicacaoDTOConsulta->retDblIdDocumento();
                $publicacaoDTOConsulta->setNumMaxRegistrosRetorno(1);
                /** Chama o componente SEI para verificar se o documento foi publicado */
                $resultPublicacao = $publicacaoRN->listarRN1045($publicacaoDTOConsulta);
                $documentoPublicado = $resultPublicacao ? 'S' : 'N';
                $ciencia = $relProtocoloProtocoloDTO->getStrSinCiencia();
                /** Faz a verificação de permissão de visualização de documento */
                $podeVisualizarDocumento = $this->podeVisualizarDocumento($documentoDTO, $bolFlagProtocolo);

                $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
                $objRelBlocoProtocoloDTO->setDistinct(true);
                $objRelBlocoProtocoloDTO->retDblIdProtocolo();
                $objRelBlocoProtocoloDTO->setNumIdUnidadeBloco(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $objRelBlocoProtocoloDTO->setStrStaTipoBloco(BlocoRN::$TB_ASSINATURA);
                $objRelBlocoProtocoloDTO->setStrStaEstadoBloco(BlocoRN::$TE_DISPONIBILIZADO);


                $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
                /** Chama o componente SEI para verificação se o documento foi disponibilizado para outra unidade */
                $arrDocumentosDisponibilizados = InfraArray::indexarArrInfraDTO($objRelBlocoProtocoloRN->listarRN1291($objRelBlocoProtocoloDTO), 'IdProtocolo');


                if (isset($arrDocumentosDisponibilizados[$documentoDTOParam->getDblIdProcedimento()])) {
                    $disponibilizado = "S";
                } else {
                    $disponibilizado = "N";
                }

                $strStaDocumento = $documentoDTO->getStrStaDocumento();
                $numIdUnidadeGeradoraProtocolo = $documentoDTO->getNumIdUnidadeGeradoraProtocolo();
                $numIdUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
                $strSinDisponibilizadoParaOutraUnidade = $disponibilizado;

                $permiteAssinatura = false;
                $hasBloco = false;

                //recupera blocos disponibilizados para a unidade atual
                $objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
                $objRelBlocoUnidadeDTO->retNumIdBloco();
                $objRelBlocoUnidadeDTO->retStrStaTipoBloco();
                $objRelBlocoUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $objRelBlocoUnidadeDTO->setStrSinRetornado('N');
                $objRelBlocoUnidadeDTO->setStrStaEstadoBloco(BlocoRN::$TE_DISPONIBILIZADO);

                $objRelBlocoUnidadeRN = new RelBlocoUnidadeRN();
                /** Chama o componente SEI para verificação dos documentos disponibilizados para a unidade atual */
                $arrObjRelBlocoUnidadeDTO = $objRelBlocoUnidadeRN->listarRN1304($objRelBlocoUnidadeDTO);


                //se tem blocos disponibilizados
                if (count($arrObjRelBlocoUnidadeDTO)) {
                    //busca documentos dos blocos que foram disponibilizados para a unidade atual
                    $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
                    $objRelBlocoProtocoloDTO->retDblIdProtocolo();
                    $objRelBlocoProtocoloDTO->retNumIdUnidadeBloco();
                    $objRelBlocoProtocoloDTO->retStrStaTipoBloco();
                    $objRelBlocoProtocoloDTO->retStrStaProtocoloProtocolo();
                    $objRelBlocoProtocoloDTO->retDblIdProcedimentoDocumento();
                    $objRelBlocoProtocoloDTO->setNumIdBloco(InfraArray::converterArrInfraDTO($arrObjRelBlocoUnidadeDTO, 'IdBloco'), InfraDTO::$OPER_IN);
                    $objRelBlocoProtocoloDTO->setDblIdProtocolo($documentoDTO->getDblIdDocumento());

                    $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
                    /** Chama o componente SEI para verificação do bloco de assinatura do documento */
                    $arrObjRelBlocoProtocoloDTO = $objRelBlocoProtocoloRN->listarRN1291($objRelBlocoProtocoloDTO);

                    if (count($arrObjRelBlocoProtocoloDTO)) {
                        $hasBloco = true;
                    }
                }


                if ((($documentoDTO->getStrStaDocumento() == DocumentoRN::$TD_EDITOR_INTERNO || $strStaDocumento == DocumentoRN::$TD_FORMULARIO_GERADO) &&
                        ($numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual && $strSinDisponibilizadoParaOutraUnidade == 'N')) || $hasBloco
                ) {
                    $permiteAssinatura = true;
                }


                $result[] = array(
                    'id' => $documentoDTO->getDblIdDocumento(),
                    'atributos' => array(
                        'idProcedimento' => $documentoDTO->getDblIdProcedimento(),
                        'idProtocolo' => $documentoDTO->getDblIdDocumento(),
                        'protocoloFormatado' => $documentoDTO->getStrProtocoloDocumentoFormatado(),
                        'nome' => $nomeAnexo,
                        'titulo' => $documentoDTO->getStrNumero(),
                        'tipo' => $documentoDTO->getStrNomeSerie(),
                        'tipoDocumento' => $strStaDocumento,
                        'mimeType' => $mimetype ? $mimetype : 'html',
                        'informacao' => $informacao,
                        'tamanho' => $tamanhoAnexo,
                        'idUnidade' => $documentoDTO->getNumIdUnidadeGeradoraProtocolo(),
                        'siglaUnidade' => $documentoDTO->getStrSiglaUnidadeGeradoraProtocolo(),
                        'nomeComposto' => DocumentoINT::montarIdentificacaoArvore($documentoDTO),
                        'tipoConferencia' => $documentoDTO->getNumIdTipoConferencia(),
                        'status' => array(
                            'sinBloqueado' => $documentoDTO->getStrStaNivelAcessoLocalProtocolo() == 1 ? 'S' : 'N',
                            'documentoSigiloso' => $documentoDTO->getStrStaNivelAcessoLocalProtocolo() == 2 ? 'S' : 'N',
                            'documentoRestrito' => $documentoDTO->getStrStaNivelAcessoLocalProtocolo() == 1 ? 'S' : 'N',
                            'documentoPublicado' => $documentoPublicado,
                            'documentoAssinado' => $documentoDTO->getStrCrcAssinatura() ? 'S' : 'N',
                            'ciencia' => $ciencia,
                            'documentoCancelado' => $documentoCancelado,
                            'podeVisualizarDocumento' => $podeVisualizarDocumento ? 'S' : 'N',
                            'permiteAssinatura' => $permiteAssinatura
                        )
                    )
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $relProtocoloProtocoloDTOConsulta->getNumTotalRegistros());
        } catch (Exception $e) {
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
    public function apiAssinarDocumentos($arrIdDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario)
    {
        //transforma os dados no array
        if (strpos($arrIdDocumento, ',') !== false) {
            $arrDocs = explode(',', $arrIdDocumento);
        } else {
            $arrDocs = array($arrIdDocumento);
        }

        foreach ($arrDocs as $dblIdDocumento) {
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
    public function apiAssinarDocumento($idDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario)
    {
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
    public function assinarDocumentoControlado(AssinaturaDTO $assinaturaDTO)
    {
        try {
            $assinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
            $assinaturaDTO->setNumIdContextoUsuario(null);
            $documentoRN = new DocumentoRN();
            $documentoRN->assinarInterno($assinaturaDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Documento em bloco assinado com sucesso.');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * @param DocumentoDTO $documentoDTO
     *   id documento obrigatorio
     * @return array
     */
    protected function darCienciaControlado(DocumentoDTO $documentoDTO)
    {
        try {
            $documentoRN = new DocumentoRN();
            if (!$documentoDTO->isSetDblIdDocumento()) {
                throw new InfraException('O documento não foi informado.');
            }
            $documentoRN->darCiencia($documentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Ciência documento realizado com sucesso.');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    protected function downloadAnexoConectado(ProtocoloDTO $protocoloDTOParam)
    {
        try {
            if (!$protocoloDTOParam->isSetDblIdProtocolo() || !$protocoloDTOParam->getDblIdProtocolo()) {
                throw new InfraException('O protocolo deve ser informado!');
            }

            if(!$this->verificarAcessoProtocolo($protocoloDTOParam->getDblIdProtocolo())) {
                throw new InfraException("Acesso ao documento " . $protocoloDTOParam->getDblIdProtocolo() . " não autorizado.");
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

            if ($documentoDTO->getStrStaDocumento() == DocumentoRN::$TD_EDITOR_EDOC) {
                if ($documentoDTO->getDblIdDocumentoEdoc() == null) {
                    throw new InfraException('Documento sem conteúdo!');
                }
                $eDocRN = new EDocRN();
                $html = $eDocRN->consultarHTMLDocumentoRN1204($documentoDTO);

                return MdWsSeiRest::formataRetornoSucessoREST(null, array('html' => $html));
            } else if (in_array($documentoDTO->getStrStaDocumento(), array(DocumentoRN::$TD_FORMULARIO_AUTOMATICO, DocumentoRN::$TD_FORMULARIO_GERADO))) {
                $html = $documentoRN->consultarHtmlFormulario($documentoDTO);

                return MdWsSeiRest::formataRetornoSucessoREST(null, array('html' => $html));
            } else if ($documentoDTO->getStrStaDocumento() == DocumentoRN::$TD_EDITOR_INTERNO) {
                $editorDTOConsulta = new EditorDTO();
                $editorDTOConsulta->setDblIdDocumento($documentoDTO->getDblIdDocumento());
                $editorDTOConsulta->setNumIdBaseConhecimento(null);
                $editorDTOConsulta->setStrSinCabecalho('S');
                $editorDTOConsulta->setStrSinRodape('S');
                $editorDTOConsulta->setStrSinIdentificacaoVersao('S');
                $editorDTOConsulta->setStrSinProcessarLinks('S');

                if (MdWsSeiEditorRN::versaoCarimboPublicacaoObrigatorio()) {
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
            } else {
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
                if (empty($resultAnexo)) {
                    throw new InfraException('Documento não encontrado!');
                }
                $anexo = $resultAnexo[0];
                SeiINT::download($anexo);
                exit;
            }
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Listar Ciencias realizadas em um Documento
     * @param MdWsSeiProcessoDTO $mdWsSeiProcessoDTOParam
     *   *valor = protocoloformatado?
     * @return array
     */
    protected function listarCienciaDocumentoConectado(MdWsSeiProcessoDTO $mdWsSeiProcessoDTOParam)
    {
        try {
            if (!$mdWsSeiProcessoDTOParam->isSetStrValor()) {
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
            foreach ($ret as $atributoAndamentoDTO) {
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
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Listar assinaturas do documento
     * @param DocumentoDTO $documentoDTOParam
     * @return array
     */
    protected function listarAssinaturasDocumentoConectado(DocumentoDTO $documentoDTOParam)
    {
        try {
            if (!$documentoDTOParam->isSetDblIdDocumento()) {
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
            foreach ($ret as $assinaturaDTO) {
                $result[] = array(
                    'nome' => $assinaturaDTO->getStrNome(),
                    'cargo' => $assinaturaDTO->getStrTratamento(),
                    'unidade' => $assinaturaDTO->getStrSiglaUnidade()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
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

    /**
     * Método que cria um documento externo atraves de uma requisição do Slim
     * @param \Slim\Http\Request $request
     */
    public function criarDocumentoExternoRequest(\Slim\Http\Request $request)
    {
        try {
            if (!$request->getAttribute('route')->getArgument('procedimento')) {
                throw new Exception('O processo não foi informado.');
            }
            $post = $request->getParams();
            /** Realiza o encapsulamento das informações vindas da requisiçao */
            $documentoDTO = self::encapsulaDocumento($post);
            $documentoDTO->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
            $arrFiles = $request->getUploadedFiles();
            if (!isset($arrFiles['anexo']) || empty($arrFiles['anexo'])) {
                throw new Exception('Anexo não informado.');
            }
            /** Processa o upload do arquivo e grava na pasta temporaria do SEI */
            $anexoDTO = MdWsSeiAnexoRN::processarUploadSlim($arrFiles['anexo']);
            $documentoDTO->getObjProtocoloDTO()->setArrObjAnexoDTO(array($anexoDTO));
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
        /** Processo de criação de documento do tipo externo */
        return $this->documentoExternoCriar($documentoDTO);
    }

    /**
     * Método que cria um documento interno atraves de uma requisição do Slim
     * @param \Slim\Http\Request $request
     */
    public function criarDocumentoInternoRequest(\Slim\Http\Request $request)
    {
        try {
            if (!$request->getAttribute('route')->getArgument('procedimento')) {
                throw new Exception('O processo não foi informado.');
            }
            $post = $request->getParams();
            /** Realiza o encapsulamento das informações vindas da requisiçao */
            $documentoDTO = self::encapsulaDocumento($post);
            $documentoDTO->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
        /** Processo de criação de documento do tipo interno */
        return $this->documentoInternoCriar($documentoDTO);
    }

    /**
     * Método que altera um documento externo atraves de uma requisição do Slim
     * @param \Slim\Http\Request $request
     */
    public function alterarDocumentoExternoRequest(\Slim\Http\Request $request)
    {
        try {
            if (!$request->getAttribute('route')->getArgument('documento')) {
                throw new Exception('O documento não foi informado.');
            }
            $documentoDTO = new DocumentoDTO();
            $documentoDTO->setDblIdDocumento($request->getAttribute('route')->getArgument('documento'));
            $documentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $documentoDTO->retTodos(true);
            /** Chamada no componente SEI para consulta de documento */
            $documentoDTO = $this->consultarRN0005($documentoDTO);
            if(!$documentoDTO){
                throw new Exception('Documento não encontrado.');
            }
            $post = $request->getParams();
            /** Realiza o encapsulamento das informações vindas da requisiçao */
            $documentoDTO = self::encapsulaDocumento($post, $documentoDTO);
            $arrFiles = $request->getUploadedFiles();
            if (isset($arrFiles['anexo']) && !empty($arrFiles['anexo'])) {
                /** Processa o upload do arquivo e grava na pasta temporaria do SEI */
                $anexoDTO = MdWsSeiAnexoRN::processarUploadSlim($arrFiles['anexo']);
                $documentoDTO->getObjProtocoloDTO()->setArrObjAnexoDTO(array($anexoDTO));
            }
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
        /** Processo de alteração de documento do tipo externo */
        return $this->documentoExternoAlterar($documentoDTO);
    }


    /**
     * Método que altera um documento interno atraves de uma requisição do Slim
     * @param \Slim\Http\Request $request
     */
    public function alterarDocumentoInternoRequest(\Slim\Http\Request $request)
    {
        try {
            if (!$request->getAttribute('route')->getArgument('documento')) {
                throw new Exception('O documento não foi informado.');
            }
            $documentoDTO = new DocumentoDTO();
            $documentoDTO->setDblIdDocumento($request->getAttribute('route')->getArgument('documento'));
            $documentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $documentoDTO->retTodos(true);
            /** Chamada no componente SEI para consulta de documento */
            $documentoDTO = $this->consultarRN0005($documentoDTO);
            if(!$documentoDTO){
                throw new Exception('Documento não encontrado.');
            }
            $post = $request->getParams();
            /** Realiza o encapsulamento das informações vindas da requisiçao */
            $documentoDTO = self::encapsulaDocumento($post, $documentoDTO);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
        /** Processo de alteração de documento do tipo interno */
        return $this->documentoInternoAlterar($documentoDTO);
    }

    /**
     * Método que cria um documento externo
     * @return array
     */
    protected function documentoExternoCriarConectado(DocumentoDTO $documentoDTO)
    {
        try {
            $result = array();
            $documentoDTO->setStrStaDocumento(DocumentoRN::$TD_EXTERNO);
            $objDocumentoRN = new DocumentoRN();
            /** Chamada a componente do SEI para cadastro de DOCUMENTO e seus anexos */
            $documentoDTO = $objDocumentoRN->cadastrarRN0003($documentoDTO);

            $result = array(
                "idDocumento" => $documentoDTO->getDblIdDocumento(),
                "protocoloDocumentoFormatado" => $documentoDTO->getStrProtocoloDocumentoFormatado()
            );

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que cria um documento interno
     * @return array
     */
    protected function documentoInternoCriarConectado(DocumentoDTO $documentoDTO)
    {
        try {
            $result = array();
            $numIdProcedimento = $documentoDTO->getDblIdProcedimento();
            $documentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
            $documentoDTO->getObjProtocoloDTO()->setDtaGeracao(InfraData::getStrDataAtual());
            $objDocumentoRN = new DocumentoRN();
            /** Chamada a componente do SEI para cadastro de DOCUMENTO */
            $documentoDTO = $objDocumentoRN->cadastrarRN0003($documentoDTO);

            $result = array(
                "idDocumento" => $documentoDTO->getDblIdDocumento(),
                "protocoloDocumentoFormatado" => $documentoDTO->getStrProtocoloDocumentoFormatado()
            );

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que altera um documento externo
     * @return array
     */
    protected function documentoExternoAlterarConectado(DocumentoDTO $documentoDTO)
    {
        try {
            $result = array();
            /** Chamada a componente do SEI para edição de DOCUMENTO e seus anexos */
            $this->alterarRN0004($documentoDTO);

            $result = array(
                "idDocumento" => $documentoDTO->getDblIdDocumento(),
                "protocoloDocumentoFormatado" => $documentoDTO->getStrProtocoloDocumentoFormatado()
            );

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que altera um documento interno
     * @return array
     */
    protected function documentoInternoAlterarConectado(DocumentoDTO $documentoDTO)
    {
        try {
            $result = array();
            /** Chamada a componente do SEI para edição de DOCUMENTO */
            $this->alterarRN0004($documentoDTO);

            $result = array(
                "idDocumento" => $documentoDTO->getDblIdDocumento(),
                "protocoloDocumentoFormatado" => $documentoDTO->getStrProtocoloDocumentoFormatado()
            );

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que consulta um documento externo
     * @return array
     * @param int $numIdDocumento
     */
    public function consultarDocumentoExterno($numIdDocumento)
    {
        try {

            if(!$this->verificarAcessoProtocolo($numIdDocumento)){
                throw new InfraException("Acesso ao documento " . $numIdDocumento . " não autorizado.");
            }

            $result = array();
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->retDblIdProtocoloProcedimento();
            $objDocumentoDTO->retDblIdProcedimento();
            $objDocumentoDTO->retDblIdDocumento();
            $objDocumentoDTO->retStrDescricaoProtocolo();
            $objDocumentoDTO->retStrStaProtocoloProtocolo();
            $objDocumentoDTO->retStrStaNivelAcessoLocalProtocolo();
            $objDocumentoDTO->retNumIdHipoteseLegalProtocolo();
            $objDocumentoDTO->retStrNomeHipoteseLegal();
            $objDocumentoDTO->retStrStaGrauSigiloProtocolo();
            $objDocumentoDTO->retDtaGeracaoProtocolo();
            $objDocumentoDTO->retNumIdSerie();
            $objDocumentoDTO->retStrNomeSerie();
            $objDocumentoDTO->retStrStaDocumento();
            $objDocumentoDTO->retNumIdTipoConferencia();
            $objDocumentoDTO->retNumIdUnidadeGeradoraProtocolo();
            $objDocumentoDTO->retStrSinBloqueado();
            $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
            $objDocumentoDTO->retStrNumero();
            $objDocumentoDTO->retStrDescricaoTipoConferencia();
            $objDocumentoDTO->retNumIdTipoConferencia();
            $objDocumentoDTO->retNumIdUnidadeGeradoraProtocolo();
            $objDocumentoDTO->retStrSiglaUnidadeGeradoraProtocolo();
            $objDocumentoDTO->setDblIdDocumento($numIdDocumento);
            $objDocumentoRN = new DocumentoRN();
            /** Consulta no componente do SEI para retornar o Documento **/
            $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
            if (!$objDocumentoDTO){
                throw new Exception("Registro não encontrado.");
            }
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setStrDescricao($objDocumentoDTO->getStrDescricaoProtocolo());
            $objProtocoloDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
            $objProtocoloDTO->setStrStaNivelAcessoLocal($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo());
            $objProtocoloDTO->setNumIdHipoteseLegal($objDocumentoDTO->getNumIdHipoteseLegalProtocolo());
            $objProtocoloDTO->setStrStaGrauSigilo($objDocumentoDTO->getStrStaGrauSigiloProtocolo());
            $objProtocoloDTO->setDtaGeracao($objDocumentoDTO->getDtaGeracaoProtocolo());

            /** Chamada para retorno da observação do documento **/
            $objObservacaoDTO = $this->retornaObservacaoDocumento($objDocumentoDTO->getDblIdDocumento());
            /** Chamada para retorno dos assuntos do documento **/
            $arrObjRelProtocoloAssuntoDTO = $this->retornaAssuntosDocumento($objDocumentoDTO->getDblIdDocumento());
            $arrDataAssuntos = array();
            /** @var RelProtocoloAssuntoDTO $objRelProtocoloAssuntoDTO */
            foreach($arrObjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO) {
                $arrDataAssuntos[] = array(
                    'id' => $objRelProtocoloAssuntoDTO->getNumIdAssunto(),
                    'codigoestruturadoformatado' => $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto(),
                    'descricao' => $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto(),
                    'sequencia' => $objRelProtocoloAssuntoDTO->retNumSequencia(),
                );
            }
            /** Chamada para retorno do remetente do documento **/
            $objRemetente = $this->retornaRemetenteDocumento($objDocumentoDTO->getDblIdDocumento());
            $arrDataRemetente = null;
            if($objRemetente){
                $arrDataRemetente = array(
                    'id' => $objRemetente->getNumIdContato(),
                    'nome' => $objRemetente->getStrNomeContato(),
                    'sigla' => $objRemetente->getStrSiglaContato(),
                    /** Chamada ao componente SEI para formatação do nome do remetente */
                    'nomeformatado' => ContatoINT::formatarNomeSiglaRI1224($objRemetente->getStrNomeContato(),$objRemetente->getStrSiglaContato()),
                );
            }
            /** Chamada para retorno dos interessados do documento */
            $arrObjParticipanteDTOInteressados = $this->retornaParticipanteDocumentoPorParticipacao($objDocumentoDTO->getDblIdDocumento(), ParticipanteRN::$TP_INTERESSADO);
            $arrDataInteressados = array();
            /** @var ParticipanteDTO $objParticipanteDTOInteressados */
            foreach($arrObjParticipanteDTOInteressados as $objParticipanteDTOInteressados) {
                $arrDataInteressados[] = array(
                    'id' => $objParticipanteDTOInteressados->getNumIdContato(),
                    'nome' => $objParticipanteDTOInteressados->getStrNomeContato(),
                    /** Chamada ao componente SEI para formatação do nome do interessado */
                    'nomeformatado' => ContatoINT::formatarNomeSiglaRI1224($objParticipanteDTOInteressados->getStrNomeContato(),$objParticipanteDTOInteressados->getStrSiglaContato()),
                    'sigla' => $objParticipanteDTOInteressados->getStrSiglaContato(),
                );
            }
            /** Chamada para retorno dos destinatarios do documento */
            $arrObjParticipanteDTODestinatarios = $this->retornaParticipanteDocumentoPorParticipacao($objDocumentoDTO->getDblIdDocumento(), ParticipanteRN::$TP_DESTINATARIO);
            $arrDataDestinatarios = array();
            /** @var ParticipanteDTO $objParticipanteDTODestinatarios */
            foreach($arrObjParticipanteDTODestinatarios as $objParticipanteDTODestinatarios) {
                $arrDataDestinatarios[] = array(
                    'id' => $objParticipanteDTODestinatarios->getNumIdContato(),
                    'nome' => $objParticipanteDTODestinatarios->getStrNomeContato(),
                    /** Chamada ao componente SEI para formatação do nome do destinatario */
                    'nomeformatado' => ContatoINT::formatarNomeSiglaRI1224($objParticipanteDTODestinatarios->getStrNomeContato(),$objParticipanteDTODestinatarios->getStrSiglaContato()),
                    'sigla' => $objParticipanteDTODestinatarios->getStrSiglaContato(),
                );
            }
            /** Chamada para retorno das observações de outras unidades */
            $arrObjObservacaoDTOOutrasUnidades = $this->retornaObservacoesDocumentoOutrasUnidades($objDocumentoDTO->getDblIdDocumento(), ParticipanteRN::$TP_DESTINATARIO);
            $arrDataObservacaoOutrasUnidades = array();
            /** @var ObservacaoDTO $objObservacaoDTOOutrasUnidades */
            foreach($arrObjObservacaoDTOOutrasUnidades as $objObservacaoDTOOutrasUnidades) {
                $arrDataObservacaoOutrasUnidades[] = array(
                    'id' => $objObservacaoDTOOutrasUnidades->getNumIdObservacao(),
                    'sigla' => $objObservacaoDTOOutrasUnidades->getStrSiglaUnidade(),
                    'unidade' => $objObservacaoDTOOutrasUnidades->getStrDescricaoUnidade(),
                    'descricao' => $objObservacaoDTOOutrasUnidades->getStrDescricao(),
                );
            }
            /** Chamada para retorno do anexo do documento externo */
            $objAnexoDTO = $this->retornaAnexoDocumento($objDocumentoDTO->getDblIdDocumento());
            /** Verifica se o anexo pode ser removido (se o documento não estiver bloqueado) **/
            $bolAcaoRemoverAnexo = (SessaoSEI::getInstance()->verificarPermissao('documento_remover_anexo') &&
                $objDocumentoDTO->getStrSinBloqueado()=='N');

            $result = array(
                'nomeDocumento' => DocumentoINT::montarIdentificacaoArvore($objDocumentoDTO),
                'protocolo' => $objDocumentoDTO->getDblIdProtocoloProcedimento(),
                'idDocumento' => $objDocumentoDTO->getDblIdDocumento(),
                'idSerie' => $objDocumentoDTO->getNumIdSerie(),
                'nomeSerie' => $objDocumentoDTO->getStrNomeSerie(),
                'numero' => $objDocumentoDTO->getStrNumero(),
                'idTipoConferencia' => $objDocumentoDTO->getNumIdTipoConferencia(),
                'descricaoTipoConferencia' => $objDocumentoDTO->getStrDescricaoTipoConferencia(),
                'nivelAcesso' => $objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo(),
                'idHipoteseLegal' => $objDocumentoDTO->getNumIdHipoteseLegalProtocolo(),
                'nomeHipoteseLegal' => $objDocumentoDTO->getStrNomeHipoteseLegal(),
                'grauSigilo' => $objDocumentoDTO->getStrStaGrauSigiloProtocolo(),
                'descricao' => $objDocumentoDTO->getStrDescricaoProtocolo(),
                'dataElaboracao' => $objDocumentoDTO->getDtaGeracaoProtocolo(),
                'observacao' => ($objObservacaoDTO ? $objObservacaoDTO->getStrDescricao() : null),
                'assuntos' => $arrDataAssuntos,
                'remetente' => $arrDataRemetente,
                'interessados' => $arrDataInteressados,
                'destinatarios' => $arrDataDestinatarios,
                'observacoesUnidades' => $arrDataObservacaoOutrasUnidades,
                'anexo' => array(
                    'id' => $objAnexoDTO->getNumIdAnexo(),
                    'unidade' => $objAnexoDTO->getNumIdUnidade(),
                    'siglaUnidade' => $objAnexoDTO->getStrSiglaUnidade(),
                    'nome' => $objAnexoDTO->getStrNome(),
                    'dataInclusao' => $objAnexoDTO->getDthInclusao(),
                    'tamanho' => $objAnexoDTO->getNumTamanho(),
                    'siglaUsuario' => $objAnexoDTO->getStrSiglaUsuario(),
                    'podeExcluir' => $bolAcaoRemoverAnexo
                )
            );
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que retorna o anexo de um documento externo
     * @param $numIdDocumento
     * @return AnexoDTO
     */
    private function retornaAnexoDocumento($numIdDocumento)
    {
        $objAnexoDTO = new AnexoDTO();
        $objAnexoDTO->retNumIdAnexo();
        $objAnexoDTO->retNumIdUnidade();
        $objAnexoDTO->retStrNome();
        $objAnexoDTO->retDthInclusao();
        $objAnexoDTO->retNumTamanho();
        $objAnexoDTO->retStrSiglaUsuario();
        $objAnexoDTO->retStrSiglaUnidade();
        $objAnexoDTO->setDblIdProtocolo($numIdDocumento);
        $objAnexoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
        $objAnexoDTO->setOrdDthInclusao(InfraDTO::$TIPO_ORDENACAO_DESC);

        $objAnexoRN = new AnexoRN();
        /** Chamada a componente do SEI para retorno dos anexos do documento */
        $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);

        return $arrObjAnexoDTO[0];
    }

    /**
     * Metodo que retorna as observações de outras unidades
     * @param $numIdDocumento
     * @return array
     */
    private function retornaObservacoesDocumentoOutrasUnidades($numIdDocumento)
    {
        $objObservacaoDTO = new ObservacaoDTO();
        $objObservacaoDTO->retNumIdObservacao();
        $objObservacaoDTO->retStrSiglaUnidade();
        $objObservacaoDTO->retStrDescricaoUnidade();
        $objObservacaoDTO->retStrDescricao();

        $objObservacaoDTO->setDblIdProtocolo($numIdDocumento);
        $objObservacaoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual(),InfraDTO::$OPER_DIFERENTE);

        $objObservacaoDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);


        $objObservacaoRN = new ObservacaoRN();
        /** Chamada ao componente SEI para consultar a lista de observações */
        return $objObservacaoRN->listarRN0219($objObservacaoDTO);
    }

    /**
     * Metodo que retorna os participantes do documento por participação
     * @param $numIdDocumento
     * @param $staParticipacao
     * @return array
     */
    private function retornaParticipanteDocumentoPorParticipacao($numIdDocumento, $staParticipacao)
    {
        $objParticipanteDTO = new ParticipanteDTO();
        $objParticipanteDTO->retNumIdContato();
        $objParticipanteDTO->retStrNomeContato();
        $objParticipanteDTO->retStrSiglaContato();
        $objParticipanteDTO->setDblIdProtocolo($numIdDocumento);
        $objParticipanteDTO->setStrStaParticipacao(array($staParticipacao),InfraDTO::$OPER_IN);

        $objParticipanteDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objParticipanteRN = new ParticipanteRN();
        $arrObjParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);

        foreach($arrObjParticipanteDTO as $objParticipanteDTO){
            /** Chamada ao componente SEI para formatação de nome do participante **/
            $objParticipanteDTO->setStrNomeContato(ContatoINT::formatarNomeSiglaRI1224($objParticipanteDTO->getStrNomeContato(),$objParticipanteDTO->getStrSiglaContato()));
        }

        return $arrObjParticipanteDTO;
    }

    /**
     * Método que retorna o remetente do documento
     * @param $numIdDocumento
     * @return ParticipanteDTO
     */
    private function retornaRemetenteDocumento($numIdDocumento)
    {
        $objRemetente = new ParticipanteDTO();
        $objRemetente->retNumIdContato();
        $objRemetente->retStrNomeContato();
        $objRemetente->retStrSiglaContato();
        $objRemetente->setDblIdProtocolo($numIdDocumento);
        $objRemetente->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
        $objParticipanteRN = new ParticipanteRN();
        /** Chamada ao componente SEI para retorno do remetente **/
        return $objParticipanteRN->consultarRN1008($objRemetente);
    }

    /**
     * Método que retorna os assuntos de um documento
     * @param $numIdDocumento
     * @return Array
     */
    private function retornaAssuntosDocumento($numIdDocumento)
    {
        $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
        $objRelProtocoloAssuntoDTO->setDistinct(true);
        $objRelProtocoloAssuntoDTO->retNumSequencia();
        $objRelProtocoloAssuntoDTO->retNumIdAssunto();
        $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();
        $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
        $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();
        $objRelProtocoloAssuntoDTO->setDblIdProtocolo($numIdDocumento);
        $objRelProtocoloAssuntoDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
        /** Consulta o componente do SEI para retornar os assuntos de um documento **/
        $arrObjRelProtocoloAssuntoDTO = InfraArray::distinctArrInfraDTO($objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO),'IdAssunto');

        foreach($arrObjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO) {
            /** Chamada ao componente SEI para formatação de nome do assunto */
            $objRelProtocoloAssuntoDTO->setStrDescricaoAssunto(AssuntoINT::formatarCodigoDescricaoRI0568(
                $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto(),
                $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto())
            );
        }

        return $arrObjRelProtocoloAssuntoDTO;
    }

    /**
     * Método que retorna os assuntos de um documento
     * @param $numIdDocumento
     * @return ObservacaoDTO
     */
    private function retornaObservacaoDocumento($numIdDocumento)
    {
        $objObservacaoDTO  = new ObservacaoDTO();
        $objObservacaoDTO->retStrDescricao();
        $objObservacaoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objObservacaoDTO->setDblIdProtocolo($numIdDocumento);

        $objObservacaoRN = new ObservacaoRN();
        /** Consulta no componente do SEI se o documento tem observações **/
        return $objObservacaoRN->consultarRN0221($objObservacaoDTO);
    }


    /**
     * Retorna a pesquisa de tipo conferencia
     * @param TipoConferenciaDTO $tipoConferenciaDTOParam
     * @return array
     */
    protected function pesquisarTipoConferenciaConectado(TipoConferenciaDTO $tipoConferenciaDTOParam)
    {
        try {
            $result = array();
            $tipoConferenciaDTOParam->retNumIdTipoConferencia();
            $tipoConferenciaDTOParam->retStrDescricao();
            $tipoConferenciaDTOParam->setOrdStrDescricao(InfraDTO::$TIPO_ORDENACAO_ASC);

            if($tipoConferenciaDTOParam->isSetStrDescricao()){
                $tipoConferenciaDTOParam->setStrDescricao(
                    '%'.$tipoConferenciaDTOParam->getStrDescricao().'%',
                    InfraDTO::$OPER_LIKE
                );
            }

            $tipoConferenciaRN = new TipoConferenciaRN();
            /** Chamando componente SEI para retorno da pesquisa de tipo conferência */
            $ret = $tipoConferenciaRN->listar($tipoConferenciaDTOParam);

            /** @var SerieDTO $tipoConferenciaDTO */
            foreach ($ret as $tipoConferenciaDTO) {
                $result[] = array(
                    'id' => $tipoConferenciaDTO->getNumIdTipoConferencia(),
                    'descricao' => $tipoConferenciaDTO->getStrDescricao(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $tipoConferenciaDTOParam->getNumTotalRegistros());
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Apoia encapsulamento do documento na criação/edição
     * @param array $post
     * @param DocumentoDTO $objDocumentoDTO
     * @return DocumentoDTO
     */
    public static function encapsulaDocumento(array $post, DocumentoDTO $objDocumentoDTO = null)
    {
        $objProtocoloDTO = new ProtocoloDTO();
        if(!$objDocumentoDTO){
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objProtocoloDTO->setDblIdProtocolo(null);
        }else{
            $objProtocoloDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
        }

        if (isset($post['idUnidadeGeradoraProtocolo'])) {
            $objDocumentoDTO->setNumIdUnidadeGeradoraProtocolo($post['idUnidadeGeradoraProtocolo']);
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        }else if(!$objDocumentoDTO){
            $objDocumentoDTO->setNumIdUnidadeGeradoraProtocolo(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        }

        if(!$objDocumentoDTO->isSetStrStaDocumento() || $objDocumentoDTO->getStrStaDocumento() == DocumentoRN::$TD_EXTERNO){
            $objDocumentoDTO->setStrNumero($post['numero']);
            $objDocumentoDTO->setNumIdSerie($post['idSerie']);
            $objProtocoloDTO->setNumIdSerieDocumento($post['idSerie']);
            $objProtocoloDTO->setDtaGeracao($post['dataElaboracao']);
        }

        $objDocumentoDTO->setNumIdTextoPadraoInterno($post['idTextoPadraoInterno']);
        $objDocumentoDTO->setNumIdTipoConferencia($post['idTipoConferencia']);
        if(!$objDocumentoDTO){
            $objDocumentoDTO->setStrSinBloqueado('N');
        }
        if (isset($post['flagProtocolo']) && $post['flagProtocolo'] == 'S') {
            $arrObjUnidadeDTOReabertura = array();
            $arrUnidadesReabertura = explode(',', $post['unidadesReabertura']);
            for ($i = 0; $i < count($arrUnidadesReabertura); $i++) {
                $objUnidadeDTO = new UnidadeDTO();
                $objUnidadeDTO->setNumIdUnidade($arrUnidadesReabertura[$i]);
                $arrObjUnidadeDTOReabertura[] = $objUnidadeDTO;
            }
            $objDocumentoDTO->setArrObjUnidadeDTO($arrObjUnidadeDTOReabertura);
        }

        $objProtocoloDTO->setStrStaNivelAcessoLocal($post['nivelAcesso']);
        $objProtocoloDTO->setNumIdHipoteseLegal($post['idHipoteseLegal']);
        $objProtocoloDTO->setStrStaGrauSigilo($post['grauSigilo']);
        $objProtocoloDTO->setStrDescricao($post['descricao']);


        if (isset($post['assuntos']) && $post['assuntos'] != '') {
            $arrAssuntos = explode(',', $post['assuntos']);
            $arrObjAssuntosDTO = array();
            for ($x = 0; $x < count($arrAssuntos); $x++) {
                $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                $objRelProtocoloAssuntoDTO->setNumIdAssunto($arrAssuntos[$x]);
                $objRelProtocoloAssuntoDTO->setNumSequencia($x);
                $arrObjAssuntosDTO[$x] = $objRelProtocoloAssuntoDTO;
            }
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrObjAssuntosDTO);
        }else{
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO(array());
        }

        $arrObjParticipantesDTO = array();

        if (isset($post['interessados']) && $post['interessados'] != '') {
            $arrParticipantes = explode(',', $post['interessados']);
            for ($i = 0; $i < count($arrParticipantes); $i++) {
                $objParticipante = new ParticipanteDTO();
                $objParticipante->setNumIdContato($arrParticipantes[$i]);
                $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
                $objParticipante->setNumSequencia($i);
                $arrObjParticipantesDTO[] = $objParticipante;
            }
        }

        if (isset($post['remetente']) && $post['remetente'] != '') {
            $objParticipante = new ParticipanteDTO();
            $objParticipante->setNumIdContato($post['remetente']);
            $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
            $objParticipante->setNumSequencia(0);
            $arrObjParticipantesDTO[] = $objParticipante;
        }

        if (isset($post['destinatarios']) && $post['destinatarios'] != '') {
            $arrParticipantes = explode(',', $post['destinatarios']);
            for ($i = 0; $i < count($arrParticipantes); $i++) {
                $objParticipante = new ParticipanteDTO();
                $objParticipante->setNumIdContato($arrParticipantes[$i]);
                $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_DESTINATARIO);
                $objParticipante->setNumSequencia($i);
                $arrObjParticipantesDTO[] = $objParticipante;
            }
        }

        $objProtocoloDTO->setArrObjParticipanteDTO($arrObjParticipantesDTO);

        $objObservacaoDTO = new ObservacaoDTO();
        $objObservacaoDTO->setStrDescricao($post['observacao']);
        $objProtocoloDTO->setArrObjObservacaoDTO(array($objObservacaoDTO));
        $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);
        $objDocumentoDTO->setNumIdTextoPadraoInterno($post['idTextoPadraoInterno']);
        $objDocumentoDTO->setStrProtocoloDocumentoTextoBase($post['protocoloDocumentoTextoBase']);

        return $objDocumentoDTO;
    }

    /**
     * Retorna a lista de sugestao de assuntos
     * @param RelSerieAssuntoDTO $relSerieAssuntoDTOParam
     * @return array
     */
    protected function sugestaoAssuntoConectado(RelSerieAssuntoDTO $relSerieAssuntoDTOParam)
    {
        try {
            $result = array();
            $relSerieAssuntoDTOParam->retNumIdAssunto();
            $relSerieAssuntoDTOParam->retStrDescricaoAssunto();
            $relSerieAssuntoDTOParam->retStrCodigoEstruturadoAssunto();
            $relSerieAssuntoDTOParam->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

            if($relSerieAssuntoDTOParam->isSetStrDescricaoAssunto() && $relSerieAssuntoDTOParam->getStrDescricaoAssunto() != ''){
                $relSerieAssuntoDTOParam->setStrDescricaoAssunto(
                    '%'.$relSerieAssuntoDTOParam->getStrDescricaoAssunto().'%',
                    InfraDTO::$OPER_LIKE
                );
            }

            $relSerieAssuntoRN = new RelSerieAssuntoRN();
            /** Consulta no componente SEI a lista de assuntos **/
            $ret = $relSerieAssuntoRN->listar($relSerieAssuntoDTOParam);

            /** @var RelSerieAssuntoDTO $relSerieAssuntoDTO */
            foreach ($ret as $relSerieAssuntoDTO) {
                $result[] = array(
                    /** Chamando componente do SEI para formataçao de nome do assunto **/
                    'codigoestruturadoformatado' => AssuntoINT::formatarCodigoDescricaoRI0568(
                        $relSerieAssuntoDTO->getStrCodigoEstruturadoAssunto(),
                        $relSerieAssuntoDTO->getStrDescricaoAssunto()
                    ),
                    'descricao' => $relSerieAssuntoDTO->getStrDescricaoAssunto(),
                    'codigoestruturado' => $relSerieAssuntoDTO->getStrCodigoEstruturadoAssunto(),
                    'id' => $relSerieAssuntoDTO->getNumIdAssunto()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $relSerieAssuntoDTOParam->getNumTotalRegistros());
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que consulta um documento interno
     * @return array
     * @param int $numIdDocumento
     */
    public function consultarDocumentoInterno($numIdDocumento)
    {
        try {
            if(!$this->verificarAcessoProtocolo($numIdDocumento)){
                throw new InfraException("Acesso ao documento " . $numIdDocumento . " não autorizado.");
            }

            $result = array();
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->retDblIdProtocoloProcedimento();
            $objDocumentoDTO->retDblIdProcedimento();
            $objDocumentoDTO->retDblIdDocumento();
            $objDocumentoDTO->retStrDescricaoProtocolo();
            $objDocumentoDTO->retStrStaProtocoloProtocolo();
            $objDocumentoDTO->retStrStaNivelAcessoLocalProtocolo();
            $objDocumentoDTO->retNumIdHipoteseLegalProtocolo();
            $objDocumentoDTO->retStrNomeHipoteseLegal();
            $objDocumentoDTO->retStrBaseLegalHipoteseLegal();
            $objDocumentoDTO->retStrStaGrauSigiloProtocolo();
            $objDocumentoDTO->retDtaGeracaoProtocolo();
            $objDocumentoDTO->retNumIdSerie();
            $objDocumentoDTO->retStrNomeSerie();
            $objDocumentoDTO->retStrStaDocumento();
            $objDocumentoDTO->retNumIdTipoConferencia();
            $objDocumentoDTO->retNumIdUnidadeGeradoraProtocolo();
            $objDocumentoDTO->retStrSinBloqueado();
            $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
            $objDocumentoDTO->retStrNumero();
            $objDocumentoDTO->retStrDescricaoTipoConferencia();
            $objDocumentoDTO->retNumIdTipoConferencia();
            $objDocumentoDTO->retNumIdUnidadeGeradoraProtocolo();
            $objDocumentoDTO->retStrSiglaUnidadeGeradoraProtocolo();
            $objDocumentoDTO->setDblIdDocumento($numIdDocumento);
            $objDocumentoRN = new DocumentoRN();
            /** Consulta no componente do SEI para retornar o Documento **/
            $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
            if (!$objDocumentoDTO){
                throw new Exception("Registro não encontrado.");
            }
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setStrDescricao($objDocumentoDTO->getStrDescricaoProtocolo());
            $objProtocoloDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
            $objProtocoloDTO->setStrStaNivelAcessoLocal($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo());
            $objProtocoloDTO->setNumIdHipoteseLegal($objDocumentoDTO->getNumIdHipoteseLegalProtocolo());
            $objProtocoloDTO->setStrStaGrauSigilo($objDocumentoDTO->getStrStaGrauSigiloProtocolo());
            $objProtocoloDTO->setDtaGeracao($objDocumentoDTO->getDtaGeracaoProtocolo());

            /** Chamada para retorno da observação do documento **/
            $objObservacaoDTO = $this->retornaObservacaoDocumento($objDocumentoDTO->getDblIdDocumento());
            /** Chamada para retorno dos assuntos do documento **/
            $arrObjRelProtocoloAssuntoDTO = $this->retornaAssuntosDocumento($objDocumentoDTO->getDblIdDocumento());
            $arrDataAssuntos = array();
            /** @var RelProtocoloAssuntoDTO $objRelProtocoloAssuntoDTO */
            foreach($arrObjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO) {
                $arrDataAssuntos[] = array(
                    'id' => $objRelProtocoloAssuntoDTO->getNumIdAssunto(),
                    'codigoestruturadoformatado' => $objRelProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto(),
                    'descricao' => $objRelProtocoloAssuntoDTO->getStrDescricaoAssunto(),
                    'sequencia' => $objRelProtocoloAssuntoDTO->retNumSequencia(),
                );
            }
            /** Chamada para retorno dos interessados do documento */
            $arrObjParticipanteDTOInteressados = $this->retornaParticipanteDocumentoPorParticipacao($objDocumentoDTO->getDblIdDocumento(), ParticipanteRN::$TP_INTERESSADO);
            $arrDataInteressados = array();
            /** @var ParticipanteDTO $objParticipanteDTOInteressados */
            foreach($arrObjParticipanteDTOInteressados as $objParticipanteDTOInteressados) {
                $arrDataInteressados[] = array(
                    'id' => $objParticipanteDTOInteressados->getNumIdContato(),
                    'nome' => $objParticipanteDTOInteressados->getStrNomeContato(),
                    /** Chamada ao componente SEI para formatação do nome do interessado */
                    'nomeformatado' => ContatoINT::formatarNomeSiglaRI1224($objParticipanteDTOInteressados->getStrNomeContato(),$objParticipanteDTOInteressados->getStrSiglaContato()),
                    'sigla' => $objParticipanteDTOInteressados->getStrSiglaContato(),
                );
            }
            /** Chamada para retorno dos destinatarios do documento */
            $arrObjParticipanteDTODestinatarios = $this->retornaParticipanteDocumentoPorParticipacao($objDocumentoDTO->getDblIdDocumento(), ParticipanteRN::$TP_DESTINATARIO);
            $arrDataDestinatarios = array();
            /** @var ParticipanteDTO $objParticipanteDTODestinatarios */
            foreach($arrObjParticipanteDTODestinatarios as $objParticipanteDTODestinatarios) {
                $arrDataDestinatarios[] = array(
                    'id' => $objParticipanteDTODestinatarios->getNumIdContato(),
                    'nome' => $objParticipanteDTODestinatarios->getStrNomeContato(),
                    /** Chamada ao componente SEI para formatação do nome do destinatario */
                    'nomeformatado' => ContatoINT::formatarNomeSiglaRI1224($objParticipanteDTODestinatarios->getStrNomeContato(),$objParticipanteDTODestinatarios->getStrSiglaContato()),
                    'sigla' => $objParticipanteDTODestinatarios->getStrSiglaContato(),
                );
            }
            /** Chamada para retorno das observações de outras unidades */
            $arrObjObservacaoDTOOutrasUnidades = $this->retornaObservacoesDocumentoOutrasUnidades($objDocumentoDTO->getDblIdDocumento(), ParticipanteRN::$TP_DESTINATARIO);
            $arrDataObservacaoOutrasUnidades = array();
            /** @var ObservacaoDTO $objObservacaoDTOOutrasUnidades */
            foreach($arrObjObservacaoDTOOutrasUnidades as $objObservacaoDTOOutrasUnidades) {
                $arrDataObservacaoOutrasUnidades[] = array(
                    'id' => $objObservacaoDTOOutrasUnidades->getNumIdObservacao(),
                    'sigla' => $objObservacaoDTOOutrasUnidades->getStrSiglaUnidade(),
                    'unidade' => $objObservacaoDTOOutrasUnidades->getStrDescricaoUnidade(),
                    'descricao' => $objObservacaoDTOOutrasUnidades->getStrDescricao(),
                );
            }

            $result = array(
                'nomeDocumento' => DocumentoINT::montarIdentificacaoArvore($objDocumentoDTO),
                'protocolo' => $objDocumentoDTO->getDblIdProtocoloProcedimento(),
                'idDocumento' => $objDocumentoDTO->getDblIdDocumento(),
                'idSerie' => $objDocumentoDTO->getNumIdSerie(),
                'nomeSerie' => $objDocumentoDTO->getStrNomeSerie(),
                'numero' => $objDocumentoDTO->getStrNumero(),
                'idTipoConferencia' => $objDocumentoDTO->getNumIdTipoConferencia(),
                'descricaoTipoConferencia' => $objDocumentoDTO->getStrDescricaoTipoConferencia(),
                'nivelAcesso' => $objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo(),
                'idHipoteseLegal' => $objDocumentoDTO->getNumIdHipoteseLegalProtocolo(),
                'nomeHipoteseLegal' => $objDocumentoDTO->getStrNomeHipoteseLegal(),
                'baseLegal' => $objDocumentoDTO->getStrBaseLegalHipoteseLegal(),
                'grauSigilo' => $objDocumentoDTO->getStrStaGrauSigiloProtocolo(),
                'descricao' => $objDocumentoDTO->getStrDescricaoProtocolo(),
                'dataElaboracao' => $objDocumentoDTO->getDtaGeracaoProtocolo(),
                'observacao' => ($objObservacaoDTO ? $objObservacaoDTO->getStrDescricao() : null),
                'assuntos' => $arrDataAssuntos,
                'interessados' => $arrDataInteressados,
                'destinatarios' => $arrDataDestinatarios,
                'observacoesUnidades' => $arrDataObservacaoOutrasUnidades,
            );
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método para verificação de permissões de acesso ao documento pelo usuário ativo
     */
    private function verificarAcessoProtocolo($paramDblIdProtocolo)
    {
        $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS);
        $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
        $objPesquisaProtocoloDTO->setDblIdProtocolo($paramDblIdProtocolo);

        $objProtocoloRN = new ProtocoloRN();
        $arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);
        return isset($arrObjProtocoloDTO) && count($arrObjProtocoloDTO);
    }

    /**
     * Método que retorna a lista de blocos de assinatura em que o documento se encontra
     * @param DocumentoDTO $documentoDTO
     * @return array
     */
    protected function listarBlocosAssinaturaConectado(DocumentoDTO $documentoDTO) {
        try{
            if(!$documentoDTO->isSetDblIdDocumento() || !$documentoDTO->getDblIdDocumento()){
                throw new InfraException('Documento não encontrado.');
            }

            $protocoloRN = new ProtocoloRN();
            $protocoloDTO = new ProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($documentoDTO->getDblIdDocumento());
            $protocoloDTO->setStrStaProtocolo(ProtocoloRN::$TP_PROCEDIMENTO, InfraDTO::$OPER_DIFERENTE);
            $protocoloDTO->retDblIdProtocolo();
            $protocoloDTO->retNumIdSerieDocumento();
            $protocoloDTO->retStrNomeSerieDocumento();
            $protocoloDTO->retStrProtocoloFormatado();
            $protocoloDTO->retDtaGeracao();

            /** Chamando o componente SEI para consulta de dados do Documento */
            $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);

            if(!$protocoloDTO){
                throw new InfraException('Documento não encontrado.');
            }

            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
            $relBlocoProtocoloDTO->retDblIdProtocolo();
            $relBlocoProtocoloDTO->retNumIdBloco();
            $relBlocoProtocoloDTO->setNumIdUnidadeBloco(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $relBlocoProtocoloDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            /** Chamando o componente SEI para retorno dos blocos do documento */
            $arrIdBlocos = array_keys(InfraArray::indexarArrInfraDTO($relBlocoProtocoloRN->listarRN1291($relBlocoProtocoloDTO), 'IdBloco'));

            $result = array(
                'idDocumento' => $protocoloDTO->getDblIdProtocolo(),
                'protocoloFormatado' => $protocoloDTO->getStrProtocoloFormatado(),
                'dataGeracao' => $protocoloDTO->getDtaGeracao(),
                'idSerie' => $protocoloDTO->getNumIdSerieDocumento(),
                'nomeSerie' => $protocoloDTO->getStrNomeSerieDocumento(),
                'blocos' => $arrIdBlocos
            );


            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}
