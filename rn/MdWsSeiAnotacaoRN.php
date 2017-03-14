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
            return array (
                "sucesso" => true,
                "mensagem" => 'Anotação cadastrada com sucesso!'
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