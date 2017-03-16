<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAcompanhamentoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    public function encapsulaAcompanhamento(array $post){
        $acompanhamentoDTO = new AcompanhamentoDTO();

        if (isset($post['protocolo'])){
            $acompanhamentoDTO->setDblIdProtocolo($post['protocolo']);
        }
        if (isset($post['unidade'])){
            $acompanhamentoDTO->setNumIdUnidade($post['unidade']);
        }

        if (isset($post['grupo'])){
            $acompanhamentoDTO->setNumIdGrupoAcompanhamento($post['grupo']);
        }
        if (isset($post['usuario'])){
            $acompanhamentoDTO->setNumIdUsuarioGerador($post['usuario']);
        }
            if (isset($post['observacao'])){
            $acompanhamentoDTO->setStrObservacao($post['observacao']);
        }

        return $acompanhamentoDTO;

    }

    protected function cadastrarAcompanhamentoControlado(AcompanhamentoDTO $acompanhamentoDTO){
        try{
            $acompanhamentoRN = new AcompanhamentoRN();
            $acompanhamentoDTO->setDthGeracao(InfraData::getStrDataHoraAtual());
            $pesquisaDTO = new AcompanhamentoDTO();
            $pesquisaDTO->setOrdNumIdAcompanhamento(InfraDTO::$TIPO_ORDENACAO_DESC);
            $pesquisaDTO->setNumMaxRegistrosRetorno(1);
            $pesquisaDTO->retNumIdAcompanhamento();
            $result = $acompanhamentoRN->listar($pesquisaDTO);
            $numIdAcompanhamento = 1;
            if(!empty($result)){
                $pesquisaDTO = end($result);
                $numIdAcompanhamento = $pesquisaDTO->getNumIdAcompanhamento()+1;
            }
            $acompanhamentoDTO->setNumIdAcompanhamento($numIdAcompanhamento);
            $acompanhamentoRN->cadastrar($acompanhamentoDTO);

            return MdWsSeiRest::formataRetornoSucessoREST('Acompanhamento realizado com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
}