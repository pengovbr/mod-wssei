<?

require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiDocumento_V1_RN extends DocumentoRN {

    const NOME_ATRIBUTO_ANDAMENTO_DOCUMENTO = 'DOCUMENTO';

  protected function inicializarObjInfraIBanco() {
      return BancoSEI::getInstance();
  }

    /**
     * Alterar Se��o do documento
     * @param DocumentoDTO DocumentoDTO
     * @return array
     */
  public function alterarSecaoDocumento($dados) {
    try {
        $idDocumento = $dados["documento"];
        $numVersao = $dados["versao"];
        $arrSecoes = $dados["secoes"];

        // Cria��o do DTO de editor que realiza a edi��o das se��es.
        $objEditorDTO = new EditorDTO();

        $objEditorDTO->setDblIdDocumento($idDocumento); // Informa o id do documento
        $objEditorDTO->setNumVersao($numVersao); // N�mero da vers�o
        $objEditorDTO->setNumIdBaseConhecimento(null);
        $objEditorDTO->setStrSinIgnorarNovaVersao('N');

        // Percorre as se��es do documento alteradas
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

        // Realiza a altera��o das se��es.
        $objEditorRN = new EditorRN();
        $numVersao = $objEditorRN->adicionarVersao($objEditorDTO);


        return MdWsSeiRest::formataRetornoSucessoREST(null, $numVersao);
    } catch (Exception $e) {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * Listar Se��o do documento
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


        //Monta as se��es que precisam ser retornadas e resgata o n�mero da �ltima vers�o
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
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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
//PAR�METROS DE ENTRADA
//            $ID = 0;
//            $FILTER = '';
//            $START = 0;
//            $LIMIT = 5;
//            $favoritos = 'N';
        //REALIZA A BUSCA DE TODOS OS TIPOS DA UNIDADE FILTRANDO APENAS PELOS FAVORITOS. AP�S A BUSCA, OS FILTROS POR ID, NOME E APLICABILIDADE DEVER�O SER FEITOS PERCORRENDO CADA UM DOS TIPOS.

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

            // $objDtoFormatado = str_replace('?', '', strtolower(iconv('ISO-8859-1', 'ASCII//TRANSLIT', $aux->getStrNome())));
            // $nomeFormatado = str_replace('?', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome)));
            $objDtoFormatado = str_replace('?', '', strtolower(mb_convert_encoding($aux->getStrNome(), 'ASCII', 'ISO-8859-1')));
            $nomeFormatado = str_replace('?', '', strtolower(mb_convert_encoding($nome, 'ASCII', 'UTF-8')));

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


      if ($start) {
          $arrayRetorno = array_slice($arrayRetorno, ($start - 1));
      }
      if ($limit) {
          $arrayRetorno = array_slice($arrayRetorno, 0, ($limit));
      }

        return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno, $total);
    } catch (Exception $e) {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * O servi�o de consulta de template de cria��o de processo informa ao client todas as varia��es existentes em um fomul�rio de cria��o de um documento. Entre essas varia��es est�o: Assuntos Sugeridos , Exist�ncia de Destinat�rios_ e Exist�ncia de Interessados_ .
     * @param MdWsSeiDocumentoDTO $dto
     * @return array
     */
  protected function pesquisarTemplateDocumentoConectado(MdWsSeiDocumentoDTO $dto) {
    try {
      //Regras de Negocio
	    $objInfraException = new InfraException();

      if (!$dto->getNumIdTipoDocumento()) {
        $objInfraException->lancarValidacao('Tipo de documento � uma informa��o obrigat�ria.');
      }

      if (!$dto->getNumIdProcesso()) {
        $objInfraException->lancarValidacao('O id do processo � obrigat�rio.');
      }

        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->setDblIdProcedimento($dto->getNumIdProcesso());
        $objProcedimentoDTO->retNumIdTipoProcedimento();

        $objProcedimentoRN = new ProcedimentoRN();
        $objProcedimentoDTO = $objProcedimentoRN->listarRN0278($objProcedimentoDTO);

      if (!$objProcedimentoDTO) {
        $objInfraException->lancarValidacao('N�o foi encontrado processo com id ' . $dto->getNumIdProcesso());
      }

        // Consulta se o tipo de documento permite a inclus�o de destinat�rios e interessados
        $serieDTO = new SerieDTO();
        $serieDTO->setNumIdSerie($dto->getNumIdTipoDocumento());
        $serieDTO->retStrSinDestinatario();
        $serieDTO->retStrSinInteressado();

        $serieRN = new SerieRN();
        $arrSerieDTO = $serieRN->listarRN0646($serieDTO);

      if (!$arrSerieDTO) {
        $objInfraException->lancarValidacao('N�o foi encontrado processo um tipo de processo ' . $dto->getNumIdTipoDocumento());
      }

        $id_tipo_documento = $dto->getNumIdTipoDocumento();
        //$idTipoProcedimento = $dto->getNumIdTipoProcedimento();
        $idProcedimento = $dto->getNumIdProcesso();
        //$idProcedimento = $dto->getNumProcedimento();
        //Consulta os assuntos sugeridos para um tipo de documento
        $relSerieAssuntoDTO = new RelSerieAssuntoDTO();
        $relSerieAssuntoDTO->setNumIdSerie($id_tipo_documento); // FILTRO PELO TIPO DE DOCUMENTO
        $relSerieAssuntoDTO->retNumIdAssuntoProxy(); // ID DO ASSUNTO QUE DEVE SE RETORNADO
        $relSerieAssuntoDTO->retStrCodigoEstruturadoAssunto(); // C�DIGO DO ASSUNTO QUE DEVE SE RETORNADO
        $relSerieAssuntoDTO->retStrDescricaoAssunto(); // DESCRI��O DO ASSUNTO

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
        if ($serie->getStrSinInteressado() == "N") {
            $permiteInteressados = false;
        }
        if ($serie->getStrSinDestinatario() == "N") {
            $permiteDestinatarios = false;
        }
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
          $nivelAcessoPermitidoDTO->retStrStaNivelAcesso(); // ID DO N�VEL DE ACESSO - ProtocoloRN::$NA_PUBLICO, ProtocoloRN::$NA_RESTRITO ou ProtocoloRN::$NA_SIGILOSO


          $nivelAcessoPermitidoRN = new NivelAcessoPermitidoRN();
          $arrNivelAcessoPermitido = $nivelAcessoPermitidoRN->listar($nivelAcessoPermitidoDTO);
        if ($arrNivelAcessoPermitido) {
          foreach ($arrNivelAcessoPermitido as $nivel) {
            if ($nivel->getStrStaNivelAcesso() == ProtocoloRN::$NA_PUBLICO) {
              $publico = true;
            }
            if ($nivel->getStrStaNivelAcesso() == ProtocoloRN::$NA_RESTRITO) {
                $restrito = true;
            }
            if ($nivel->getStrStaNivelAcesso() == ProtocoloRN::$NA_SIGILOSO) {
                $sigiloso = true;
            }
          }
        }

          $arrayRetorno["nivelAcessoPermitido"] = array(
              "publico" => $publico ? $publico : false,
              "restrito" => $restrito ? $restrito : false,
              "sigiloso" => $sigiloso ? $sigiloso : false,
          );
      }

      if(!$permiteInteressados) {
          $interessados =null;
      }

        $arrayRetorno = array(
            "assuntos" => $assuntos,
            "interessados" => empty($interessados) ? array() : $interessados,
            "nivelAcessoPermitido" => empty($arrayRetorno["nivelAcessoPermitido"]) ? array() : $arrayRetorno["nivelAcessoPermitido"],
            "permiteInteressados" => $permiteInteressados,
            "permiteDestinatarios" => $permiteDestinatarios
        );


        //CONSULTA NO PAR�METRO QUE INFORMA SE A HIP�TESE LEGAL � OBRIGAT�RIO PARA UM TIPO DE PROCESSO
        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $obrigatoriedadeHipoteseLegal = $objInfraParametro->getValor('SEI_HABILITAR_HIPOTESE_LEGAL');

        //CONSULTA NO PAR�METRO QUE INFORMA SE UM GRAU DE SIGILO � OBRIGAT�RIO PARA UM TIPO DE PROCESSO
        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $obrigatoriedadeGrauSigilo = $objInfraParametro->getValor('SEI_HABILITAR_GRAU_SIGILO');

        $arrayRetorno["obrigatoriedadeHipoteseLegal"] = $obrigatoriedadeHipoteseLegal;
        $arrayRetorno["obrigatoriedadeGrauSigilo"] = $obrigatoriedadeGrauSigilo;



        return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno);
    } catch (Exception $e) {
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
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
      //Regras de Negocio
	    $objInfraException = new InfraException();

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
        $descricao = $dados['descricao'];

        //Altera os dados do documento
        $protocoloDTO = new ProtocoloDTO();
        $protocoloDTO->setDblIdProtocolo($documento);
        $protocoloDTO->setDtaGeracao($data);
        $protocoloDTO->setStrNumeroDocumento($numero);
        $protocoloDTO->setStrDescricao($descricao);
        $protocoloDTO->setStrStaNivelAcessoLocal($nivelAcesso);
        $protocoloDTO->setNumIdHipoteseLegal($hipoteseLegal);
        $protocoloDTO->setStrStaGrauSigilo($grauSigilo);


        $protocoloDTOauxiliar = new ProtocoloDTO();
        $protocoloDTOauxiliar->setDblIdProtocolo($documento);
        $protocoloDTOauxiliar->retStrStaProtocolo();
        $protocoloRN = new ProtocoloRN();
        $retProtoculoDTO = $protocoloRN->consultarRN0186($protocoloDTOauxiliar);

      if(empty($retProtoculoDTO)){
        $objInfraException->lancarValidacao('Documento n�o encontrado.');
      }

      if($retProtoculoDTO->getStrStaProtocolo() != ProtocoloRN::$TP_DOCUMENTO_RECEBIDO){
        $objInfraException->lancarValidacao('A altera��o deve ser apenas de documentos externos.');
      }

        //Altera os Destinat�rios, Remetentes e Interessados
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

        //Edita a observa��o
        $observacaoDTO = new ObservacaoDTO();
        $observacaoDTO->setStrDescricao($observacao);
        $protocoloDTO->setArrObjObservacaoDTO(array($observacaoDTO));

        //Edita o tipo de documento e n�mero
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


        return MdWsSeiRest::formataRetornoSucessoREST(null);
    } catch (Exception $e) {
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
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
      //Regras de Negocio
	    $objInfraException = new InfraException();

        $documento = $dados['documento'];
        $idTipoDocumento = $dados['idTipoDocumento'];
        $arrAssuntos = $dados['assuntos'];
        $arrInteressados = $dados['interessados'];
        $arrDestinatarios = $dados['destinatarios'];
        $nivelAcesso = $dados['nivelAcesso'];
        $hipoteseLegal = $dados['hipoteseLegal'];
        $grauSigilo = $dados['grauSigilo'];
        $observacao = $dados['observacao'];
        $descricao = $dados['descricao'];

        //PAR�METROS DE ENTRADA
        //           $documento = 106;
        //           $descricao = "DESCRI��O E TESTE";
        //           $arrAssuntos = array(array('id' => 2), array('id' => 4));
        //           $arrInteressados = array(array('id' => 100000008), array('id' => 100000010), array('id' => 100000002), array('id' => 100000006));
        //           $arrDestinatarios =  array(array('id' => 100000008));
        //           $nivelAcesso = 0;
        //           $hipoteseLegal = "";
        //           $grauSigilo = "";
        //           $observacao = "OBSERVA��O TESTE UM";
        //Altera os dados do documento
        $protocoloDTO = new ProtocoloDTO();
        $protocoloDTO->setDblIdProtocolo($documento);
        $protocoloDTO->setStrDescricao($descricao);
        $protocoloDTO->setStrStaNivelAcessoLocal($nivelAcesso);
        $protocoloDTO->setNumIdHipoteseLegal($hipoteseLegal);
        $protocoloDTO->setStrStaGrauSigilo($grauSigilo);

        $protocoloDTOauxiliar = new ProtocoloDTO();
        $protocoloDTOauxiliar->setDblIdProtocolo($documento);
        $protocoloDTOauxiliar->retStrStaProtocolo();
        $protocoloRN = new ProtocoloRN();
        $retProtoculoDTO = $protocoloRN->consultarRN0186($protocoloDTOauxiliar);

      if(empty($retProtoculoDTO)){
        $objInfraException->lancarValidacao('Documento n�o encontrado.');
      }

      if($retProtoculoDTO->getStrStaProtocolo() != ProtocoloRN::$TP_DOCUMENTO_GERADO){
        $objInfraException->lancarValidacao('A altera��o deve ser apenas de documentos internos.');
      }


        //Altera os Destinat�rios e Interessados
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

        //Edita a observa��o
        $observacaoDTO = new ObservacaoDTO();
        $observacaoDTO->setStrDescricao($observacao);
        $protocoloDTO->setArrObjObservacaoDTO(array($observacaoDTO));

        //Edita o tipo de documento e n�mero
        $documentoDTO = new DocumentoDTO();
        $documentoDTO->setDblIdDocumento($documento);
        $documentoDTO->setObjProtocoloDTO($protocoloDTO);

        $documentoRN = new DocumentoRN();
        $documentoRN->alterarRN0004($documentoDTO);


        return MdWsSeiRest::formataRetornoSucessoREST(null);
    } catch (Exception $e) {
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
      return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * M�todo que cria um documento interno
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
//            $descricao = 'descri��o de teste';
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
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * M�todo que cria um documento interno
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


        //Par�metros de entrada
//            $idProcesso = 15;
//            $dataGeracaoDocumento = '25/01/2017';
//            $idTipoDocumento = 8;
//            $numero = '598714789156';
//            $descricao = 'descri��o de teste';
//            $nome_arquivo = 'teste.pdf';
//            $nivelAcesso = 1;
//            $hipoteseLegal = 1;
//            $grauSigilo = '';
//            $arrAssuntos = array(array('id' => 2), array('id' => 4));
//            $arrInteressados = array(array('id' => 100000008), array('id' => 100000010), array('id' => 100000002), array('id' => 100000006));
//            $arrDestinatarios = array(array('id' => 100000008));
//            $arrRemetentes = array(array('id' => 100000008));
//            $conteudoDocumento = file_get_contents('/opt/sei/web/modulos/mod-wssei/codigo-fonte/mod-wssei/rn/c.pdf'); // DEVE CONTER O BIN�RIO DO ARQUIVO. ESSE FILE_GET_CONTENTS � UM EXEMPLO APENAS
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

        //Popula os remetentes, destinat�rios e interessados
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
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * M�todo que retorna os documentos de um processo
     * @param DocumentoDTO $documentoDTOParam
     * @return array
     */
  protected function listarDocumentosProcessoConectado(DocumentoDTO $documentoDTOParam) {
    try {

        global $SEI_MODULOS;
        //Regras de Negocio
        $objInfraException = new InfraException();

        $arrDocHtml = array(
            DocumentoRN::$TD_EDITOR_EDOC,
            DocumentoRN::$TD_FORMULARIO_AUTOMATICO,
            DocumentoRN::$TD_FORMULARIO_GERADO,
            DocumentoRN::$TD_EDITOR_INTERNO
        );
        $result = array();
        $relProtocoloProtocoloDTOConsulta = new RelProtocoloProtocoloDTO();
        if (!$documentoDTOParam->isSetDblIdProcedimento()) {
          $objInfraException->lancarValidacao('O procedimento deve ser informado.');
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
      if (empty($documentoDTOParam->getNumPaginaAtual())) {
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


          $objProtocoloDTO = new ProtocoloDTO();
          $objProtocoloDTO->setDblIdProtocolo($relProtocoloProtocoloDTO->getDblIdProtocolo2());
          $objProtocoloDTO->retStrDescricao();
          $objTempProtocoloRN = new ProtocoloRN();
          $objProtocoloDTO = $objTempProtocoloRN->consultarRN0186($objProtocoloDTO);
          $informacao = $objProtocoloDTO->getStrDescricao();


          /*   if ($resultObservacao) {
                 // @var ObservacaoDTO $observacaoDTO
                 $observacaoDTO = $resultObservacao[0];
                 $informacao = substr($observacaoDTO->getStrDescricao(), 0, 250);
             }*/

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


          //recupera documentos disponibilizados pela unidade atual
          $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
          $objRelBlocoProtocoloDTO->setDistinct(true);
          $objRelBlocoProtocoloDTO->retDblIdProtocolo();
          $objRelBlocoProtocoloDTO->setNumIdUnidadeBloco(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
          $objRelBlocoProtocoloDTO->setStrStaTipoBloco(BlocoRN::$TB_ASSINATURA);
          $objRelBlocoProtocoloDTO->setStrStaEstadoBloco(BlocoRN::$TE_DISPONIBILIZADO);


          $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
          $arrDocumentosDisponibilizados = InfraArray::indexarArrInfraDTO($objRelBlocoProtocoloRN->listarRN1291($objRelBlocoProtocoloDTO), 'IdProtocolo');


        if (isset($arrDocumentosDisponibilizados[$documentoDTOParam->getDblIdProcedimento()])) {
            $disponibilizado = "S";
        } else {
            $disponibilizado = "N";
        }

          $strStaDocumento =  $documentoDTO->getStrStaDocumento();
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
          $arrObjRelBlocoUnidadeDTO = $objRelBlocoUnidadeRN->listarRN1304($objRelBlocoUnidadeDTO);


          //se tem blocos disponibilizados
        if (count($arrObjRelBlocoUnidadeDTO)){
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
            $arrObjRelBlocoProtocoloDTO = $objRelBlocoProtocoloRN->listarRN1291($objRelBlocoProtocoloDTO);

          if(count($arrObjRelBlocoProtocoloDTO)){
              $hasBloco = true;
          }
        }


        if((($documentoDTO->getStrStaDocumento() == DocumentoRN::$TD_EDITOR_INTERNO || $strStaDocumento==DocumentoRN::$TD_FORMULARIO_GERADO) &&
                  ($numIdUnidadeGeradoraProtocolo == $numIdUnidadeAtual && $strSinDisponibilizadoParaOutraUnidade == 'N')) || $hasBloco){
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
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
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
        sleep(3);
        $assinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
        // $assinaturaDTO->setNumIdContextoUsuario(null);
        $documentoRN = new DocumentoRN();
        $documentoRN->assinarInterno($assinaturaDTO);
        return MdWsSeiRest::formataRetornoSucessoREST('Documento em bloco assinado com sucesso.');
    } catch (Exception $e) {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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
      //Regras de Negocio
	    $objInfraException = new InfraException();

        $documentoRN = new DocumentoRN();
      if (!$documentoDTO->isSetDblIdDocumento()) {
        $objInfraException->lancarValidacao('O documento n�o foi informado.');
      }
        $documentoRN->darCiencia($documentoDTO);
        return MdWsSeiRest::formataRetornoSucessoREST('Ci�ncia documento realizado com sucesso.');
    } catch (Exception $e) {
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
      return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

  protected function downloadAnexoConectado(ProtocoloDTO $protocoloDTOParam) {
    try {
      //Regras de Negocio
	    $objInfraException = new InfraException();

      if (!$protocoloDTOParam->isSetDblIdProtocolo() || !$protocoloDTOParam->getDblIdProtocolo()) {
        $objInfraException->lancarValidacao('O protocolo deve ser informado!');
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
          $objInfraException->lancarValidacao('Documento sem conte�do!');
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
          $objInfraException->lancarValidacao('Documento n�o encontrado!');
        }
          $anexo = $resultAnexo[0];
          SeiINT::download($anexo);
          exit;
      }
    } catch (Exception $e) {
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
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
      //Regras de Negocio
	    $objInfraException = new InfraException();

      if (!$mdWsSeiProcessoDTOParam->isSetStrValor()) {
        $objInfraException->lancarValidacao('N�mero do documento n�o informado.');
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
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
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
      //Regras de Negocio
	    $objInfraException = new InfraException();

      if (!$documentoDTOParam->isSetDblIdDocumento()) {
        $objInfraException->lancarValidacao('O documento n�o foi informado.');
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
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
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
    if ($numCodigoAcesso > 0) {
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
        throw new Exception('N�o foi encontrado documento com id ' . $parNumIdDocumento);
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

        //Busca os interessados, destinat�rios e remetentes
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

        //Busca as observa��es
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
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

}
