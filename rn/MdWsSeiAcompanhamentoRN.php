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
        }else{
            $acompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        }

        if (isset($post['grupo'])){
            $acompanhamentoDTO->setNumIdGrupoAcompanhamento($post['grupo']);
        }
        if (isset($post['usuario'])){
            $acompanhamentoDTO->setNumIdUsuarioGerador($post['usuario']);
        }else{
            $acompanhamentoDTO->setNumIdUsuarioGerador(SessaoSEI::getInstance()->getNumIdUsuario());
        }
        if (isset($post['observacao'])){
            $acompanhamentoDTO->setStrObservacao($post['observacao']);
        }
        $acompanhamentoDTO->setDthGeracao(InfraData::getStrDataHoraAtual());
        $acompanhamentoDTO->setNumTipoVisualizacao(AtividadeRN::$TV_VISUALIZADO);
        $acompanhamentoDTO->setNumIdAcompanhamento(null);

        return $acompanhamentoDTO;

    }

    protected function cadastrarAcompanhamentoControlado(AcompanhamentoDTO $acompanhamentoDTO){
        try{
            $acompanhamentoRN = new AcompanhamentoRN();
            $acompanhamentoRN->cadastrar($acompanhamentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Acompanhamento realizado com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
}