<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAtividadeRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna as atividades de um processo
     * @param AtividadeDTO $atividadeDTOParam
     * @return array
     * @throws InfraException
     */
    protected function listarAtividadesProcessoConectado(AtividadeDTO $atividadeDTOParam){
        try{
            $result = array();
            $atividadeDTOConsulta = new AtividadeDTO();
            if(!$atividadeDTOParam->isSetDblIdProtocolo()){
                throw new InfraException('O procedimento deve ser informado!');
            }
            $atividadeDTOConsulta->setDblIdProtocolo($atividadeDTOParam->getDblIdProtocolo());
            if(is_null($atividadeDTOParam->getNumPaginaAtual())){
                $atividadeDTOConsulta->setNumPaginaAtual(0);
            }else{
                $atividadeDTOConsulta->setNumPaginaAtual($atividadeDTOParam->getNumPaginaAtual());
            }
            if($atividadeDTOParam->getNumMaxRegistrosRetorno()){
                $atividadeDTOConsulta->setNumMaxRegistrosRetorno($atividadeDTOParam->getNumMaxRegistrosRetorno());
            }else{
                $atividadeDTOConsulta->setNumMaxRegistrosRetorno(10);
            }
            $atividadeDTOConsulta->retDblIdProtocolo();
            $atividadeDTOConsulta->retDthAbertura();
            $atividadeDTOConsulta->retNumIdUsuarioOrigem();
            $atividadeDTOConsulta->retStrNomeTarefa();
            $atividadeDTOConsulta->retNumIdAtividade();
            $atividadeDTOConsulta->retStrSiglaUsuarioOrigem();
            $atividadeDTOConsulta->retStrSiglaUnidade();
            $atividadeDTOConsulta->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_DESC);
            $atividadeRN = new AtividadeRN();
            $ret = $atividadeRN->listarRN0036($atividadeDTOConsulta);
            /** @var AtividadeDTO $atividadeDTO */
            foreach($ret as $atividadeDTO) {
                $dateTime = explode(' ', $atividadeDTO->getDthAbertura());
                $informacao = null;
                $mdWsSeiProcessoDTO = new MdWsSeiProcessoDTO();
                $mdWsSeiProcessoDTO->setStrTemplate($atividadeDTO->getStrNomeTarefa());
                $mdWsSeiProcessoDTO->setNumIdAtividade($atividadeDTO->getNumIdAtividade());
                $mdWsSeiProcessoRN = new MdWsSeiProcessoRN();

                $result[] = [
                    "id" => $atividadeDTO->getNumIdAtividade(),
                    "atributos" => [
                        "idProcesso" => $atividadeDTO->getDblIdProtocolo(),
                        "usuario" => ($atividadeDTO->getNumIdUsuarioOrigem())? $atividadeDTO->getStrSiglaUsuarioOrigem() : null,
                        "data" => $dateTime[0],
                        "hora" => $dateTime[1],
                        "unidade" => $atividadeDTO->getStrSiglaUnidade(),
                        "informacao" => $mdWsSeiProcessoRN->traduzirTemplate($mdWsSeiProcessoDTO)
                    ]
                ];
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $atividadeDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que encapsula os dados para o cadastramento do andamento do processo
     * @param array $post
     * @return AtualizarAndamentoDTO
     */
    public function encapsulaLancarAndamentoProcesso(array $data){
        $entradaLancarAndamentoAPI = new EntradaLancarAndamentoAPI();
        $entradaLancarAndamentoAPI->setIdTarefa(TarefaRN::$TI_ATUALIZACAO_ANDAMENTO);
        if($data['protocolo']){
            $entradaLancarAndamentoAPI->setIdProcedimento($data['protocolo']);
        }

        if($data['descricao']){
            $atributoAndamentoAPI = new AtributoAndamentoAPI();
            $atributoAndamentoAPI->setNome('DESCRICAO');
            $atributoAndamentoAPI->setValor($data['descricao']);
            $atributoAndamentoAPI->setIdOrigem(null);
            $entradaLancarAndamentoAPI->setAtributos(array($atributoAndamentoAPI));
        }

        return $entradaLancarAndamentoAPI;
    }

    /**
     * Método que cadastra o andamento manual de um processo
     * @param EntradaLancarAndamentoAPI $entradaLancarAndamentoAPIParam
     * @info usar o método auxiliar encapsulaLancarAndamentoProcesso para faciliar
     * @return array
     */
    protected function lancarAndamentoProcessoControlado(EntradaLancarAndamentoAPI $entradaLancarAndamentoAPIParam){
        try{
            $seiRN = new SeiRN();
            $seiRN->lancarAndamento($entradaLancarAndamentoAPIParam);

            return MdWsSeiRest::formataRetornoSucessoREST('Observação cadastrada com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }



}