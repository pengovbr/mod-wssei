<?
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiGrupoProtocoloModeloRN extends InfraRN {

  protected function inicializarObjInfraIBanco(){
      return BancoSEI::getInstance();
  }

    /**
     * Retorna os grupos de modelos de documentos
     * @param GrupoProtocoloModeloDTO $grupoProtocoloModeloDTOConsulta
     * @return array
     */
  protected function listarConectado(GrupoProtocoloModeloDTO $grupoProtocoloModeloDTOConsulta)
    {
    try{
        $result = array();
      if($grupoProtocoloModeloDTOConsulta->isSetStrNome()){
        $grupoProtocoloModeloDTOConsulta->setStrNome(
            '%'.$grupoProtocoloModeloDTOConsulta->getStrNome().'%',
            InfraDTO::$OPER_LIKE
        );
      }
        $grupoProtocoloModeloDTOConsulta->retNumIdGrupoProtocoloModelo();
        $grupoProtocoloModeloDTOConsulta->retStrNome();
        $grupoProtocoloModeloDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $grupoProtocoloModeloDTOConsulta->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
        $grupoProtocoloModeloRN = new GrupoProtocoloModeloRN();
        /** Acessa o componente SEI para consulta de grupos de modelos de documento **/
        $arrGrupoProtocoloModeloDTO = $grupoProtocoloModeloRN->listar($grupoProtocoloModeloDTOConsulta);

        /** Lógica de processamento para retorno de dados **/
      foreach($arrGrupoProtocoloModeloDTO as $grupoProtocoloModeloDTO) {
          $result[] = array(
              'idGrupoProtocoloModelo' => $grupoProtocoloModeloDTO->getNumIdGrupoProtocoloModelo(),
              'nome' => $grupoProtocoloModeloDTO->getStrNome(),
          );
      }


        return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $grupoProtocoloModeloDTOConsulta->getNumTotalRegistros());
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }

  }
}