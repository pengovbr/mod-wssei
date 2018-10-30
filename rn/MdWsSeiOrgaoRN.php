<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiOrgaoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna todos os orgaos ativos cadastrados
     * @param OrgaoDTO $orgaoDTO
     * @return array
     */
    protected function listarOrgaoConectado(OrgaoDTO $orgaoDTOParam){
        try{
            $result = array();
            $orgaoDTO = new OrgaoDTO();
            $orgaoDTO->retNumIdOrgao();
            $orgaoDTO->retStrSigla();
            $orgaoDTO->retStrDescricao();
            $orgaoDTO->setStrSinAtivo('S');
            $orgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

            if($orgaoDTOParam->getNumMaxRegistrosRetorno()){
                $orgaoDTO->setNumMaxRegistrosRetorno($orgaoDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $orgaoDTO->setNumMaxRegistrosRetorno(10);
            }
            if(!is_null($orgaoDTOParam->getNumPaginaAtual())){
                $orgaoDTO->setNumPaginaAtual($orgaoDTOParam->getNumPaginaAtual());
            }else{
                $orgaoDTO->setNumPaginaAtual(0);
            }

            $orgaoBD = new OrgaoBD($this->getObjInfraIBanco());
            $ret = $orgaoBD->listar($orgaoDTO);

            /** @var OrgaoDTO $orgDTO */
            foreach($ret as $orgDTO){
                $result[] = array(
                    'id' => $orgDTO->getNumIdOrgao(),
                    'sigla' => $orgDTO->getStrSigla(),
                    'descricao' => $orgDTO->getStrDescricao()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $orgaoDTO->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}