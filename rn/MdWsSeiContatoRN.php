<?php
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiContatoRN extends InfraRN
{

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    
     /**
     * Retorna todos tipos de procedimentos filtrados
     * @param MdWsSeiTipoProcedimentoDTO $objGetMdWsSeiTipoProcedimentoDTO
     * @return array
     */
    protected function listarContatoConectado(MdWsSeiContatoDTO $objGetMdWsSeiContatoDTO)
    {
        try {
            
            $id         = $objGetMdWsSeiContatoDTO->getNumIdContato();
            $filter     = $objGetMdWsSeiContatoDTO->getStrFilter();
            $start      = $objGetMdWsSeiContatoDTO->getNumStart();
            $limit      = $objGetMdWsSeiContatoDTO->getNumLimit();
            
            $contatoDTO = new ContatoDTO();

            if($id)
                $contatoDTO->setNumIdContato($id);

            if($filter)
                $contatoDTO->setStrNome('%'.utf8_decode($filter).'%',InfraDTO::$OPER_LIKE);

            $contatoCountDTO = new ContatoDTO();
            $contatoCountDTO->retNumIdContato();

            IF($limit)
                $contatoDTO->setNumMaxRegistrosRetorno($limit);

            IF($start)
                $contatoDTO->setNumPaginaAtual($start); 

            $contatoDTO->retNumIdContato();
            $contatoDTO->retStrSigla();
            $contatoDTO->retStrNome();

            $contatoRN = new ContatoRN();
            $arrContatoDTO = $contatoRN->listarRN0325($contatoDTO);

            $contatoCountDTO = $contatoRN->listarRN0325($contatoCountDTO);
            
            $arrayRetorno = array();
            if($arrContatoDTO){
                foreach ($arrContatoDTO as $obj) {
                    $arrayRetorno[] = array(
                                        "id"        => $obj->getNumIdContato(),
                                        "sigla"     => $obj->getStrSigla(),
                                        "nome"      => $obj->getStrNome()
                                    );
                }
            }
            
            $total = 0;
            $total = count($contatoCountDTO);
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno, $total);    
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