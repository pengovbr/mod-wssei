<?
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiBloco_V1_RN extends InfraRN {

  protected function inicializarObjInfraIBanco(){
      return BancoSEI::getInstance();
  }

    /**
     * M�todo que retorna o bloco de assinatura
     * @param BlocoDTO $blocoDTO
     * @return array
     */
  protected function retornarControlado(BlocoDTO $blocoDTO){
    try{
      if(!$blocoDTO->isSetNumIdBloco()){
        throw new Exception('Bloco n�o informado!');
      }
        $blocoRN = new BlocoRN();
        $blocoRN->retornar(array($blocoDTO));

        return MdWsSeiRest::formataRetornoSucessoREST('Bloco retornado com sucesso!');
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }


    /**
     * Assina todos os documentos do bloco
     * @param $idOrgao
     * @param $strCargoFuncao
     * @param $siglaUsuario
     * @param $senhaUsuario
     * @param $idUsuario
     * @return array
     */
  public function apiAssinarBloco($idBloco, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario)
    {
    try{
        sleep(3);
        $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
        $objRelBlocoProtocoloDTO->setNumIdBloco($idBloco);
        $objRelBlocoProtocoloDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
        $arrIdDocumentos = InfraArray::converterArrInfraDTO($objRelBlocoProtocoloRN->listarProtocolosBloco($objRelBlocoProtocoloDTO), 'IdProtocolo');
      if(!$arrIdDocumentos){
        return MdWsSeiRest::formataRetornoSucessoREST('Nenhum documento para ser assinado neste bloco.');
      }
        $assinaturaDTO = new AssinaturaDTO();
        $assinaturaDTO->setStrSiglaUsuario($siglaUsuario);
        $assinaturaDTO->setStrSenhaUsuario($senhaUsuario);
        $assinaturaDTO->setNumIdUsuario($idUsuario);
        $assinaturaDTO->setNumIdOrgaoUsuario($idOrgao);
        $assinaturaDTO->setStrCargoFuncao($strCargoFuncao);
        $assinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
        // $assinaturaDTO->setNumIdContextoUsuario(null);
        $assinaturaDTO->setArrObjDocumentoDTO(InfraArray::gerarArrInfraDTO('DocumentoDTO', 'IdDocumento', $arrIdDocumentos));
        $documentoRN = new DocumentoRN();
        $documentoRN->assinarInterno($assinaturaDTO);
        return MdWsSeiRest::formataRetornoSucessoREST('Documentos em bloco assinados com sucesso.');
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * Consultar Blocos
     * @param BlocoDTO $blocoDTO
     * @return array
     */
  protected function listarBlocoConectado(BlocoDTO $blocoDTO){
    try{
        $result = array();
        $blocoRN = new BlocoRN();
        $blocoDTOConsulta = new BlocoDTO();
      if(!$blocoDTO->getNumMaxRegistrosRetorno()){
        $blocoDTOConsulta->setNumMaxRegistrosRetorno(10);
      }else{
          $blocoDTOConsulta->setNumMaxRegistrosRetorno($blocoDTO->getNumMaxRegistrosRetorno());
      }
      if(empty($blocoDTO->getNumPaginaAtual())){
          $blocoDTOConsulta->setNumPaginaAtual(0);
      }else{
          $blocoDTOConsulta->setNumPaginaAtual($blocoDTO->getNumPaginaAtual());
      }

        $blocoDTOConsulta->setStrStaEstado(BlocoRN::$TE_CONCLUIDO, InfraDTO::$OPER_DIFERENTE);
        $blocoDTOConsulta->setStrStaTipo(BlocoRN::$TB_ASSINATURA);
        $blocoDTOConsulta->retNumIdBloco();
        $blocoDTOConsulta->retNumIdUnidade();
        $blocoDTOConsulta->retStrDescricao();
        $blocoDTOConsulta->retStrStaTipo();
        $blocoDTOConsulta->retStrStaEstado();
        $blocoDTOConsulta->retStrStaEstadoDescricao();
        $blocoDTOConsulta->retStrTipoDescricao();
        $blocoDTOConsulta->retStrSiglaUnidade();
        $blocoDTOConsulta->retStrDescricaoUnidade();
        $blocoDTOConsulta->retStrSinVazio();
        $blocoDTOConsulta->retArrObjRelBlocoUnidadeDTO();
        $blocoDTOConsulta->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);

        $ret = $blocoRN->pesquisar($blocoDTOConsulta);

        /** @var BlocoDTO $blocoDTO */
      foreach($ret as $blocoDTO){
          $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
          $relBlocoProtocoloDTOConsulta = new RelBlocoProtocoloDTO();
          $relBlocoProtocoloDTOConsulta->setNumMaxRegistrosRetorno(1);
          $relBlocoProtocoloDTOConsulta->setNumPaginaAtual(0);
          $relBlocoProtocoloDTOConsulta->setNumIdBloco($blocoDTO->getNumIdBloco());
          $relBlocoProtocoloDTOConsulta->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);
          $relBlocoProtocoloDTOConsulta->retDblIdProtocolo();
          $relBlocoProtocoloRN->listarRN1291($relBlocoProtocoloDTOConsulta);
          $numeroDocumentos = $relBlocoProtocoloDTOConsulta->getNumTotalRegistros();

          $arrUnidades = array();
          /** @var RelBlocoUnidadeDTO $relBlocoUnidadeDTO */
        foreach($blocoDTO->getArrObjRelBlocoUnidadeDTO() as $relBlocoUnidadeDTO){
            $arrUnidades[] = array(
                'idUnidade' => $relBlocoUnidadeDTO->getNumIdUnidade(),
                'unidade' => $relBlocoUnidadeDTO->getStrSiglaUnidade()
              );
        }
            $result[] = array(
                'id' => $blocoDTO->getNumIdBloco(),
                'atributos' => array(
                    'idBloco' => $blocoDTO->getNumIdBloco(),
                    'idUnidade' => $blocoDTO->getNumIdUnidade(),
                    'siglaUnidade' => $blocoDTO->getStrSiglaUnidade(),
                    'estado' => $blocoDTO->getStrStaEstado(),
                    'descricao' => $blocoDTO->getStrDescricao(),
                    'unidades' => $arrUnidades,
                    'numeroDocumentos' => $numeroDocumentos
                )
            );
      }
        return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $blocoDTOConsulta->getNumTotalRegistros());
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * Consultar Documentos por Bloco
     * @param BlocoDTO $blocoDTOConsulta
     * @return array
     */
  protected function listarDocumentosBlocoConectado(BlocoDTO $blocoDTOConsulta){
    try{
      //Regras de Negocio
	    $objInfraException = new InfraException();

      if(!$blocoDTOConsulta->getNumIdBloco()){
        $objInfraException->lancarValidacao('Bloco n�o informado.');
      }
        $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
        $relBlocoProtocoloDTOConsulta = new RelBlocoProtocoloDTO();
      if($blocoDTOConsulta->getNumMaxRegistrosRetorno()){
          $relBlocoProtocoloDTOConsulta->setNumMaxRegistrosRetorno($blocoDTOConsulta->getNumMaxRegistrosRetorno());
      }else{
          $relBlocoProtocoloDTOConsulta->setNumMaxRegistrosRetorno(10000000);
      }
      if(empty($blocoDTOConsulta->getNumPaginaAtual())){
          $relBlocoProtocoloDTOConsulta->setNumPaginaAtual(0);
      }else{
          $relBlocoProtocoloDTOConsulta->setNumPaginaAtual($blocoDTOConsulta->getNumPaginaAtual());
      }
        $result = array();
        $relBlocoProtocoloDTOConsulta->setNumIdBloco($blocoDTOConsulta->getNumIdBloco());
        $relBlocoProtocoloDTOConsulta->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);
        $relBlocoProtocoloDTOConsulta->retDblIdProtocolo();
        $relBlocoProtocoloDTOConsulta->retStrAnotacao();
        $relBlocoProtocoloDTOConsulta->retStrProtocoloFormatadoProtocolo();
        $arrRelProtocolo = $relBlocoProtocoloRN->listarRN1291($relBlocoProtocoloDTOConsulta);
      if($arrRelProtocolo){
          $anexoRN = new AnexoRN();
          $assinaturaRN = new AssinaturaRN();
          $protocoloRN = new ProtocoloRN();
          $protocoloProtocoloRN = new RelProtocoloProtocoloRN();
          /** @var RelBlocoProtocoloDTO $relBlocoProtocoloDTO */
        foreach($arrRelProtocolo as $relBlocoProtocoloDTO){
            $relProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
            $relProtocoloProtocoloDTO->setStrStaAssociacao($protocoloProtocoloRN::$TA_DOCUMENTO_CIRCULAR, InfraDTO::$OPER_DIFERENTE);
            $relProtocoloProtocoloDTO->setDblIdProtocolo2($relBlocoProtocoloDTO->getDblIdProtocolo());
            $relProtocoloProtocoloDTO->retDblIdProtocolo1();
            $relProtocoloProtocoloDTO = $protocoloProtocoloRN->consultarRN0841($relProtocoloProtocoloDTO);
            $arrResultAssinatura = array();
            $protocoloDTO = new ProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($relProtocoloProtocoloDTO->getDblIdProtocolo1());
            $protocoloDTO->retStrNomeSerieDocumento();
            $protocoloDTO->retStrProtocoloFormatado();
            $protocoloDTO->retDblIdProtocolo();
            $protocoloDTO->retDtaGeracao();
            $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);

            $protocoloDTODocumento = new ProtocoloDTO();
            $protocoloDTODocumento->retStrNomeSerieDocumento();
            $protocoloDTODocumento->setDblIdProtocolo($relBlocoProtocoloDTO->getDblIdProtocolo());
            $protocoloDTODocumento = $protocoloRN->consultarRN0186($protocoloDTODocumento);

            $assinaturaDTOConsulta = new AssinaturaDTO();
            $assinaturaDTOConsulta->setDblIdDocumento($relBlocoProtocoloDTO->getDblIdProtocolo());
            $assinaturaDTOConsulta->retStrNome();
            $assinaturaDTOConsulta->retStrTratamento();
            $assinaturaDTOConsulta->retNumIdUsuario();
            $arrAssinatura = $assinaturaRN->listarRN1323($assinaturaDTOConsulta);
            /** @var AssinaturaDTO $assinaturaDTO */
          foreach($arrAssinatura as $assinaturaDTO){
            $arrResultAssinatura[] = array(
            'nome' => $assinaturaDTO->getStrNome(),
            'cargo' => $assinaturaDTO->getStrTratamento(),
            'idUsuario' => $assinaturaDTO->getNumIdUsuario(),
            );
          }
            $anexoDTOConsulta = new AnexoDTO();
            $anexoDTOConsulta->retTodos();
            $anexoDTOConsulta->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $anexoDTOConsulta->setStrSinAtivo('S');
            $anexoDTOConsulta->setNumMaxRegistrosRetorno(1);
            $retAnexo = $anexoRN->listarRN0218($anexoDTOConsulta);
            $mimetype = null;
          if($retAnexo){
              $mimetype = $retAnexo[0]->getStrNome();
              $mimetype = substr($mimetype, strrpos($mimetype, '.')+1);
          }
            $result[] = array(
                'id' => $protocoloDTO->getDblIdProtocolo(),
                'atributos' => array(
                    'idDocumento' => $relBlocoProtocoloDTO->getDblIdProtocolo(),
                    'mimeType' => ($mimetype)?$mimetype:'html',
                    'data' => $protocoloDTO->getDtaGeracao(),
                    'numero' => $relBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo(),
                    'numeroProcesso' => $protocoloDTO->getStrProtocoloFormatado(),
                    'tipo' => $protocoloDTODocumento->getStrNomeSerieDocumento(),
                    'assinaturas' => $arrResultAssinatura
                ),
                'anotacao' => $relBlocoProtocoloDTO->getStrAnotacao()
            );
        }
      }


        return MdWsSeiRest::formataRetornoSucessoREST(null, $result, count($result));
    }catch (Exception $e){
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
      return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * Metodo publico que cadastra a anotacao em um bloco
     * @param array $post
     * @return array
     */
  public function cadastrarAnotacaoBlocoFromRequest(array $post){
      $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
    if($post['protocolo']){
        $relBlocoProtocoloDTO->setDblIdProtocolo($post['protocolo']);
    }
    if($post['bloco']){
        $relBlocoProtocoloDTO->setNumIdBloco($post['bloco']);
    }
    if($post['anotacao']){
        $relBlocoProtocoloDTO->setStrAnotacao($post['anotacao']);
    }

      return $this->cadastrarAnotacaoBloco($relBlocoProtocoloDTO);
  }

    /**
     * Cadastrar Anotacao documento do Bloco
     * @param RelBlocoProtocoloDTO $relBlocoProtocoloDTOParam
     * @return array
     */
  protected function cadastrarAnotacaoBlocoControlado(RelBlocoProtocoloDTO $relBlocoProtocoloDTOParam){

    try {
      //Regras de Negocio
	    $objInfraException = new InfraException();

      if (!$relBlocoProtocoloDTOParam->isSetNumIdBloco()) {
        $objInfraException->lancarValidacao('O bloco deve ser informado.');
      }
      if (!$relBlocoProtocoloDTOParam->isSetDblIdProtocolo()) {
        $objInfraException->lancarValidacao('O protocolo deve ser informado.');
      }
      if (!$relBlocoProtocoloDTOParam->isSetStrAnotacao()) {
        $objInfraException->lancarValidacao('A anota��o deve ser informada.');
      }
        $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
        $relBlocoProtocoloDTO->setNumIdBloco($relBlocoProtocoloDTOParam->getNumIdBloco());
        $relBlocoProtocoloDTO->setDblIdProtocolo($relBlocoProtocoloDTOParam->getDblIdProtocolo());
        $relBlocoProtocoloDTO->retTodos();
        $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
        $relBlocoProtocoloDTO = $relBlocoProtocoloRN->consultarRN1290($relBlocoProtocoloDTO);
      if (!$relBlocoProtocoloDTO) {
        $objInfraException->lancarValidacao('Documento n�o encontrado no bloco informado.');
      }
        $relBlocoProtocoloDTO->setStrAnotacao($relBlocoProtocoloDTOParam->getStrAnotacao());
        $relBlocoProtocoloRN->alterarRN1288($relBlocoProtocoloDTO);

        return MdWsSeiRest::formataRetornoSucessoREST('Anota��o realizada com sucesso.');
    }catch (Exception $e){
      if($objInfraException->contemValidacoes()){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e), LogSEI::$INFORMACAO);
      }else{
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
      }
      return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

}