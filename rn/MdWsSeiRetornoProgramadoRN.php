<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiRetornoProgramadoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    public function encapsulaRetornoProgramado(array $post){
        $retornoProgramadoDTO = new RetornoProgramadoDTO();
        $retornoProgramadoDTO->setNumIdRetornoProgramado(null);
        $retornoProgramadoDTO->setDthAlteracao(null);
        if (isset($post['usuario'])) {
            $retornoProgramadoDTO->setNumIdUsuario($post['usuario']);
        }else{
            $retornoProgramadoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        }

        if (isset($post['atividadeEnvio'])) {
            $retornoProgramadoDTO->setNumIdAtividadeEnvio($post['atividadeEnvio']);
        }

        if (isset($post['unidade'])) {
            $retornoProgramadoDTO->setNumIdUnidade($post['unidade']);
        }

        if (isset($post['dtProgramada'])) {
            $retornoProgramadoDTO->setDtaProgramada($post['dtProgramada']);
        }

        return $retornoProgramadoDTO;
    }

    /**
     * Metodo que agenda um retorno programado
     * @param RetornoProgramadoDTO $retornoProgramadoDTO
     * @info metodo auxiliar encapsulaRetornoProgramado para facilitar encapsulamento
     * @return array
     */
    protected function agendarRetornoProgramadoControlado(RetornoProgramadoDTO $retornoProgramadoDTO){
        try{
            $retornoProgramadoRN = new RetornoProgramadoRN();
            $retornoProgramadoRN->cadastrar($retornoProgramadoDTO);

            return MdWsSeiRest::formataRetornoSucessoREST('Retorno Programado agendado com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
}