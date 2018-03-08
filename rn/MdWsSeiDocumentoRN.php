<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdWsSeiDocumentoRN extends DocumentoRN {

    CONST NOME_ATRIBUTO_ANDAMENTO_DOCUMENTO = 'DOCUMENTO';

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    /**
     * Alterar Seção do documento
     * @param DocumentoDTO DocumentoDTO
     * @return array
     */
    public function alterarSecaoDocumento($dados) {
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
    protected function listarSecaoDocumentoConectado(DocumentoDTO $dto) {
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
    protected function pesquisarTipoDocumentoConectado(MdWsSeiDocumentoDTO $dto) {
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
                    if (
                            ($aux->getNumIdSerie() == $id || !$id) &&
                            (($nome && strpos(utf8_encode($aux->getStrNome()), $nome) !== false) || !$nome) &&
                            (in_array($aux->getStrStaAplicabilidade(), $aplicabilidade) == $aplicabilidade || !$aplicabilidade)
                    ) {
                        $arrayRetorno[] = array(
                            "id" => $aux->getNumIdSerie(),
                            "nome" => $aux->getStrNome()
                        );
                    }
                }
            }

            if ($start)
                $arrayRetorno = array_slice($arrayRetorno, ($start - 1));
            if ($limit)
                $arrayRetorno = array_slice($arrayRetorno, 0, ($limit));

            $total = 0;
            $total = count($arrayRetorno);

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
    protected function pesquisarTemplateDocumentoConectado(MdWsSeiDocumentoDTO $dto) {
        try {
            $id_tipo_documento = $dto->getNumIdTipoDocumento();
            $idTipoProcedimento = $dto->getNumIdTipoProcedimento();
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

            // Consulta se o tipo de documento permite a inclusão de destinatários e interessados
            $serieDTO = new SerieDTO();
            $serieDTO->setNumIdSerie($id_tipo_documento);
            $serieDTO->retStrSinDestinatario();
            $serieDTO->retStrSinInteressado();

            $serieRN = new SerieRN();
            $arrSerieDTO = $serieRN->listarRN0646($serieDTO);

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

            $arrayRetorno = array(
                "assuntos" => $assuntos,
                "permiteInteressados" => $permiteInteressados,
                "permiteDestinatarios" => $permiteDestinatarios
            );


            //CONSULTA QUE LISTA TODOS OS NÍVES DE ACESSOS PERMITIDOS PARA OS TIPO DE PROCESSO
            $nivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
            $nivelAcessoPermitidoDTO->setNumIdTipoProcedimento($idTipoProcedimento); // FILTRO PELO TIPO DE PROCESSO
            $nivelAcessoPermitidoDTO->retStrStaNivelAcesso(); // ID DO NÍVEL DE ACESSO - ProtocoloRN::$NA_PUBLICO, ProtocoloRN::$NA_RESTRITO ou ProtocoloRN::$NA_SIGILOSO
            // A CONSULTA RETORNARÁ OS NÍVEL DE ACESSO PERMITIDOS PARA O TIPO DE PROCESSO ESPECIFICADO NO DTO. AQUELES QUE NÃO FOREM RETORNADOS NESSA
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
     * Alterar Documento Externo
     * @param DocumentoDTO DocumentoDTO
     * @return array
     */
    public function alterarDocumentoExterno($dados) {
        try {
            $documento = $dados['documento'];
            $idTipoDocumento = $dados['idTipoDocumento'];
            $numero = $dados['numero'];
            $data = $dados['data'];
            $arrAssuntos = $dados['assuntos'];
            $arrInteressados = $dados['interessados'];
            $arrDestinatarios = $dados['destinatarios'];
            $arrRemetentes = $dados['remetentes'];
            $nivelAcesso = $dados['nivelAcesso'];
            $hipoteseLegal = $dados['hipoteseLegal'];
            $grauSigilo = $dados['grauSigilo'];
            $observacao = $dados['observacao'];
            $conteudoDocumento = $dados['conteudoDocumento'];
            $nomeArquivo = $dados['nomeArquivo'];
            $tipoConferencia = $dados['tipoConferencia'];


            //Altera os dados do documento    
            $protocoloDTO = new ProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($documento);
            $protocoloDTO->setDtaGeracao($data);
            $protocoloDTO->setStrNumeroDocumento($numero);
            $protocoloDTO->setStrDescricao($descricao);
            $protocoloDTO->setStrStaNivelAcessoLocal($nivelAcesso);
            $protocoloDTO->setNumIdHipoteseLegal($hipoteseLegal);
            $protocoloDTO->setStrStaGrauSigilo($grauSigilo);

            //Altera os Destinatários, Remetentes e Interessados
            $arrParticipantes = array();

            $i = 0;
            if ($arrInteressados) {
                foreach ($arrInteressados as $interessado) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($interessado['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
                    $objParticipanteDTO->setNumSequencia($i);
                    $i++;

                    $arrParticipantes[] = $objParticipanteDTO;
                }
            }

            $i = 0;
            if ($arrDestinatarios) {
                foreach ($arrDestinatarios as $destinatario) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($destinatario['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_DESTINATARIO);
                    $objParticipanteDTO->setNumSequencia($i);
                    $i++;

                    $arrParticipantes[] = $objParticipanteDTO;
                }
            }

            $i = 0;
            if ($arrRemetentes) {
                foreach ($arrRemetentes as $remetente) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($remetente['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
                    $objParticipanteDTO->setNumSequencia($i);
                    $i++;

                    $arrParticipantes[] = $objParticipanteDTO;
                }
            }

            $protocoloDTO->setArrObjParticipanteDTO($arrParticipantes);

            //Altera os assuntos
            $arrRelProtocoloAssuntoDTO = array();

            $i = 0;
            if ($arrAssuntos) {
                foreach ($arrAssuntos as $assunto) {
                    $relProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                    $relProtocoloAssuntoDTO->setNumIdAssunto($assunto['id']);
                    $relProtocoloAssuntoDTO->setDblIdProtocolo($documento);
                    $relProtocoloAssuntoDTO->setNumSequencia($i);
                    $arrRelProtocoloAssuntoDTO[] = $relProtocoloAssuntoDTO;

                    $i++;
                }
            }

            $protocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrRelProtocoloAssuntoDTO);

            //Edita a observação
            $observacaoDTO = new ObservacaoDTO();
            $observacaoDTO->setStrDescricao($observacao);
            $protocoloDTO->setArrObjObservacaoDTO(array($observacaoDTO));

            //Edita o tipo de documento e número
            $documentoDTO = new DocumentoDTO();
            $documentoDTO->setDblIdDocumento($documento);
            $documentoDTO->setNumIdSerie($idTipoDocumento);
            $documentoDTO->setStrNumero($numero);
            $documentoDTO->setObjProtocoloDTO($protocoloDTO);
            $documentoDTO->setNumIdTipoConferencia($tipoConferencia);

            if ($conteudoDocumento === false) {
                $objAnexoDTO = new AnexoDTO();
                $objAnexoDTO->retNumIdAnexo();
                $objAnexoDTO->setDblIdProtocolo($documento);

                $objAnexoRN = new AnexoRN();
                $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);
                $objAnexoRN->excluirRN0226($arrObjAnexoDTO);
            }
            if ($conteudoDocumento) {
                $objAnexoDTO = new AnexoDTO();
                $objAnexoDTO->setStrNome($nomeArquivo);
                $protocoloDTO->setArrObjAnexoDTO(array($objAnexoDTO));

                $documentoDTO->setStrConteudo(null);
                $documentoDTO->setStrStaDocumento(DocumentoRN::$TD_EXTERNO);

                $arrObjAnexoDTO = $documentoDTO->getObjProtocoloDTO()->getArrObjAnexoDTO();

                //Adiciona o anexo 
                if (count($arrObjAnexoDTO) == 1) {

                    if (!$arrObjAnexoDTO[0]->isSetNumIdAnexoOrigem()) {
                        $objAnexoRN = new AnexoRN();
                        $strNomeArquivoUpload = $objAnexoRN->gerarNomeArquivoTemporario();

                        $fp = fopen(DIR_SEI_TEMP . '/' . $strNomeArquivoUpload, 'w');
                        fwrite($fp, $conteudoDocumento);
                        fclose($fp);

                        $arrObjAnexoDTO[0]->setNumIdAnexo($strNomeArquivoUpload);
                        $arrObjAnexoDTO[0]->setDthInclusao(InfraData::getStrDataHoraAtual());
                        $arrObjAnexoDTO[0]->setNumTamanho(filesize(DIR_SEI_TEMP . '/' . $strNomeArquivoUpload));
                        $arrObjAnexoDTO[0]->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                    }
                }
            }


            $documentoRN = new DocumentoRN();
            $documentoRN->alterarRN0004($documentoDTO);


            return MdWsSeiRest::formataRetornoSucessoREST(nulL);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Alterar Documento Iterno
     * @param DocumentoDTO DocumentoDTO
     * @return array
     */
    public function alterarDocumentoInterno($dados) {
        try {
            $documento = $dados['documento'];
            $idTipoDocumento = $dados['idTipoDocumento'];
            $arrAssuntos = $dados['assuntos'];
            $arrInteressados = $dados['interessados'];
            $arrDestinatarios = $dados['destinatarios'];
            $nivelAcesso = $dados['nivelAcesso'];
            $hipoteseLegal = $dados['hipoteseLegal'];
            $grauSigilo = $dados['grauSigilo'];
            $observacao = $dados['observacao'];

            //PARÂMETROS DE ENTRADA
            //           $documento = 106;
            //           $descricao = "DESCRIÇÃO E TESTE";
            //           $arrAssuntos = array(array('id' => 2), array('id' => 4));
            //           $arrInteressados = array(array('id' => 100000008), array('id' => 100000010), array('id' => 100000002), array('id' => 100000006));
            //           $arrDestinatarios =  array(array('id' => 100000008));
            //           $nivelAcesso = 0;
            //           $hipoteseLegal = "";
            //           $grauSigilo = "";
            //           $observacao = "OBSERVAÇÃO TESTE UM";
            //Altera os dados do documento    
            $protocoloDTO = new ProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($documento);
            $protocoloDTO->setStrDescricao("asdadas");
            $protocoloDTO->setStrStaNivelAcessoLocal($nivelAcesso);
            $protocoloDTO->setNumIdHipoteseLegal($hipoteseLegal);
            $protocoloDTO->setStrStaGrauSigilo($grauSigilo);

            //Altera os Destinatários e Interessados
            $arrParticipantes = array();

            $i = 0;
            if ($arrInteressados) {
                foreach ($arrInteressados as $interessado) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($interessado['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
                    $objParticipanteDTO->setNumSequencia($i);
                    $i++;

                    $arrParticipantes[] = $objParticipanteDTO;
                }
            }

            $i = 0;
            if ($arrDestinatarios) {
                foreach ($arrDestinatarios as $destinatario) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($destinatario['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_DESTINATARIO);
                    $objParticipanteDTO->setNumSequencia($i);
                    $i++;

                    $arrParticipantes[] = $objParticipanteDTO;
                }
            }

            $protocoloDTO->setArrObjParticipanteDTO($arrParticipantes);

            //Altera os assuntos
            $arrRelProtocoloAssuntoDTO = array();

            $i = 0;
            if ($arrAssuntos) {
                foreach ($arrAssuntos as $assunto) {
                    $relProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                    $relProtocoloAssuntoDTO->setNumIdAssunto($assunto['id']);
                    $relProtocoloAssuntoDTO->setDblIdProtocolo($documento);
                    $relProtocoloAssuntoDTO->setNumSequencia($i);
                    $arrRelProtocoloAssuntoDTO[] = $relProtocoloAssuntoDTO;

                    $i++;
                }
            }
            $protocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrRelProtocoloAssuntoDTO);

            //Edita a observação
            $observacaoDTO = new ObservacaoDTO();
            $observacaoDTO->setStrDescricao($observacao);
            $protocoloDTO->setArrObjObservacaoDTO(array($observacaoDTO));

            //Edita o tipo de documento e número
            $documentoDTO = new DocumentoDTO();
            $documentoDTO->setDblIdDocumento($documento);
            $documentoDTO->setObjProtocoloDTO($protocoloDTO);

            $documentoRN = new DocumentoRN();
            $documentoRN->alterarRN0004($documentoDTO);


            return MdWsSeiRest::formataRetornoSucessoREST(nulL);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que cria um documento interno
     * @param MdWsSeiDocumentoDTO $dto
     * @return array
     */
    protected function documentoInternoCriarConectado(MdWsSeiDocumentoDTO $dto) {
        try {
            $idProcesso = $dto->getNumIdProcesso();
            $idTipoDocumento = $dto->getNumIdTipoDocumento();
            $descricao = $dto->getStrDescricao();
            $nivelAcesso = $dto->getStrNivelAcesso();
            $hipoteseLegal = $dto->getNumIdHipoteseLegal();
            $grauSigilo = $dto->getStrGrauSigilo();
            $arrAssuntos = $dto->getArrAssuntos();
            $arrInteressados = $dto->getArrInteressados();
            $arrDestinatarios = $dto->getArrDestinatarios();
            $observacao = $dto->getStrObservacao();

//            $idProcesso = 13;
//            $idTipoDocumento = 12;
//            $descricao = 'descrição de teste';
//            $nivelAcesso = 1;
//            $hipoteseLegal = 1;
//            $grauSigilo = '';
//            $arrAssuntos = array(array('id' => 2), array('id' => 4));
//            $arrInteressados = array(array('id' => 100000008), array('id' => 100000010), array('id' => 100000002), array('id' => 100000006));
//            $arrDestinatarios = array(array('id' => 100000008));
//            $observacao = 'teste';

            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($idProcesso);

            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo(null);
            $objProtocoloDTO->setStrStaProtocolo('G');
            // $objProtocoloDTO->setDtaGeracao($dtaGeracao);

            $objDocumentoDTO->setNumIdSerie($idTipoDocumento);
            // $objDocumentoDTO->setStrNomeSerie($nomeTipo);

            $objDocumentoDTO->setDblIdDocumentoEdoc(null);
            $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objDocumentoDTO->setNumIdTipoConferencia(null);
            $objDocumentoDTO->setStrNumero('');
            // $objDocumentoDTO->setNumIdTipoConferencia($objDocumentoAPI->getIdTipoConferencia());

            $objProtocoloDTO->setStrStaNivelAcessoLocal($nivelAcesso);
            $objProtocoloDTO->setNumIdHipoteseLegal($hipoteseLegal);
            $objProtocoloDTO->setStrDescricao($descricao);
            $objProtocoloDTO->setStrStaGrauSigilo($grauSigilo);

            $arrParticipantesDTO = array();
            if ($arrInteressados) {
                foreach ($arrInteressados as $k => $interessado) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($interessado['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
                    $objParticipanteDTO->setNumSequencia($k);
                    $arrParticipantesDTO[] = $objParticipanteDTO;
                }
            }

            if ($arrDestinatarios) {
                foreach ($arrDestinatarios as $k => $destinatario) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($destinatario['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_DESTINATARIO);
                    $objParticipanteDTO->setNumSequencia($k);
                    $arrParticipantesDTO[] = $objParticipanteDTO;
                }
            }
            $arrRelProtocoloAssuntoDTO = array();

            if ($arrAssuntos) {
                foreach ($arrAssuntos as $k => $assunto) {
                    $relProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                    $relProtocoloAssuntoDTO->setNumIdAssunto($assunto['id']);
                    $relProtocoloAssuntoDTO->setDblIdProtocolo($idProcesso);
                    $relProtocoloAssuntoDTO->setNumSequencia($k);
                    $arrRelProtocoloAssuntoDTO[] = $relProtocoloAssuntoDTO;
                }
            }

            $objProtocoloDTO->setArrObjParticipanteDTO($arrParticipantesDTO);
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrRelProtocoloAssuntoDTO);

            //OBSERVACOES
            $objObservacaoDTO = new ObservacaoDTO();
            $objObservacaoDTO->setStrDescricao($observacao);
            $objProtocoloDTO->setArrObjObservacaoDTO(array($objObservacaoDTO));

            $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);
            $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);

            $objDocumentoRN = new DocumentoRN();
            $obj = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

            $arrayRetorno = array();
            if ($obj) {
                $arrayRetorno = array(
                    "IdDocumento" => $obj->getDblIdDocumento(),
                    "ProtocoloDocumentoFormatado" => $obj->getStrProtocoloDocumentoFormatado()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que cria um documento interno
     * @param MdWsSeiDocumentoDTO $dto
     * @return array
     */
    protected function documentoExternoCriarConectado(MdWsSeiDocumentoDTO $dto) {
        try {
            $idProcesso = $dto->getNumIdProcesso();
            $idTipoDocumento = $dto->getNumIdTipoDocumento();
            $dataGeracaoDocumento = $dto->getDtaDataGeracaoDocumento();
            $numero = $dto->getStrNumero();
            $descricao = $dto->getStrDescricao();
            $nomeArquivo = $dto->getStrNomeArquivo();
            $nivelAcesso = $dto->getStrNivelAcesso();
            $hipoteseLegal = $dto->getNumIdHipoteseLegal();
            $grauSigilo = $dto->getStrGrauSigilo();
            $arrAssuntos = $dto->getArrAssuntos();
            $arrInteressados = $dto->getArrInteressados();
            $arrDestinatarios = $dto->getArrDestinatarios();
            $arrRemetentes = $dto->getArrRemetentes();
            $conteudoDocumento = $dto->getStrConteudoDocumento();
            $observacao = $dto->getStrObservacao();
            $tipoConferencia = $dto->getNumTipoConferencia();


            //Parâmetros de entrada 
//            $idProcesso = 15;
//            $dataGeracaoDocumento = '25/01/2017';
//            $idTipoDocumento = 8;
//            $numero = '598714789156';
//            $descricao = 'descrição de teste';
//            $nome_arquivo = 'teste.pdf';
//            $nivelAcesso = 1;
//            $hipoteseLegal = 1;
//            $grauSigilo = '';
//            $arrAssuntos = array(array('id' => 2), array('id' => 4));
//            $arrInteressados = array(array('id' => 100000008), array('id' => 100000010), array('id' => 100000002), array('id' => 100000006));
//            $arrDestinatarios = array(array('id' => 100000008));
//            $arrRemetentes = array(array('id' => 100000008));
//            $conteudoDocumento = file_get_contents('/opt/sei/web/modulos/mod-wssei/codigo-fonte/mod-wssei/rn/c.pdf'); // DEVE CONTER O BINÁRIO DO ARQUIVO. ESSE FILE_GET_CONTENTS É UM EXEMPLO APENAS
//            $observacao = 'ewefwe';
            //Popula os dados do documento para salvamento 
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento(null);
            $objDocumentoDTO->setDblIdProcedimento($idProcesso);
            $objDocumentoDTO->setNumIdSerie($idTipoDocumento);
            $objDocumentoDTO->setDblIdDocumentoEdoc(null);
            $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
            $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objDocumentoDTO->setNumIdTipoConferencia($tipoConferencia);
            $objDocumentoDTO->setStrNumero($numero);

            //Popula os dados do protocolo do documento
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo(null);
            $objProtocoloDTO->setStrStaProtocolo('R');
            $objProtocoloDTO->setDtaGeracao($dataGeracaoDocumento);
            $objProtocoloDTO->setStrStaNivelAcessoLocal($nivelAcesso);
            $objProtocoloDTO->setNumIdHipoteseLegal($hipoteseLegal);
            $objProtocoloDTO->setStrDescricao($descricao);
            $objProtocoloDTO->setStrStaGrauSigilo($grauSigilo);

            //Popula os remetentes, destinatários e interessados 
            $arrParticipantesDTO = array();
            if ($arrRemetentes) {
                foreach ($arrRemetentes as $k => $remetente) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($remetente['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
                    $objParticipanteDTO->setNumSequencia($k);
                    $arrParticipantesDTO[] = $objParticipanteDTO;
                }
            }
            if ($arrInteressados) {
                foreach ($arrInteressados as $k => $interessado) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($interessado['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
                    $objParticipanteDTO->setNumSequencia($k);
                    $arrParticipantesDTO[] = $objParticipanteDTO;
                }
            }
            if ($arrDestinatarios) {
                foreach ($arrDestinatarios as $k => $destinatario) {
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($destinatario['id']);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_DESTINATARIO);
                    $objParticipanteDTO->setNumSequencia($k);
                    $arrParticipantesDTO[] = $objParticipanteDTO;
                }
            }
            //Popula os assuntos
            $arrRelProtocoloAssuntoDTO = array();
            if ($arrAssuntos) {
                foreach ($arrAssuntos as $k => $assunto) {
                    $relProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                    $relProtocoloAssuntoDTO->setNumIdAssunto($assunto['id']);
                    $relProtocoloAssuntoDTO->setDblIdProtocolo($idProcesso);
                    $relProtocoloAssuntoDTO->setNumSequencia($k);
                    $arrRelProtocoloAssuntoDTO[] = $relProtocoloAssuntoDTO;
                }
            }
            $objProtocoloDTO->setArrObjParticipanteDTO($arrParticipantesDTO);
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrRelProtocoloAssuntoDTO);

            //OBSERVACOES
            $objObservacaoDTO = new ObservacaoDTO();
            $objObservacaoDTO->setStrDescricao($observacao);
            $objProtocoloDTO->setArrObjObservacaoDTO(array($objObservacaoDTO));

            $objAnexoDTO = new AnexoDTO();
            $objAnexoDTO->setStrNome($nomeArquivo);
            $objProtocoloDTO->setArrObjAnexoDTO(array($objAnexoDTO));

            $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);
            $objDocumentoDTO->setStrConteudo(null);
            $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EXTERNO);

            $arrObjAnexoDTO = $objDocumentoDTO->getObjProtocoloDTO()->getArrObjAnexoDTO();

            //Adiciona o anexo 
            if (count($arrObjAnexoDTO) == 1) {

                if (!$arrObjAnexoDTO[0]->isSetNumIdAnexoOrigem()) {
                    $objAnexoRN = new AnexoRN();
                    $strNomeArquivoUpload = $objAnexoRN->gerarNomeArquivoTemporario();

                    $fp = fopen(DIR_SEI_TEMP . '/' . $strNomeArquivoUpload, 'w');
                    fwrite($fp, $conteudoDocumento);
                    fclose($fp);

                    $arrObjAnexoDTO[0]->setNumIdAnexo($strNomeArquivoUpload);
                    $arrObjAnexoDTO[0]->setDthInclusao(InfraData::getStrDataHoraAtual());
                    $arrObjAnexoDTO[0]->setNumTamanho(filesize(DIR_SEI_TEMP . '/' . $strNomeArquivoUpload));
                    $arrObjAnexoDTO[0]->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                }
            }

            //Gera o documento
            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTOGerado = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);



            $arrayRetorno = array();
            if ($objDocumentoDTOGerado) {
                $arrayRetorno = array(
                    "IdDocumento" => $objDocumentoDTOGerado->getDblIdDocumento(),
                    "ProtocoloDocumentoFormatado" => $objDocumentoDTOGerado->getStrProtocoloDocumentoFormatado()
                );
            }

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
    protected function listarDocumentosProcessoConectado(DocumentoDTO $documentoDTOParam) {
        try {
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
                $retDocumentos = $documentoBD->listar($documentoDTOConsulta);

//                var_dump($retDocumentos);
//                die();
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
                $observacaoDTOConsulta = new ObservacaoDTO();
                $observacaoDTOConsulta->setNumMaxRegistrosRetorno(1);
                $observacaoDTOConsulta->setOrdNumIdObservacao(InfraDTO::$TIPO_ORDENACAO_DESC);
                $observacaoDTOConsulta->retStrDescricao();
                $resultObservacao = $observacaoRN->listarRN0219($observacaoDTOConsulta);
                if ($resultObservacao) {
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

                $arrObjProtocoloDTO = "";
                $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
                $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS_GERADOS);
                $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_TODOS);
                $objPesquisaProtocoloDTO->setDblIdProtocolo($relProtocoloProtocoloDTO->getDblIdProtocolo2());
                $objProtocoloRN = new ProtocoloRN();
                $arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);

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
                            'permiteAssinatura' => $arrObjProtocoloDTO ? $arrObjProtocoloDTO[0]->getStrSinAssinado() : ""
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
    public function apiAssinarDocumentos($arrIdDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario) {
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
    public function apiAssinarDocumento($idDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario) {
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
    public function assinarDocumentoControlado(AssinaturaDTO $assinaturaDTO) {
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
    protected function darCienciaControlado(DocumentoDTO $documentoDTO) {
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

    protected function downloadAnexoConectado(ProtocoloDTO $protocoloDTOParam) {
        try {
            if (!$protocoloDTOParam->isSetDblIdProtocolo() || !$protocoloDTOParam->getDblIdProtocolo()) {
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
    protected function listarCienciaDocumentoConectado(MdWsSeiProcessoDTO $mdWsSeiProcessoDTOParam) {
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
    protected function listarAssinaturasDocumentoConectado(DocumentoDTO $documentoDTOParam) {
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
    protected function podeVisualizarDocumento(DocumentoDTO $documentoDTO, $bolFlagProtocolo = false) {
        $podeVisualizar = false;
        $pesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $pesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_TODOS);
        $pesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_TODOS);
        $pesquisaProtocoloDTO->setDblIdProtocolo($documentoDTO->getDblIdDocumento());
        $protocoloRN = new ProtocoloRN();
        $arrProtocoloDTO = InfraArray::indexarArrInfraDTO($protocoloRN->pesquisarRN0967($pesquisaProtocoloDTO), 'IdProtocolo');
        $protocoloDTODocumento = $arrProtocoloDTO[$documentoDTO->getDblIdDocumento()];

        $numCodigoAcesso = $protocoloDTODocumento->getNumCodigoAcesso();
        if ($numCodigoAcesso > 0 || $bolFlagProtocolo) {
            $podeVisualizar = true;
        }
        if ($documentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_DOCUMENTO_CANCELADO) {
            $podeVisualizar = false;
        }

        return $podeVisualizar;
    }

    public function consultarDocumento($parNumIdDocumento) {

        try {
            $arrDadosDocumento = array();

            //Busca o tipo de documento
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento($parNumIdDocumento);
            $objDocumentoDTO->retNumIdSerie();
            $objDocumentoDTO->retStrNumero();

            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

            if (!$objDocumentoDTO) {
                throw new Exception('Não foi encontrado documento com id ' . $parNumIdDocumento);
            }

            $arrDadosDocumento['tipoDocumento'] = $objDocumentoDTO->getNumIdSerie();
            $arrDadosDocumento['numero'] = $objDocumentoDTO->getStrNumero();

            //Busca os assuntos
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($parNumIdDocumento);
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();
            $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
            $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();
            $objRelProtocoloAssuntoDTO->retNumSequencia();
            $objRelProtocoloAssuntoDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $objArrRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

            if ($objArrRelProtocoloAssuntoDTO) {
                foreach ($objArrRelProtocoloAssuntoDTO as $key => $objProtocoloAssuntoDTO) {
                    $arrDadosDocumento['assuntos'][$key]['id'] = $objProtocoloAssuntoDTO->getNumIdAssunto();
                    $arrDadosDocumento['assuntos'][$key]['codigo'] = $objProtocoloAssuntoDTO->getStrCodigoEstruturadoAssunto();
                    $arrDadosDocumento['assuntos'][$key]['descricao'] = $objProtocoloAssuntoDTO->getStrDescricaoAssunto();
                }
            }

            //Busca os interessados, destinatários e remetentes
            $objParticipanteDTO = new ParticipanteDTO();
            $objParticipanteDTO->setDblIdProtocolo($parNumIdDocumento);
            $objParticipanteDTO->setStrStaParticipacao(array(ParticipanteRN::$TP_INTERESSADO, ParticipanteRN::$TP_DESTINATARIO, ParticipanteRN::$TP_REMETENTE), InfraDTO::$OPER_IN);
            $objParticipanteDTO->retNumIdContato();
            $objParticipanteDTO->retStrStaParticipacao();
            $objParticipanteDTO->retStrSiglaContato();
            $objParticipanteDTO->retStrNomeContato();
            $objParticipanteDTO->retNumSequencia();
            $objParticipanteDTO->setOrdStrStaParticipacao(InfraDTO::$TIPO_ORDENACAO_ASC);
            $objParticipanteDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);
            
            $objParticipanteRN = new ParticipanteRN();
            $objArrParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);
            $arrDadosDocumento['interessados'] = array();
            $arrDadosDocumento['destinatarios'] = array();
            $arrDadosDocumento['remetentes'] = array();

            if ($objArrParticipanteDTO) {
                foreach ($objArrParticipanteDTO as $key => $objParticipanteDTO) {
                    if ($objParticipanteDTO->getStrStaParticipacao() == ParticipanteRN::$TP_INTERESSADO) {
                        $arrDadosDocumento['interessados'][$objParticipanteDTO->getNumSequencia()]['id'] = $objParticipanteDTO->getNumIdContato();
                        $arrDadosDocumento['interessados'][$objParticipanteDTO->getNumSequencia()]['sigla'] = $objParticipanteDTO->getStrSiglaContato();
                        $arrDadosDocumento['interessados'][$objParticipanteDTO->getNumSequencia()]['nome'] = $objParticipanteDTO->getStrNomeContato();
                    }

                    if ($objParticipanteDTO->getStrStaParticipacao() == ParticipanteRN::$TP_DESTINATARIO) {
                        $arrDadosDocumento['destinatarios'][$objParticipanteDTO->getNumSequencia()]['id'] = $objParticipanteDTO->getNumIdContato();
                        $arrDadosDocumento['destinatarios'][$objParticipanteDTO->getNumSequencia()]['sigla'] = $objParticipanteDTO->getStrSiglaContato();
                        $arrDadosDocumento['destinatarios'][$objParticipanteDTO->getNumSequencia()]['nome'] = $objParticipanteDTO->getStrNomeContato();
                    }

                    if ($objParticipanteDTO->getStrStaParticipacao() == ParticipanteRN::$TP_REMETENTE) {
                        $arrDadosDocumento['remetentes'][$objParticipanteDTO->getNumSequencia()]['id'] = $objParticipanteDTO->getNumIdContato();
                        $arrDadosDocumento['remetentes'][$objParticipanteDTO->getNumSequencia()]['sigla'] = $objParticipanteDTO->getStrSiglaContato();
                        $arrDadosDocumento['remetentes'][$objParticipanteDTO->getNumSequencia()]['nome'] = $objParticipanteDTO->getStrNomeContato();
                    }
                }
            }


            //Busca os dados do documento referentes ao protocolo
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo($parNumIdDocumento);
            $objProtocoloDTO->retStrDescricao();
            $objProtocoloDTO->retStrStaNivelAcessoLocal();
            $objProtocoloDTO->retNumIdHipoteseLegal();
            $objProtocoloDTO->retStrStaGrauSigilo();
            $objProtocoloDTO->retDtaGeracao();
            $objProtocoloDTO->retNumIdTipoConferenciaDocumento();

            $objProtocoloRN = new ProtocoloRN();
            $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

            $arrDadosDocumento['descricao'] = $objProtocoloDTO->getStrDescricao();
            $arrDadosDocumento['nivelAcesso'] = $objProtocoloDTO->getStrStaNivelAcessoLocal();
            $arrDadosDocumento['hipoteseLegal'] = $objProtocoloDTO->getNumIdHipoteseLegal();
            $arrDadosDocumento['grauSigilo'] = $objProtocoloDTO->getStrStaGrauSigilo();
            $arrDadosDocumento['dataGeracao'] = $objProtocoloDTO->getDtaGeracao();
            $arrDadosDocumento['tipoConferencia'] = $objProtocoloDTO->getNumIdTipoConferenciaDocumento();

            //Busca as observações
            $objObservacaoDTO = new ObservacaoDTO();
            $objObservacaoDTO->setDblIdProtocolo($parNumIdDocumento);
            $objObservacaoDTO->retNumIdUnidade();
            $objObservacaoDTO->retStrSiglaUnidade();
            $objObservacaoDTO->retStrDescricaoUnidade();
            $objObservacaoDTO->retStrDescricao();

            $objObservacaoRN = new ObservacaoRN();
            $arrObjObservacaoDTO = $objObservacaoRN->listarRN0219($objObservacaoDTO);

            if ($arrObjObservacaoDTO) {
                foreach ($arrObjObservacaoDTO as $key => $objObservacaoDTO) {
                    $arrDadosDocumento['observacoes'][$key]['unidade'] = $objObservacaoDTO->getNumIdUnidade();
                    $arrDadosDocumento['observacoes'][$key]['siglaUnidade'] = $objObservacaoDTO->getStrSiglaUnidade();
                    $arrDadosDocumento['observacoes'][$key]['nomeUnidade'] = $objObservacaoDTO->getStrDescricaoUnidade();
                    $arrDadosDocumento['observacoes'][$key]['observacao'] = $objObservacaoDTO->getStrDescricao();
                }
            } else {
                $arrDadosDocumento['observacao'] = array();
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrDadosDocumento);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}
