<?
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiUnidadeRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Pesquisa as unidades pela sigla
     */
    protected function pesquisarUnidadeConectado(UnidadeDTO $unidadeDTOParam){
        try{
            $unidadeRN = new UnidadeRN();
            $unidadeDTO = new UnidadeDTO();
            if($unidadeDTOParam->getNumMaxRegistrosRetorno()){
                $unidadeDTO->setNumMaxRegistrosRetorno($unidadeDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $unidadeDTO->setNumMaxRegistrosRetorno(10);
            }
            if(empty($unidadeDTOParam->getNumPaginaAtual())){
                $unidadeDTO->setNumPaginaAtual(0);
            }else{
                $unidadeDTO->setNumPaginaAtual($unidadeDTOParam->getNumPaginaAtual());
            }
            if($unidadeDTOParam->isSetStrSigla()){
                $filter = '%'.$unidadeDTOParam->getStrSigla().'%';

                $unidadeDTO->adicionarCriterio(
                    array('Sigla', 'Descricao'),
                    array(InfraDTO::$OPER_LIKE, InfraDTO::$OPER_LIKE),
                    array($filter, $filter),
                    InfraDTO::$OPER_LOGICO_OR
                );
            }
            $unidadeDTO->setStrSinAtivo('S');
            $unidadeDTO->setStrSinEnvioProcesso('S');
            $unidadeDTO->retNumIdUnidade();
            $unidadeDTO->retStrSigla();
            $unidadeDTO->retStrDescricao();
            $unidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
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

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $unidadeDTO->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método para pesquisar outras unidades,
     * baseado no UnidadeINT::autoCompletarUnidades
     * @param UnidadeDTO $unidadeDTOParam
     * @return array
     */
    protected function pesquisarOutrasConectado(UnidadeDTO $unidadeDTOParam)
    {
        try{
            $unidadeDTOParam->retNumIdUnidade();
            $unidadeDTOParam->retStrSigla();
            $unidadeDTOParam->retStrDescricao();
            $unidadeDTOParam->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
            $unidadeRN = new UnidadeRN();
            /** Acessa o componente SEI para listagem de unidades */
            $arrObjUnidadeDTO = $unidadeRN->listarOutrasComFiltro($unidadeDTOParam);
            $result = array();
            /** @var UnidadeDTO $unidadeDTO */
            foreach($arrObjUnidadeDTO as $unidadeDTO)
            {
                $result[] = array(
                    'id' => $unidadeDTO->getNumIdUnidade(),
                    'descricao' => $unidadeDTO->getStrDescricao(),
                    'sigla' => $unidadeDTO->getStrSigla(),
                    /** Acessa o componente SEI para formatação do nome da unidade */
                    'nomeformatado' => UnidadeINT::formatarSiglaDescricao($unidadeDTO->getStrSigla(),$unidadeDTO->getStrDescricao()),
                );
            }
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $unidadeDTOParam->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}