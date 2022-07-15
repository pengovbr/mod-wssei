<?
require_once DIR_SEI_WEB . '/SEI.php';

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
            if($grupoAcompanhamentoDTOConsulta->isSetStrNome() && $grupoAcompanhamentoDTOConsulta->getStrNome()){
                $grupoAcompanhamentoDTOConsulta->setStrNome(
                    '%' . $grupoAcompanhamentoDTOConsulta->getStrNome() . '%',
                    InfraDTO::$OPER_LIKE
                );
            }
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

    /**
     * Método que realiza o cadastro do grupo de acompanhamento
     * @param GrupoAcompanhamentoDTO $grupoAcompanhamentoDTO
     * @return array
     */
    protected function cadastrarControlado(GrupoAcompanhamentoDTO $grupoAcompanhamentoDTO)
    {
        try{
            $grupoAcompanhamentoRN = new GrupoAcompanhamentoRN();
            /** Acessa o componente SEI para retorno da unidade da sessão do usuário */
            $grupoAcompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            /** Acessando o componente SEI para cadastro de grupo de acompanhamento **/
            $grupoAcompanhamentoRN->cadastrar($grupoAcompanhamentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST(
                'Grupo de Acompanhamento '
                .$grupoAcompanhamentoDTO->getNumIdGrupoAcompanhamento()
                .' cadastrado com sucesso.',
                array(
                    'id' => $grupoAcompanhamentoDTO->getNumIdGrupoAcompanhamento(),
                    'nome' => $grupoAcompanhamentoDTO->getStrNome()
                )
            );
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que realiza a edição do grupo de acompanhamento
     * @param GrupoAcompanhamentoDTO $grupoAcompanhamentoDTO
     * @return array
     */
    protected function alterarControlado(GrupoAcompanhamentoDTO $grupoAcompanhamentoDTOParam)
    {
        try{
            /** Validação de parametro recebido **/
            if(!$grupoAcompanhamentoDTOParam->isSetNumIdGrupoAcompanhamento() || !$grupoAcompanhamentoDTOParam->getNumIdGrupoAcompanhamento()){
                throw new Exception('Grupo de Acompanhamento não informado.');
            }
            $grupoAcompanhamentoRN = new GrupoAcompanhamentoRN();
            $grupoAcompanhamentoDTO = new GrupoAcompanhamentoDTO();
            $grupoAcompanhamentoDTO->retTodos();
            $grupoAcompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $grupoAcompanhamentoDTO->setNumIdGrupoAcompanhamento($grupoAcompanhamentoDTOParam->getNumIdGrupoAcompanhamento());
            /** Acessa o componente SEI para retorno dos dados do grupo de acompanhamento **/
            $grupoAcompanhamentoDTO = $grupoAcompanhamentoRN->consultar($grupoAcompanhamentoDTO);

            if(!$grupoAcompanhamentoDTO){
                throw new Exception('Grupo de Acompanhamento não encontrado.');
            }
            $grupoAcompanhamentoDTO->setStrNome($grupoAcompanhamentoDTOParam->getStrNome());
            /** Acessando o componente SEI para cadastro de grupo de acompanhamento **/
            $grupoAcompanhamentoRN->alterar($grupoAcompanhamentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST(
                'Grupo de Acompanhamento '
                .$grupoAcompanhamentoDTO->getNumIdGrupoAcompanhamento()
                .' alterado com sucesso.'
            );
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que realiza a exclusão de grupos de acompanhamento
     * @param array $arrIdGrupos
     * @return array
     */
    protected function excluirControlado(array $arrIdGrupos)
    {
        try{
            if(empty($arrIdGrupos)){
                throw new Exception('Grupo de Acompanhamento não informado.');
            }
            $grupoAcompanhamentoRN = new GrupoAcompanhamentoRN();
            $grupoAcompanhamentoDTO = new GrupoAcompanhamentoDTO();
            $grupoAcompanhamentoDTO->retTodos();
            $grupoAcompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $grupoAcompanhamentoDTO->setNumIdGrupoAcompanhamento($arrIdGrupos, InfraDTO::$OPER_IN);
            /** Acessa o componente SEI para retorno dos grupos de acompanhamento **/
            $arrGrupoAcompanhamentoDTOExclusao = $grupoAcompanhamentoRN->listar($grupoAcompanhamentoDTO);
            
            if(!$arrGrupoAcompanhamentoDTOExclusao){
                throw new Exception('Grupo de Acompanhamento não informado.');
            }

            /** Chama o componente SEI para exclusão de grupos de acompanhamento */
            $grupoAcompanhamentoRN->excluir($arrGrupoAcompanhamentoDTOExclusao);

            return MdWsSeiRest::formataRetornoSucessoREST('Grupo(s) de Acompanhamento excluído(s) com sucesso.', null);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
}