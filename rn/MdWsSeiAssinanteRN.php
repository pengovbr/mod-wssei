<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAssinanteRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna todas as funcoes/cargos cadastrados
     * @param AssinanteDTO $assinanteDTO
     * @return array
     */
    protected function listarAssinanteConectado(AssinanteDTO $assinanteDTOParam){
        try{
            $result = array();
            $assinanteDTOConsulta = new AssinanteDTO();
            if(!$assinanteDTOParam->isSetNumIdUnidade()){
                $assinanteDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            }else{
                $assinanteDTOConsulta->setNumIdUnidade($assinanteDTOParam->getNumIdUnidade());
            }
            if($assinanteDTOParam->getNumMaxRegistrosRetorno()){
                $assinanteDTOConsulta->setNumMaxRegistrosRetorno($assinanteDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $assinanteDTOConsulta->setNumMaxRegistrosRetorno(10);
            }
            if(!is_null($assinanteDTOParam->getNumPaginaAtual())){
                $assinanteDTOConsulta->setNumPaginaAtual($assinanteDTOParam->getNumPaginaAtual());
            }else{
                $assinanteDTOConsulta->setNumPaginaAtual(0);
            }
            $assinanteDTOConsulta->retNumIdAssinante();
            $assinanteDTOConsulta->retStrCargoFuncao();
            $assinanteDTOConsulta->setOrdStrCargoFuncao(InfraDTO::$TIPO_ORDENACAO_ASC);
            $assinanteRN = new AssinanteRN();
            $ret = $assinanteRN->pesquisar($assinanteDTOConsulta);
            /** @var AssinanteDTO $assinanteDTO */
            foreach($ret as $assinanteDTO){
                $result[] = array(
                    'id' => $assinanteDTO->getNumIdAssinante(),
                    'nome' => $assinanteDTO->getStrCargoFuncao(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $assinanteDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}