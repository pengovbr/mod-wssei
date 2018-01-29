<?php
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiHipoteseLegalRN extends InfraRN
{

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    
     /**
     * O serviço realiza a pesquisa das hipóteses legais do SEI.
     * @param MdWsSeiHipoteseLegalDTO $dto
     * @return array
     */
    protected function listarHipoteseLegalConectado(MdWsSeiHipoteseLegalDTO $dto)
    {
        try {
            $id             = $dto->getNumIdHipoteseLegal();
            $nivelAcesso    = $dto->getNumNivelAcesso();
            $filter         = $dto->getStrFilter();
            $start          = $dto->getNumStart();
            $limit          = $dto->getNumLimit();
            
            $hipoteseLegalDTO = new HipoteseLegalDTO();

            if ($id)
                $hipoteseLegalDTO->setNumIdHipoteseLegal($id);

            if ($nivelAcesso)
                $hipoteseLegalDTO->setStrStaNivelAcesso($nivelAcesso);

            if ($filter)
                $hipoteseLegalDTO->setStrNome('%' . utf8_decode ($filter) . '%', InfraDTO::$OPER_LIKE);

            IF ($limit)
                $hipoteseLegalDTO->setNumMaxRegistrosRetorno($limit);

            IF ($start)
                $hipoteseLegalDTO->setNumPaginaAtual($start);

            $hipoteseLegalDTO->retNumIdHipoteseLegal();
            $hipoteseLegalDTO->retStrNome();

            $hipoteseLegalRN = new HipoteseLegalRN();
            $arrHipoteseLegalDTO = $hipoteseLegalRN->listar($hipoteseLegalDTO);
            
            $arrayRetorno = array();
            if($arrHipoteseLegalDTO){
                foreach ($arrHipoteseLegalDTO as $obj) {
                    $arrayRetorno[] = array(
                        "id"    =>  $obj->getNumIdHipoteseLegal(),
                        "nome"  =>  $obj->getStrNome()
                    );
                }
            }
            
            $total = count($arrayRetorno);
            
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