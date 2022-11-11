<?php
require_once DIR_SEI_WEB . '/SEI.php';


class MdWsSeiTextoPadraoInternoRN extends TextoPadraoInternoRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Pesquisa os textos padr�o da unidade
     * @param TextoPadraoInternoDTO $textoPadraoInternoDTOParam
     * @return array
     */
    protected function pesquisarConectado(TextoPadraoInternoDTO $textoPadraoInternoDTOParam)
    {
        try{
            $idUnidade = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
            if ($textoPadraoInternoDTOParam->isSetNumIdUnidade()) {
                $idUnidade = $textoPadraoInternoDTOParam->getNumIdUnidade();
            }

            $textoPadraoInternoDTOParam->setNumIdUnidade($idUnidade);
            $result = array();
            $textoPadraoInternoDTOParam->retNumIdTextoPadraoInterno();
            $textoPadraoInternoDTOParam->retNumIdUnidade();
            $textoPadraoInternoDTOParam->retNumIdConjuntoEstilos();
            $textoPadraoInternoDTOParam->retStrNome();
            $textoPadraoInternoDTOParam->retStrDescricao();
            if($textoPadraoInternoDTOParam->isSetNumIdTextoPadraoInterno()){
                $textoPadraoInternoDTOParam->retStrConteudo();
            }
            $textoPadraoInternoDTOParam->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);


            if($textoPadraoInternoDTOParam->isSetStrNome()){
                $textoPadraoInternoDTOParam->setStrNome(
                    '%'.$textoPadraoInternoDTOParam->getStrNome().'%',
                    InfraDTO::$OPER_LIKE
                );
            }

            /** Acessa o componente SEI para retornar a lista de textos padr�o da unidade */
            $ret = $this->listar($textoPadraoInternoDTOParam);

            /** @var SerieDTO $serieDTO */
            foreach($ret as $serieDTO){
                $result[] = array(
                    'id' => $serieDTO->getNumIdTextoPadraoInterno(),
                    'nome' => $serieDTO->getStrNome(),
                    'descricao' => $serieDTO->getStrDescricao(),
                    'idUnidade' => $serieDTO->getNumIdUnidade(),
                    'idConjuntoEstilos' => $serieDTO->getNumIdConjuntoEstilos(),
                    /** Otimiza retorno para nao trafegar informa��es de forma desnecess�ria */
                    'conteudo' => (
                        $textoPadraoInternoDTOParam->isSetStrConteudo() ?
                            $textoPadraoInternoDTOParam->getStrConteudo() : ''
                    )
                );
            }
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $textoPadraoInternoDTOParam->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}