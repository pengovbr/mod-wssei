<?php
require_once DIR_SEI_WEB . '/SEI.php';

//phpcs:ignore
class MdWsSeiContato_V1_RN extends InfraRN
{

  protected function inicializarObjInfraIBanco()
    {
      return BancoSEI::getInstance();
  }


    /**
     * Retorna todos tipos de procedimentos filtrados
     * @param MdWsSeiTipoProcedimentoDTO $objGetMdWsSeiTipoProcedimentoDTO
     * @return array
     */
  protected function listarContatoConectado(MdWsSeiContatoDTO $objGetMdWsSeiContatoDTO)
    {
    try {

        $id         = $objGetMdWsSeiContatoDTO->getNumIdContato();
        $filter     = $objGetMdWsSeiContatoDTO->getStrFilter();
        $start      = $objGetMdWsSeiContatoDTO->getNumStart();
        $limit      = $objGetMdWsSeiContatoDTO->getNumLimit();
        $bool       = false;

        $contatoDTO = new ContatoDTO();

      if($id) {
          $contatoDTO->setNumIdContato($id);
      }

      if($filter) {
        $contatoDTO->adicionarCriterio(['Nome', 'Nome'],
            [InfraDTO::$OPER_LIKE, InfraDTO::$OPER_LIKE],
            ['%' . mb_convert_encoding(str_replace("-", " ", $filter), 'ISO-8859-1') . '%', '%' . mb_convert_encoding(str_replace(" ", "-", $filter), 'ISO-8859-1') . '%'],
            [InfraDTO::$OPER_LOGICO_OR]);
      }


        $contatoCountDTO = new ContatoDTO();
        $contatoCountDTO->retNumIdContato();

      if($limit) {
          $contatoDTO->setNumMaxRegistrosRetorno($limit);
          $bool = true;
      }

      if($start) {
          $contatoDTO->setNumPaginaAtual($start);
          $bool = true;
      }

        $contatoDTO->retNumIdContato();
        $contatoDTO->retStrSigla();
        $contatoDTO->retStrNome();

        $contatoRN = new ContatoRN();
        $arrContatoDTO = $contatoRN->listarRN0325($contatoDTO);

        $contatoCountDTO = $contatoRN->listarRN0325($contatoCountDTO);

        $arrayRetorno = [];
      if($arrContatoDTO){
        foreach ($arrContatoDTO as $obj) {
            $arrayRetorno[] = [
                "id"        => $obj->getNumIdContato(),
                "sigla"     => $obj->getStrSigla(),
                "nome"      => $obj->getStrNome()
            ];
        }
      }
          $total = count($arrayRetorno);

      if($bool){
          $total = count($contatoCountDTO);
      }


          return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno, $total);
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