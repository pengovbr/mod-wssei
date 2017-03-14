<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiObservacaoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    public function encapsulaObservacao(array $post){
        $observacaoDTO = new ObservacaoDTO();
        $observacaoDTO->setStrIdxObservacao(null);
        if (isset($post['unidade'])) {
            $observacaoDTO->setNumIdUnidade($post['unidade']);
        }

        if (isset($post['descricao'])) {
            $observacaoDTO->setStrDescricao($post['descricao']);
        }

        if (isset($post['protocolo'])) {
            $observacaoDTO->setDblIdProtocolo($post['protocolo']);
        }

        return $observacaoDTO;
    }

    /**
     * Metodo que cria uma observacao
     * @param ObservacaoDTO $observacaoDTO
     * @info metodo auxiliar encapsulaObservacao para facilitar encapsulamento
     * @return array
     */
    protected function criarObservacaoControlado(ObservacaoDTO $observacaoDTO){
        try{
            $observacaoRN = new ObservacaoRN();
            $observacaoRN->cadastrarRN0222($observacaoDTO);

            return array(
                'sucesso' => true,
                'mensagem' => 'Observação cadastrada com sucesso!'
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