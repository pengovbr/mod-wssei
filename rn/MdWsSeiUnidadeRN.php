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

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}