<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiUnidadeRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna todas as unidades cadastradas
     */
    protected function listarUnidadesConectado(){
        try{
            $unidadeRN = new UnidadeRN();
            $unidadeDTO = new UnidadeDTO();
            $unidadeDTO->retNumIdUnidade();
            $unidadeDTO->retStrSigla();
            $unidadeDTO->retStrDescricao();
            $ret = $unidadeRN->listarRN0127($unidadeDTO);
            $result = array();
            /** @var UnidadeDTO $unDTO */
            foreach($ret as $unDTO){
                $result[] = array(
                    'id' => $unDTO->getNumIdUnidade(),
                    'sigla' => $unDTO->getStrSigla(),
                    'descricao' => $unDTO->getStrDescricao()
                );
            }
            return array(
                'sucesso' => true,
                'data' => $result
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