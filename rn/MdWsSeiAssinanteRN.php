<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAssinanteRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna todas as funcoes/cargos cadastrados
     * @param AssinanteDTO $assinanteDTO
     * @return array
     */
    protected function listarAssinanteConectado(AssinanteDTO $assinanteDTOConsulta){
        try{
            $result = array();

            $usuarioRN = new UsuarioRN();
            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $usuarioDTO->retNumIdUsuario();
            $usuarioDTO->retStrStaTipo();
            $usuarioDTO->retStrExpressaoCargoContato();
            /** Chama o componente SEI para retorno de complemento de dados de usuário **/
            $usuarioDTO = $usuarioRN->consultarRN0489($usuarioDTO);

            if($usuarioDTO->getStrStaTipo() == UsuarioRN::$TU_SIP){
                $relAssinanteUnidadeDTO = new RelAssinanteUnidadeDTO();
                $relAssinanteUnidadeDTO->retNumIdAssinante();
                if($assinanteDTOConsulta->isSetNumIdAssinante() && $assinanteDTOConsulta->getNumIdAssinante() != ''){
                    $relAssinanteUnidadeDTO->setNumIdAssinante($assinanteDTOConsulta->getNumIdAssinante());
                }
                $relAssinanteUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

                $relAssinanteUnidadeRN = new RelAssinanteUnidadeRN();
                /** Chama o componente SEI para consulta dos assinantes relacionados a unidade **/
                $arrObjRelAssinanteUnidadeDTO = $relAssinanteUnidadeRN->listarRN1380($relAssinanteUnidadeDTO);

                if (count($arrObjRelAssinanteUnidadeDTO) > 0) {
                    $assinanteDTOConsulta->retStrCargoFuncao();
                    $assinanteDTOConsulta->retNumIdAssinante();
                    $assinanteDTOConsulta->setNumIdAssinante(InfraArray::converterArrInfraDTO($arrObjRelAssinanteUnidadeDTO, 'IdAssinante'), InfraDTO::$OPER_IN);

                    $assinanteRN = new AssinanteRN();
                    /** Chama o componente SEI para retorno dos assinantes **/
                    $arrAssinanteDTO = $assinanteRN->listarRN1339($assinanteDTOConsulta);

                    foreach($arrAssinanteDTO as $assinanteDTO) {
                        $result[] = array(
                            'id' => $assinanteDTO->getNumIdAssinante(),
                            'nome' => $assinanteDTO->getStrCargoFuncao()
                        );
                    }
                }

            } else if ($usuarioDTO->getStrStaTipo() == UsuarioRN::$TU_EXTERNO) {
                $result[] = array(
                    'id' => null,
                    'nome' => 'Usuário Externo'
                );
            }

            if ($usuarioDTO->getStrExpressaoCargoContato() != null) {
                $result[] = array(
                    'id' => null,
                    'nome' => $usuarioDTO->getStrExpressaoCargoContato()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $assinanteDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}