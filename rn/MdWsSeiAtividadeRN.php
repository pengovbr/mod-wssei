<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAtividadeRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna as atividades de um processo
     * @param AtividadeDTO $atividadeDTOParam
     * @return array
     * @throws InfraException
     */
    protected function listarAtividadesProcessoConectado(AtividadeDTO $atividadeDTOParam){
        try{
            $result = array();
            $atividadeDTOConsulta = new AtividadeDTO();
            if(!$atividadeDTOParam->isSetDblIdProtocolo()){
                throw new InfraException('O procedimento deve ser informado!');
            }
            $atividadeDTOConsulta->setDblIdProtocolo($atividadeDTOParam->isSetDblIdProtocolo());
            if(is_null($atividadeDTOParam->getNumPaginaAtual())){
                $atividadeDTOConsulta->setNumPaginaAtual(0);
            }else{
                $atividadeDTOConsulta->setNumPaginaAtual($atividadeDTOParam->getNumPaginaAtual());
            }
            if($atividadeDTOParam->getNumMaxRegistrosRetorno()){
                $atividadeDTOConsulta->setNumMaxRegistrosRetorno($atividadeDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $atividadeDTOConsulta->setNumMaxRegistrosRetorno(0);
            }
            $atividadeDTOConsulta->retDblIdProtocolo();
            $atividadeDTOConsulta->retDthAbertura();
            $atividadeDTOConsulta->retNumIdUsuarioOrigem();
            $atividadeDTOConsulta->retStrNomeTarefa();
            $atividadeDTOConsulta->retNumIdAtividade();
            $atividadeDTOConsulta->retStrSiglaUsuarioOrigem();
            $atividadeDTOConsulta->retStrSiglaUnidade();
            $atividadeDTOConsulta->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_DESC);
            $atividadeRN = new AtividadeRN();
            $ret = $atividadeRN->listarRN0036($atividadeDTOConsulta);
            /** @var AtividadeDTO $atividadeDTO */
            foreach($ret as $atividadeDTO) {
                $dateTime = explode(' ', $atividadeDTO->getDthAbertura());
                $informacao = null;
                $mdWsSeiProcessoDTO = new MdWsSeiProcessoDTO();
                $mdWsSeiProcessoDTO->setStrTemplate($atividadeDTO->getStrNomeTarefa());
                $mdWsSeiProcessoDTO->setNumIdAtividade($atividadeDTO->getNumIdAtividade());
                $mdWsSeiProcessoRN = new MdWsSeiProcessoRN();

                $result[] = [
                    "id" => $atividadeDTO->getNumIdAtividade(),
                    "atributos" => [
                        "idProcesso" => $atividadeDTO->getDblIdProtocolo(),
                        "usuario" => ($atividadeDTO->getNumIdUsuarioOrigem())? $atividadeDTO->getStrSiglaUsuarioOrigem() : null,
                        "data" => $dateTime[0],
                        "hora" => $dateTime[1],
                        "unidade" => $atividadeDTO->getStrSiglaUnidade(),
                        "informacao" => $mdWsSeiProcessoRN->traduzirTemplate($mdWsSeiProcessoDTO)
                    ]
                ];
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $atividadeDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }



}