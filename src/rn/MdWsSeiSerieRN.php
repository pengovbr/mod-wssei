<?php
require_once DIR_SEI_WEB . '/SEI.php';


class MdWsSeiSerieRN extends InfraRN {

  protected function inicializarObjInfraIBanco(){
      return BancoSEI::getInstance();
  }

    /**
     * Retorna todos os tipos de documento externo
     * @param SerieDTO $serieDTO
     * @return array
     */
  protected function pesquisarExternoConectado(SerieDTO $serieDTOParam)
    {
    try{
        $result = [];
        $serieDTOParam->retNumIdSerie();
        $serieDTOParam->retStrNome();
        $serieDTOParam->setStrStaAplicabilidade([SerieRN::$TA_INTERNO_EXTERNO, SerieRN::$TA_EXTERNO], InfraDTO::$OPER_IN);
        $serieDTOParam->setStrSinInterno('N');
        $serieDTOParam->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

      if($serieDTOParam->isSetStrNome()){
        $serieDTOParam->setStrNome(
            '%'.$serieDTOParam->getStrNome().'%',
            InfraDTO::$OPER_LIKE
        );
      }

        $serieRN = new SerieRN();
        /** Chamada ao componente SEI para retorno da lista de Series do Documento */
        $ret = $serieRN->listarRN0646($serieDTOParam);

        /** @var SerieDTO $serieDTO */
      foreach($ret as $serieDTO){
          $result[] = [
              'id' => $serieDTO->getNumIdSerie(),
              'nome' => $serieDTO->getStrNome(),
          ];
      }
            
        return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $serieDTOParam->getNumTotalRegistros());
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

}