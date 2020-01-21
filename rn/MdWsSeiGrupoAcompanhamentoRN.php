<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiGrupoAcompanhamentoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna os grupos de acompanhamento
     * @param GrupoAcompanhamentoDTO $grupoAcompanhamentoDTO
     * @return array
     */
    protected function listarConectado(GrupoAcompanhamentoDTO $grupoAcompanhamentoDTOConsulta)
    {
        try{
            $result = array();
            $grupoAcompanhamentoDTOConsulta->retNumIdGrupoAcompanhamento();
            $grupoAcompanhamentoDTOConsulta->retStrNome();
            $grupoAcompanhamentoDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $grupoAcompanhamentoDTOConsulta->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
            $grupoAcompanhamentoRN = new GrupoAcompanhamentoRN();
            /** Acessa o componente SEI para consulta de grupos de acompanhamento **/
            $arrGrupoAcompanhamentoDTO = $grupoAcompanhamentoRN->listar($grupoAcompanhamentoDTOConsulta);

            /** Lógica de processamento para retorno de dados **/
            foreach($arrGrupoAcompanhamentoDTO as $grupoAcompanhamentoDTO) {
                $result[] = array(
                    'idGrupoAcompanhamento' => $grupoAcompanhamentoDTO->getNumIdGrupoAcompanhamento(),
                    'nome' => $grupoAcompanhamentoDTO->getStrNome(),
                );
            }


            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $grupoAcompanhamentoDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }

    }
}