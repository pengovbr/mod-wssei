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
    protected function pesquisarExternoConectado(SerieDTO $serieDTOParam)
    {
        try{
            $result = array();
            $serieDTOParam->retNumIdSerie();
            $serieDTOParam->retStrNome();
            $serieDTOParam->setStrStaAplicabilidade(array(SerieRN::$TA_INTERNO_EXTERNO, SerieRN::$TA_EXTERNO),InfraDTO::$OPER_IN);
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
                $result[] = array(
                    'id' => $serieDTO->getNumIdSerie(),
                    'nome' => $serieDTO->getStrNome(),
                );
            }
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $serieDTOParam->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}