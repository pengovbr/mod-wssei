<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiGrupoAcompanhamentoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna todos os grupos de acompanhamento
     * @param GrupoAcompanhamentoDTO $grupoAcompanhamentoDTOParam
     * @return array
     */
    protected function listarGrupoAcompanhamentoConectado(GrupoAcompanhamentoDTO $grupoAcompanhamentoDTOParam){
        try{
            $result = array();
            $grupoAcompanhamentoDTOConsulta = new GrupoAcompanhamentoDTO();
            $grupoAcompanhamentoRN = new GrupoAcompanhamentoRN();
            $grupoAcompanhamentoDTOConsulta->retNumIdGrupoAcompanhamento();
            $grupoAcompanhamentoDTOConsulta->retStrNome();
            $grupoAcompanhamentoDTOConsulta->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
            if(!$grupoAcompanhamentoDTOParam->isSetNumIdUnidade()){
                $grupoAcompanhamentoDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            }else{
                $grupoAcompanhamentoDTOConsulta->setNumIdUnidade($grupoAcompanhamentoDTOParam->getNumIdUnidade());
            }
            if($grupoAcompanhamentoDTOParam->getNumMaxRegistrosRetorno()){
                $grupoAcompanhamentoDTOConsulta->setNumMaxRegistrosRetorno($grupoAcompanhamentoDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $grupoAcompanhamentoDTOConsulta->setNumMaxRegistrosRetorno(10);
            }
            if(!is_null($grupoAcompanhamentoDTOParam->getNumPaginaAtual())){
                $grupoAcompanhamentoDTOConsulta->setNumPaginaAtual($grupoAcompanhamentoDTOParam->getNumPaginaAtual());
            }else{
                $grupoAcompanhamentoDTOConsulta->setNumPaginaAtual(0);
            }
            $ret = $grupoAcompanhamentoRN->listar($grupoAcompanhamentoDTOConsulta);
            /** @var GrupoAcompanhamentoDTO $grupoAcompanhamentoDTO */
            foreach($ret as $grupoAcompanhamentoDTO){
                $result[] = array(
                    'id' => $grupoAcompanhamentoDTO->getNumIdGrupoAcompanhamento(),
                    'nome' => $grupoAcompanhamentoDTO->getStrNome()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $grupoAcompanhamentoDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}