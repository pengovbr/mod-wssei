<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAcompanhamentoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    public function encapsulaAcompanhamento(array $post){
        $acompanhamentoDTO = new AcompanhamentoDTO();

        if (!empty($post['protocolo'])){
            $acompanhamentoDTO->setDblIdProtocolo($post['protocolo']);
        }

        $acompanhamentoDTO->setNumIdGrupoAcompanhamento($post['grupo']);
        $acompanhamentoDTO->setStrObservacao($post['observacao']);

        $acompanhamentoDTO->setNumIdUsuarioGerador(SessaoSEI::getInstance()->getNumIdUsuario());
        $acompanhamentoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $acompanhamentoDTO->setDthGeracao(InfraData::getStrDataHoraAtual());
        $acompanhamentoDTO->setNumTipoVisualizacao(AtividadeRN::$TV_VISUALIZADO);
        $acompanhamentoDTO->setNumIdAcompanhamento(null);

        return $acompanhamentoDTO;

    }

    protected function cadastrarAcompanhamentoControlado(AcompanhamentoDTO $acompanhamentoDTO){
        try{
            if($acompanhamentoDTO->isSetDblIdProtocolo() && $acompanhamentoDTO->isSetNumIdUnidade()){
                $protocoloRN = new ProtocoloRN();
                $protocoloDTO = new ProtocoloDTO();
                
                $protocoloDTO->setDblIdProtocolo($acompanhamentoDTO->getDblIdProtocolo());
                $protocoloDTO->retNumIdUnidadeGeradora();
                /** Consulta o componente SEI para retorno dos dados do protocolo para validação **/
                $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
                if(!$protocoloDTO || $protocoloDTO->getNumIdUnidadeGeradora() != $acompanhamentoDTO->getNumIdUnidade()){
                    throw new Exception('Protocolo não encontrado.');
                }
            }
            $acompanhamentoRN = new AcompanhamentoRN();
            $acompanhamentoRN->cadastrar($acompanhamentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Acompanhamento realizado com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
}