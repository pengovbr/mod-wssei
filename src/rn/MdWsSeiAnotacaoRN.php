<?
require_once DIR_SEI_WEB . '/SEI.php';

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
        $anotacaoDTO->setDblIdProtocolo(array($post['protocolo']));
    }

    if (isset($post['unidade'])) {
        $anotacaoDTO->setNumIdUnidade($post['unidade']);
    }

    if (isset($post['usuario'])) {
        $anotacaoDTO->setNumIdUsuario($post['usuario']);
    }

      $anotacaoDTO->setDthAnotacao(InfraData::getStrDataHoraAtual());

    if (isset($post['prioridade']) && in_array($post['prioridade'], array('S', 'N'))) {
        $anotacaoDTO->setStrSinPrioridade($post['prioridade']);
    }else{
        $anotacaoDTO->setStrSinPrioridade('N');
    }
      $anotacaoDTO->setStrStaAnotacao('U');

      return $anotacaoDTO;
  }

  protected function cadastrarAnotacaoControlado(AnotacaoDTO $anotacaoDTO){
    try{
        $anotacaoRN = new AnotacaoRN();
      if(!$anotacaoDTO->getDblIdProtocolo()){
        throw new InfraException('Protocolo não informado.');
      }
        $anotacaoRN->registrar($anotacaoDTO);

        return MdWsSeiRest::formataRetornoSucessoREST('Anotação cadastrada com sucesso!');
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

}
