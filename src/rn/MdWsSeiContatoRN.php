<?php
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiContatoRN extends InfraRN
{

  protected function inicializarObjInfraIBanco()
    {
      return BancoSEI::getInstance();
  }

    
     /**
   * Pesquisa os contatos
   * @return array
   */
  protected function listarContatoConectado(ContatoDTO $contatoDTOParam)
    {
    try {
        $result = [];

        $objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
        $objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);

        $objTipoContatoRN = new TipoContatoRN();
        /** Chamada ao componente SEI para verificação de tipos de contato com acesso a unidade */
        $arrIdTipoContatoAcesso = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

      if (count($arrIdTipoContatoAcesso)) {
        $contatoDTOParam->retNumIdContato();
        $contatoDTOParam->retStrSigla();
        $contatoDTOParam->retStrNome();


        $contatoDTOParam->adicionarCriterio(['StaAcessoTipoContato', 'IdTipoContato'],
            [InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IN],
            [TipoContatoRN::$TA_NENHUM, $arrIdTipoContatoAcesso],
            InfraDTO::$OPER_LOGICO_OR);

        $contatoDTOParam->setStrSinAtivoTipoContato('S');
        $contatoDTOParam->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

        $contatoRN = new ContatoRN();
        /** Chama o componente SEI para retorno da busca de contatos */
        $ret = $contatoRN->pesquisarRN0471($contatoDTOParam);

        /** @var ContatoDTO $contatoDTO */
        foreach ($ret as $contatoDTO) {
          $result[] = [
          'nomeformatado' => ContatoINT::formatarNomeSiglaRI1224($contatoDTO->getStrNome(), $contatoDTO->getStrSigla()),
          'nome' => $contatoDTO->getStrNome(),
          'sigla' => $contatoDTO->getStrSigla(),
          'id' => $contatoDTO->getNumIdContato()
          ];
        }
      }

        return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $contatoDTOParam->getNumTotalRegistros());
    } catch (Exception $e) {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }
    
    
     /**
   * Realiza a inclusão de um contato no SEI.
   * @return array
   */
  protected function criarContatoConectado(MdWsSeiContatoDTO $objGetMdWsSeiContatoDTO)
    {
    try {
            
        $nome = $objGetMdWsSeiContatoDTO->getStrNome();
            
        $objContatoDTO = new ContatoDTO();
        $objContatoDTO->setStrNome($nome);

        $objContatoRN = new ContatoRN();
        $objContatoDTO = $objContatoRN->cadastrarContextoTemporario($objContatoDTO);
            
        return MdWsSeiRest::formataRetornoSucessoREST(null, ["id"=>$objContatoDTO->getNumIdContato()]);    
    } catch (Exception $e) {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }
    
    
    
    
}