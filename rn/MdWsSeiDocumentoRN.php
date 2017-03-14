<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiDocumentoRN extends InfraRN {

    CONST NOME_ATRIBUTO_ANDAMENTO_DOCUMENTO = 'DOCUMENTO';

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Metodo simplificado (abstraido) de assinatura de documentos
     * @param array $arrIdDocumento
     * @param $idOrgao
     * @param $strCargoFuncao
     * @param $siglaUsuario
     * @param $senhaUsuario
     * @param $idUsuario
     * @return array
     */
    public function apiAssinarDocumentos(array $arrIdDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario){
        $arrDocumentoDTO = array();
        foreach($arrIdDocumento as $dblIdDocumento){
            $documentoDTO = new DocumentoDTO();
            $documentoDTO->setDblIdDocumento($dblIdDocumento);
            $arrDocumentoDTO[] = $documentoDTO;
        }
        $assinaturaDTO = new AssinaturaDTO();
        $assinaturaDTO->setStrSiglaUsuario($siglaUsuario);
        $assinaturaDTO->setStrSenhaUsuario($senhaUsuario);
        $assinaturaDTO->setNumIdUsuario($idUsuario);
        $assinaturaDTO->setNumIdOrgaoUsuario($idOrgao);
        $assinaturaDTO->setStrCargoFuncao($strCargoFuncao);
        $assinaturaDTO->setArrObjDocumentoDTO($arrDocumentoDTO);
        return $this->assinarDocumento($assinaturaDTO);
    }

    /**
     * Metodo simplificado (abstraido) de assinatura de documento
     * @param array $arrIdDocumento
     * @param $idOrgao
     * @param $strCargoFuncao
     * @param $siglaUsuario
     * @param $senhaUsuario
     * @param $idUsuario
     * @return array
     */
    public function apiAssinarDocumento($idDocumento, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario){
        $arrDocumentoDTO = array();
        $documentoDTO = new DocumentoDTO();
        $documentoDTO->setDblIdDocumento($idDocumento);
        $arrDocumentoDTO[] = $documentoDTO;
        $assinaturaDTO = new AssinaturaDTO();
        $assinaturaDTO->setStrSiglaUsuario($siglaUsuario);
        $assinaturaDTO->setStrSenhaUsuario($senhaUsuario);
        $assinaturaDTO->setNumIdUsuario($idUsuario);
        $assinaturaDTO->setNumIdOrgaoUsuario($idOrgao);
        $assinaturaDTO->setStrCargoFuncao($strCargoFuncao);
        $assinaturaDTO->setArrObjDocumentoDTO($arrDocumentoDTO);
        return $this->assinarDocumento($assinaturaDTO);
    }

    /**
     * Realizar Assinatura Eletrônica
     * @param AssinaturaDTO $assinaturaDTO
     * @return array
     */
    public function assinarDocumentoControlado(AssinaturaDTO $assinaturaDTO){
        try{
            $assinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
            $assinaturaDTO->setNumIdContextoUsuario(null);
            $documentoRN = new DocumentoRN();
            $documentoRN->assinarInterno($assinaturaDTO);
            return array(
                'sucesso' => true,
                'mensagem' => 'Documento em bloco assinado com sucesso.'
            );
        }catch (Exception $e){
            return array(
                'sucesso' => false,
                'mensagem' => $e->getMessage(),
                'exception' => $e
            );
        }
    }

    /**
     * @param DocumentoDTO $documentoDTO
     *   id documento obrigatorio
     * @return array
     */
    protected function darCienciaControlado(DocumentoDTO $documentoDTO){
        try{
            $documentoRN = new DocumentoRN();
            if(!$documentoDTO->getDblIdDocumento()){
                throw new InfraException('O documento não foi informado.');
            }
            $documentoRN->darCiencia($documentoDTO);
            return array(
                'sucesso' => true,
                'mensagem' => 'Ciência documento realizado com sucesso.'
            );
        }catch (Exception $e){
            return array(
                'sucesso' => false,
                'mensagem' => $e->getMessage(),
                'exception' => $e
            );
        }
    }

    protected function downloadAnexoConectado(ProtocoloDTO $protocoloDTO){
        try{
            if(!$protocoloDTO->isSetDblIdProtocolo() || !$protocoloDTO->getDblIdProtocolo()){
                throw new InfraException('O protocolo deve ser informado!');
            }
            $documentoDTO = new DocumentoDTO();
            $documentoDTO->setDblIdProtocoloProtocolo($protocoloDTO->getDblIdProtocolo());
            $documentoDTO->retStrConteudo();
            $documentoDTO->retStrConteudoAssinatura();
            $documentoBD = new DocumentoRN();
            $resultDocumento = $documentoBD->listarRN0008($documentoDTO);

            if(!empty($resultDocumento)){
                $documento = $resultDocumento[0];
                if ($documento->getStrConteudo()) {
                    $html = $documento->getStrConteudo() . $documento->getStrConteudoAssinatura();
                    return ["html" => $html];
                }
            }

            $anexoDTO = new AnexoDTO();
            $anexoDTO->retNumIdAnexo();
            $anexoDTO->retDthInclusao();
            $anexoDTO->retDthInclusao();
            $anexoDTO->retStrNome();
            $anexoDTO->retStrHash();
            $anexoDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $anexoDTO->setStrSinAtivo('S');
            $anexoRN = new AnexoRN();
            $resultAnexo = $anexoRN->listarRN0218($anexoDTO);
            if(empty($resultAnexo)){
                throw new InfraException('Documento não encontrado!');
            }
            $anexo = $resultAnexo[0];
            SeiINT::download($anexo);
        }catch (Exception $e){
            return array(
                'sucesso' => false,
                'mensagem' => $e->getMessage(),
                'exception' => $e
            );
        }
    }

    /**
     * Listar Ciencias realizadas em um Documento
     * @param MdWsSeiProcessoDTO $mdWsSeiProcessoDTOParam
     *   *valor = protocoloformatado?
     * @return array
     */
    protected function listarCienciaDocumentoConectado(MdWsSeiProcessoDTO $mdWsSeiProcessoDTOParam){
        try{
            if(!$mdWsSeiProcessoDTOParam->isSetStrValor()){
                throw new InfraException('Número do documento não informado.');
            }

            $result = array();
            $atributoAndamentoDTOConsulta = new AtributoAndamentoDTO();
            $atributoAndamentoDTOConsulta->retTodos();
            $atributoAndamentoDTOConsulta->retDthAberturaAtividade();
            $atributoAndamentoDTOConsulta->retStrSiglaUsuarioOrigemAtividade();
            $atributoAndamentoDTOConsulta->retStrSiglaUnidadeOrigemAtividade();
            $atributoAndamentoDTOConsulta->setNumIdTarefaAtividade(TarefaRN::$TI_DOCUMENTO_CIENCIA);
            $atributoAndamentoDTOConsulta->setStrValor($mdWsSeiProcessoDTOParam->getStrValor());
            $atributoAndamentoDTOConsulta->setStrNome(self::NOME_ATRIBUTO_ANDAMENTO_DOCUMENTO);
            $atributoAndamentoRN = new AtributoAndamentoRN();
            $ret = $atributoAndamentoRN->listarRN1367($atributoAndamentoDTOConsulta);
            $tarefaDTO = new TarefaDTO();
            $tarefaDTO->retStrNome();
            $tarefaDTO->setNumIdTarefa($atributoAndamentoDTOConsulta->getNumIdTarefaAtividade());
            $tarefaRN = new TarefaRN();
            $tarefaDTO = $tarefaRN->consultar($tarefaDTO);
            $mdWsSeiProcessoRN = new MdWsSeiProcessoRN();
            /** @var AtributoAndamentoDTO $atributoAndamentoDTO */
            foreach($ret as $atributoAndamentoDTO){
                $mdWsSeiProcessoDTO = new MdWsSeiProcessoDTO();
                $mdWsSeiProcessoDTO->setNumIdAtividade($atributoAndamentoDTO->getNumIdAtividade());
                $mdWsSeiProcessoDTO->setStrTemplate($tarefaDTO->getStrNome());
                $result[] = array(
                    'data' => $atributoAndamentoDTO->getDthAberturaAtividade(),
                    'unidade' => $atributoAndamentoDTO->getStrSiglaUnidadeOrigemAtividade(),
                    'nome' => $atributoAndamentoDTO->getStrSiglaUsuarioOrigemAtividade(),
                    'descricao' => $mdWsSeiProcessoRN->traduzirTemplate($mdWsSeiProcessoDTO)
                );
            }

            return array(
                'sucesso' => true,
                'data' => $result
            );
        }catch (Exception $e){
            return array(
                'sucesso' => false,
                'mensagem' => $e->getMessage(),
                'exception' => $e
            );
        }
    }

    /**
     * Listar assinaturas do documento
     * @param DocumentoDTO $documentoDTOParam
     * @return array
     */
    protected function listarAssinaturasDocumentoConectado(DocumentoDTO $documentoDTOParam){
        try{
            if(!$documentoDTOParam->isSetDblIdDocumento()){
                throw new InfraException('O documento não foi informado.');
            }

            $result = array();
            $assinaturaDTOConsulta = new AssinaturaDTO();
            $assinaturaDTOConsulta->retTodos();
            $assinaturaDTOConsulta->retStrSiglaUnidade();
            $assinaturaDTOConsulta->setDblIdDocumento($documentoDTOParam->getDblIdDocumento());
            $assinaturaRN = new AssinaturaRN();
            $ret = $assinaturaRN->listarRN1323($assinaturaDTOConsulta);
            /** @var AssinaturaDTO $assinaturaDTO */
            foreach($ret as $assinaturaDTO){
                $result[] = array(
                    'nome' => $assinaturaDTO->getStrNome(),
                    'cargo' => $assinaturaDTO->getStrTratamento(),
                    'unidade' => $assinaturaDTO->getStrSiglaUnidade()
                );
            }

            return array(
                'sucesso' => true,
                'data' => $result
            );
        }catch (Exception $e){
            return array(
                'sucesso' => false,
                'mensagem' => $e->getMessage(),
                'exception' => $e
            );
        }
    }

}