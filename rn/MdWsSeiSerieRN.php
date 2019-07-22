<?php
require_once dirname(__FILE__).'/../../../SEI.php';


class MdWsSeiSerieRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna todos os tipos de documento externo
     * @param SerieDTO $serieDTO
     * @return array
     */
    protected function listarExternoConectado(SerieDTO $serieDTOParam)
    {
        try{
            $result = array();
            $serieDTOParam->retNumIdSerie();
            $serieDTOParam->retStrNome();
            $serieDTOParam->setStrStaAplicabilidade(array(SerieRN::$TA_INTERNO_EXTERNO, SerieRN::$TA_EXTERNO),InfraDTO::$OPER_IN);
            $serieDTOParam->setStrSinInterno('N');
            $serieDTOParam->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

            $serieRN = new SerieRN();
            $ret = $serieRN->listarRN0646($serieDTOParam);

            /** @var SerieDTO $serieDTO */
            foreach($ret as $serieDTO){
                $result[] = array(
                    'id' => $serieDTO->getNumIdSerie(),
                    'sigla' => $serieDTO->getStrNome(),
                );
            }
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $serieDTOParam->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}