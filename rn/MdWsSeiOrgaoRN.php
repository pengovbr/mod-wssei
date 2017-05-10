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

            //Chamada Direta ao BD devido a ponta ser um serviço público sem autenticação.

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

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}