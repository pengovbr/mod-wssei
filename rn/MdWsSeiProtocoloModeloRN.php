<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiProtocoloModeloRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna os modelos de documentos
     * @param ProtocoloModeloDTO $protocoloModeloDTOConsulta
     * @return array
     */
    protected function listarConectado(ProtocoloModeloDTO $protocoloModeloDTOConsulta)
    {
        try{
            $result = array();
            $protocoloModeloDTOConsulta->setOrdDthGeracao(InfraDTO::$TIPO_ORDENACAO_DESC);
            $protocoloModeloRN = new ProtocoloModeloRN();
            /** Acessa o componente SEI para consulta dos modelos de documento **/
            $arrProtocoloModeloDTO = $protocoloModeloRN->listarModelosUnidade($protocoloModeloDTOConsulta);

            /** Lógica de processamento para retorno de dados **/
            foreach($arrProtocoloModeloDTO as $protocoloModeloDTO) {
                $result[] = array(
                    'idProtocoloModelo' => $protocoloModeloDTO->getDblIdProtocoloModelo(),
                    'idGrupoProtocoloModelo' => $protocoloModeloDTO->getNumIdGrupoProtocoloModelo(),
                    'nomeGrupoProtocoloModelo' => $protocoloModeloDTO->getStrNomeGrupoProtocoloModelo(),
                    'nomeGrupoProtocoloModelo' => $protocoloModeloDTO->getStrNomeGrupoProtocoloModelo(),
                    'idUsuario' => $protocoloModeloDTO->getNumIdUsuario(),
                    'nomeUsuario' => $protocoloModeloDTO->getStrNomeUsuario(),
                    'siglaUsuario' => $protocoloModeloDTO->getStrSiglaUsuario(),
                    'protocoloFormatado' => $protocoloModeloDTO->getStrProtocoloFormatado(),
                    'nomeSerie' => $protocoloModeloDTO->getStrNomeSerie(),
                    'dataGeracao' => $protocoloModeloDTO->getDthGeracao()
                );
            }


            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $protocoloModeloDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }

    }
}