<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiGrupoAcompanhamentoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna todos os grupos de acompanhamento
     * @param OrgaoDTO $orgaoDTO
     * @info para paginacao e necessario informar dentro do DTO os parametros abaixo:
     *  - setNumMaxRegistrosRetorno
     *  - setNumPaginaAtual
     * @return array
     */
    protected function listarGrupoAcompanhamentoConectado(GrupoAcompanhamentoDTO $grupoAcompanhamentoDTO){
        try{
            $result = array();
            $grupoAcompanhamentoRN = new GrupoAcompanhamentoRN();
            if(!$grupoAcompanhamentoDTO->isRetNumIdGrupoAcompanhamento()){
                $grupoAcompanhamentoDTO->retNumIdGrupoAcompanhamento();
            }
            if(!$grupoAcompanhamentoDTO->isRetStrNome()){
                $grupoAcompanhamentoDTO->retStrNome();
            }
            if(!$grupoAcompanhamentoDTO->isSetNumIdUnidade()){
                $grupoAcompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            }
            $ret = $grupoAcompanhamentoRN->listar($grupoAcompanhamentoDTO);
            /** @var GrupoAcompanhamentoDTO $grupDTO */
            foreach($ret as $grupDTO){
                $result[] = array(
                    'id' => $grupDTO->getNumIdGrupoAcompanhamento(),
                    'nome' => $grupDTO->getStrNome()
                );
            }


            return array(
                'sucesso' => true,
                'data' => $result,
                'total' => $grupoAcompanhamentoDTO->getNumTotalRegistros()
            );
        }catch (Exception $e){
            return array(
                'sucesso' => false,
                'mensagem' => $e->getMessage(),
                'exception' => $e
            );
        }
    }

}