<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiContextoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna a lista de contextos por orgao
     * @return array
     */
    protected function listarContextoConectado(OrgaoDTO $orgaoDTO){
        try{
            if(!$orgaoDTO->isSetNumIdOrgao()){
                throw new Exception('O órgão do contexto deve ser informado!');
            }
            $result = array();
            $contextoRN = new ContextoRN();
            $contextDTOConsulta = new ContextoDTO();
            $contextDTOConsulta->retNumIdContexto();
            $contextDTOConsulta->retStrNome();
            $contextDTOConsulta->retStrDescricao();
            $contextDTOConsulta->retStrBaseDnLdap();
            $contextDTOConsulta->setStrSinAtivo('S');
            $contextDTOConsulta->setNumIdOrgao($orgaoDTO->getNumIdOrgao());
            $ret = $contextoRN->listar($contextDTOConsulta);
            /** @var ContextoDTO $contextoDTO */
            foreach($ret as $contextoDTO){
                $result[] = array(
                    'id' => $contextoDTO->getNumIdContexto(),
                    'nome' => $contextoDTO->getStrNome(),
                    'descricao' => $contextoDTO->getStrDescricao(),
                    'base_dn_ldap' => $contextoDTO->getStrBaseDnLdap(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}