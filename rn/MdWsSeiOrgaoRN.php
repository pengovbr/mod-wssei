<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiOrgaoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna todos os orgaos ativos cadastrados
     * @param OrgaoDTO $orgaoDTO
     * @info para p�ginacao e necess�rio informar dentro do DTO os parametros abaixo:
     *  - setNumMaxRegistrosRetorno
     *  - setNumPaginaAtual
     * @return array
     */
    protected function listarOrgaoConectado(OrgaoDTO $orgaoDTO){
        try{
            $result = array();
            $orgaoRN = new OrgaoRN();
            if(!$orgaoDTO->isRetNumIdOrgao()){
                $orgaoDTO->retNumIdOrgao();
            }
            if(!$orgaoDTO->isRetStrSigla()){
                $orgaoDTO->retStrSigla();
            }
            if(!$orgaoDTO->isRetStrDescricao()){
                $orgaoDTO->retStrDescricao();
            }
            if(!$orgaoDTO->isSetStrSinAtivo()){
                $orgaoDTO->setStrSinAtivo('S');
            }
            $ret = $orgaoRN->listarRN1353($orgaoDTO);
            /** @var OrgaoDTO $orgDTO */
            foreach($ret as $orgDTO){
                $result[] = array(
                    'id' => $orgDTO->getNumIdOrgao(),
                    'sigla' => $orgDTO->getStrSigla(),
                    'descricao' => $orgDTO->getStrDescricao()
                );
            }


            return array(
                'sucesso' => true,
                'data' => $result,
                'total' => $orgaoDTO->getNumTotalRegistros()
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