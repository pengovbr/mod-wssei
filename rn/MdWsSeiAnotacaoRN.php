<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAnotacaoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    public function encapsulaAnotacao(array $post){
        $anotacaoDTO = new AnotacaoDTO();
        if (isset($post['descricao'])) {
            $anotacaoDTO->setStrDescricao($post['descricao']);
        }


        if (isset($post['protocolo'])) {
            $anotacaoDTO->setDblIdProtocolo($post['protocolo']);
        }

        if (isset($post['unidade'])) {
            $anotacaoDTO->setNumIdUnidade($post['unidade']);
        }

        if (isset($post['usuario'])) {
            $anotacaoDTO->setNumIdUsuario($post['usuario']);
        }

        $anotacaoDTO->setDthAnotacao(InfraData::getStrDataHoraAtual());

        if (isset($post['prioridade'])) {
            $anotacaoDTO->setStrSinPrioridade(
                ($post['prioridade']) ? 'S' : 'N'
            );
        }
        $anotacaoDTO->setStrStaAnotacao('U');

        return $anotacaoDTO;
    }

    protected function cadastrarAnotacaoControlado(AnotacaoDTO $anotacaoDTO){
        try{
            $anotacaoRN = new AnotacaoRN();
            if(!$anotacaoDTO->getDblIdProtocolo()){
                throw new InfraException('Protocolo n�o informado.');
            }
            $anotacaoConsulta = new AnotacaoDTO();
            $anotacaoConsulta->setDblIdProtocolo($anotacaoDTO->getDblIdProtocolo());
            $anotacaoConsulta->setNumMaxRegistrosRetorno(1);
            $anotacaoConsulta->retNumIdAnotacao();
            $ret = $anotacaoRN->listar($anotacaoConsulta);
            if($ret){
                $anotacaoDTO->setNumIdAnotacao($ret[0]->getNumIdAnotacao());
                $anotacaoRN->alterar($anotacaoDTO);
            }else{
                $anotacaoRN->cadastrar($anotacaoDTO);
            }
            return MdWsSeiRest::formataRetornoSucessoREST('Anotação cadastrada com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}