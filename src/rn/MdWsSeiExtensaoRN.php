<?php
require_once DIR_SEI_WEB . '/SEI.php';


class MdWsSeiExtensaoRN extends InfraRN {

  protected function inicializarObjInfraIBanco(){
      return BancoSEI::getInstance();
  }

    /**
     * Retorna os parametros para upload de arquivo
     * @return array
     */
  protected function retornarParametrosUploadConectado()
    {
    try{
      //Regras de Negocio
        $objInfraException = new InfraException();

        $infraParametro = new InfraParametro(BancoSEI::getInstance());
        /** Acessa as configura��es do sistema para retornar o tamanho m�ximo para upload de documentos */
        $numTamMbDocExterno = $infraParametro->getValor('SEI_TAM_MB_DOC_EXTERNO');
      if (InfraString::isBolVazia($numTamMbDocExterno) || !is_numeric($numTamMbDocExterno)){
        $objInfraException->lancarValidacao('Valor do par�metro SEI_TAM_MB_DOC_EXTERNO inv�lido.');
      }

        /**'Acessa as configura��es do sistema para retornar se ser� realizada a valida��o de extens�es */
        $bolValidarExtensaoArq = $infraParametro->getValor('SEI_HABILITAR_VALIDACAO_EXTENSAO_ARQUIVOS');

        $arquivoExtensaoDTO = new ArquivoExtensaoDTO();
        $arquivoExtensaoDTO->retNumTamanhoMaximo();
        $arquivoExtensaoDTO->retStrExtensao();
        $arquivoExtensaoDTO->retStrDescricao();
        $arquivoExtensaoDTO->setOrdStrExtensao(InfraDTO::$TIPO_ORDENACAO_ASC);
        $arquivoExtensaoRN = new ArquivoExtensaoRN();
        /** Acessa o componente SEI para listar as extens�es e seus tamanhos m�ximos permitidos */
        $ret = $arquivoExtensaoRN->listar($arquivoExtensaoDTO);

        $arrExtensoes = array();
        /** @var ArquivoExtensaoDTO $arquivoExtensaoDTO */
      foreach($ret as $arquivoExtensaoDTO){
          $arrExtensoes[] = array(
              'extensao' => InfraString::transformarCaixaBaixa($arquivoExtensaoDTO->getStrExtensao()),
              'tamanho' => ($arquivoExtensaoDTO->getNumTamanhoMaximo() ?: $numTamMbDocExterno),
          );
      }

        $result = array(
            'extensoes' => $arrExtensoes,
            'tamanhoDocDefault' => $numTamMbDocExterno,
            'validarExtensoes' => ($bolValidarExtensaoArq == '1' ? true : false),
            'info' => 'Tamanhos em Mb'
        );
            
        return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
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