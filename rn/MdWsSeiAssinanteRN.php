<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAssinanteRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna todas as funcoes/cargos cadastrados
     * @param AssinanteDTO $assinanteDTO
     * @info Para retornar a consulta paginada e necessario passar dentro do DTO os parametros:
     *      - setNumMaxRegistrosRetorno - maximo de registros por pagina (limit)
     *      - setNumPaginaAtual - pagina atual (offset)
     * @return array
     */
    protected function listarAssinanteConectado(AssinanteDTO $assinanteDTO){
        try{
            $result = array();
            $assinanteDTO->retNumIdAssinante();
            $assinanteDTO->retStrCargoFuncao();
            $assinanteDTO->setOrdStrCargoFuncao(InfraDTO::$TIPO_ORDENACAO_ASC);
            $assinanteRN = new AssinanteRN();
            $ret = $assinanteRN->pesquisar($assinanteDTO);
            /** @var AssinanteDTO $assinDTO */
            foreach($ret as $assinDTO){
                $result[] = array(
                    'id' => $assinDTO->getNumIdAssinante(),
                    'nome' => $assinDTO->getStrCargoFuncao(),
                );
            }

            return array(
                'sucesso' => true,
                'data' => $result,
                'total' => $assinanteDTO->getNumTotalRegistros()
            );
        }catch (Exception $e){
            $mensagem = $e->getMessage();
            if($e instanceof InfraException){
                if(!$e->getStrDescricao()){
                    /** @var InfraValidacaoDTO $validacaoDTO */
                    if(count($e->getArrObjInfraValidacao()) == 1){
                        $mensagem = $e->getArrObjInfraValidacao()[0]->getStrDescricao();
                    }else{
                        foreach($e->getArrObjInfraValidacao() as $validacaoDTO){
                            $mensagem[] = $validacaoDTO->getStrDescricao();
                        }
                    }
                }else{
                    $mensagem = $e->getStrDescricao();
                }

            }
            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }
    }

}