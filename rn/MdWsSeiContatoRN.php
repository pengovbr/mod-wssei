<?php
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiContatoRN extends InfraRN
{

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    
     /**
     * Pesquisa os contatos
     * @param ContatoDTO $contatoDTOParam
     * @return array
     */
    protected function listarContatoConectado(ContatoDTO $contatoDTOParam)
    {
        try {
            $result = array();

            $objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
            $objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);

            $objTipoContatoRN = new TipoContatoRN();
            /** Chamada ao componente SEI para verificação de tipos de contato com acesso a unidade */
            $arrIdTipoContatoAcesso = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

            if (count($arrIdTipoContatoAcesso)) {
                $contatoDTOParam->retNumIdContato();
                $contatoDTOParam->retStrSigla();
                $contatoDTOParam->retStrNome();


                $contatoDTOParam->adicionarCriterio(array('StaAcessoTipoContato', 'IdTipoContato'),
                    array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IN),
                    array(TipoContatoRN::$TA_NENHUM, $arrIdTipoContatoAcesso),
                    InfraDTO::$OPER_LOGICO_OR);

                $contatoDTOParam->setStrSinAtivoTipoContato('S');
                $contatoDTOParam->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

                $contatoRN = new ContatoRN();
                /** Chama o componente SEI para retorno da busca de contatos */
                $ret = $contatoRN->pesquisarRN0471($contatoDTOParam);

                /** @var ContatoDTO $contatoDTO */
                foreach ($ret as $contatoDTO) {
                    $result[] = array(
                        'nomeformatado' => ContatoINT::formatarNomeSiglaRI1224($contatoDTO->getStrNome(), $contatoDTO->getStrSigla()),
                        'nome' => $contatoDTO->getStrNome(),
                        'sigla' => $contatoDTO->getStrSigla(),
                        'id' => $contatoDTO->getNumIdContato()
                    );
                }
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $contatoDTOParam->getNumTotalRegistros());
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
    
    
     /**
     * Realiza a inclusão de um contato no SEI.
     * @param MdWsSeiContatoDTO $objGetMdWsSeiContatoDTO
     * @return array
     */
    protected function criarContatoConectado(MdWsSeiContatoDTO $objGetMdWsSeiContatoDTO)
    {
        try {
            
            $nome = $objGetMdWsSeiContatoDTO->getStrNome();
            
            $objContatoDTO = new ContatoDTO();
            $objContatoDTO->setStrNome($nome);

            $objContatoRN = new ContatoRN();
            $objContatoDTO = $objContatoRN->cadastrarContextoTemporario($objContatoDTO);
            
            return MdWsSeiRest::formataRetornoSucessoREST(null,array("id"=>$objContatoDTO->getNumIdContato()));    
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
    
    
    
    
}