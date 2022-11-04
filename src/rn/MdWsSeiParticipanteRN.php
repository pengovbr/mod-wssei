<?php
require_once DIR_SEI_WEB . '/SEI.php';


class MdWsSeiParticipanteRN extends ParticipanteRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * @return array
     */
    protected function processoInteressadosListarConectado(ParticipanteDTO $participanteDTOParam)
    {
        try{
            if(!$participanteDTOParam->getDblIdProtocolo()){
                throw new Exception('Protocolo não informado.');
            }
            $result = array();
            $participanteDTOParam->retNumIdContato();
            $participanteDTOParam->retStrNomeContato();
            $participanteDTOParam->retStrSiglaContato();
            $participanteDTOParam->setStrStaParticipacao(array(ParticipanteRN::$TP_INTERESSADO),InfraDTO::$OPER_IN);
            $participanteDTOParam->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

            /** Chama o componente SEI para retornar a lista de interessados */
            $ret = $this->listarRN0189($participanteDTOParam);
            /** @var ParticipanteDTO $participanteDTO */
            foreach($ret as $participanteDTO){
                $result[] = array(
                    'id' => $participanteDTO->getNumIdContato(),
                    'nome' => $participanteDTO->getStrNomeContato(),
                    'sigla' => $participanteDTO->getStrSiglaContato(),
                    /** Chama o componente SEI para formatação do nome do interessado */
                    'nomeformatado' => ContatoINT::formatarNomeSiglaRI1224($participanteDTO->getStrNomeContato(),$participanteDTO->getStrSiglaContato()),
                );
            }
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $participanteDTOParam->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}