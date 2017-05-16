<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiBlocoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Consultar Blocos
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function listarBlocoConectado(BlocoDTO $blocoDTO){
        try{
            $result = array();
            $blocoRN = new BlocoRN();
            $blocoDTOConsulta = new BlocoDTO();
            if(!$blocoDTO->getNumMaxRegistrosRetorno()){
                $blocoDTOConsulta->setNumMaxRegistrosRetorno(10);
            }else{
                $blocoDTOConsulta->setNumMaxRegistrosRetorno($blocoDTO->getNumMaxRegistrosRetorno());
            }
            if(is_null($blocoDTO->getNumPaginaAtual())){
                $blocoDTOConsulta->setNumPaginaAtual(0);
            }else{
                $blocoDTOConsulta->setNumPaginaAtual($blocoDTO->getNumPaginaAtual());
            }

            $blocoDTOConsulta->setStrStaEstado(BlocoRN::$TE_CONCLUIDO,InfraDTO::$OPER_DIFERENTE);
            $blocoDTOConsulta->setStrStaTipo(BlocoRN::$TB_ASSINATURA);
            $blocoDTOConsulta->retNumIdBloco();
            $blocoDTOConsulta->retNumIdUnidade();
            $blocoDTOConsulta->retStrDescricao();
            $blocoDTOConsulta->retStrStaTipo();
            $blocoDTOConsulta->retStrStaEstado();
            $blocoDTOConsulta->retStrStaEstadoDescricao();
            $blocoDTOConsulta->retStrTipoDescricao();
            $blocoDTOConsulta->retStrSiglaUnidade();
            $blocoDTOConsulta->retStrDescricaoUnidade();
            $blocoDTOConsulta->retStrSinVazio();
            $blocoDTOConsulta->retArrObjRelBlocoUnidadeDTO();
            $blocoDTOConsulta->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);

            $ret = $blocoRN->pesquisar($blocoDTOConsulta);

            /** @var BlocoDTO $blocoDTO */
            foreach($ret as $blocoDTO){
                $arrUnidades = array();
                /** @var RelBlocoUnidadeDTO $relBlocoUnidadeDTO */
                foreach($blocoDTO->getArrObjRelBlocoUnidadeDTO() as $relBlocoUnidadeDTO){
                    $arrUnidades[] = array(
                        'idUnidade' => $relBlocoUnidadeDTO->getNumIdUnidade(),
                        'unidade' => $relBlocoUnidadeDTO->getStrSiglaUnidade()
                    );
                }
                $result[] = array(
                    'id' => $blocoDTO->getNumIdBloco(),
                    'atributos' => array(
                        'idBloco' => $blocoDTO->getNumIdBloco(),
                        'idUnidade' => $blocoDTO->getNumIdUnidade(),
                        'siglaUnidade' => $blocoDTO->getStrSiglaUnidade(),
                        'estado' => $blocoDTO->getStrStaEstado(),
                        'descricao' => $blocoDTO->getStrDescricao(),
                        'unidades' => $arrUnidades
                    )
                );
            }
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $blocoDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Consultar Documentos por Bloco
     * @param BlocoDTO $blocoDTOConsulta
     * @return array
     */
    protected function listarDocumentosBlocoConectado(BlocoDTO $blocoDTOConsulta){
        try{
            if(!$blocoDTOConsulta->getNumIdBloco()){
                throw new InfraException('Bloco não informado.');
            }
            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            $relBlocoProtocoloDTOConsulta = new RelBlocoProtocoloDTO();
            if($blocoDTOConsulta->getNumMaxRegistrosRetorno()){
                $relBlocoProtocoloDTOConsulta->setNumMaxRegistrosRetorno($blocoDTOConsulta->getNumMaxRegistrosRetorno());
            }else{
                $relBlocoProtocoloDTOConsulta->setNumMaxRegistrosRetorno(10);
            }
            if(!is_null($blocoDTOConsulta->getNumPaginaAtual())){
                $relBlocoProtocoloDTOConsulta->setNumPaginaAtual($blocoDTOConsulta->getNumPaginaAtual());
            }else{
                $relBlocoProtocoloDTOConsulta->setNumPaginaAtual(0);
            }
            $result = array();
            $relBlocoProtocoloDTOConsulta->setNumIdBloco($blocoDTOConsulta->getNumIdBloco());
            $relBlocoProtocoloDTOConsulta->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);
            $relBlocoProtocoloDTOConsulta->retDblIdProtocolo();
            $relBlocoProtocoloDTOConsulta->retStrAnotacao();
            $relBlocoProtocoloDTOConsulta->retStrProtocoloFormatadoProtocolo();
            $arrRelProtocolo = $relBlocoProtocoloRN->listarRN1291($relBlocoProtocoloDTOConsulta);
            if($arrRelProtocolo){
                $anexoRN = new AnexoRN();
                $assinaturaRN = new AssinaturaRN();
                $protocoloRN = new ProtocoloRN();
                $protocoloProtocoloRN = new RelProtocoloProtocoloRN();
                /** @var RelBlocoProtocoloDTO $relBlocoProtocoloDTO */
                foreach($arrRelProtocolo as $relBlocoProtocoloDTO){
                    $relProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
                    $relProtocoloProtocoloDTO->setDblIdProtocolo2($relBlocoProtocoloDTO->getDblIdProtocolo());
                    $relProtocoloProtocoloDTO->retDblIdProtocolo1();
                    $relProtocoloProtocoloDTO = $protocoloProtocoloRN->consultarRN0841($relProtocoloProtocoloDTO);
                    $arrResultAssinatura = array();
                    $protocoloDTO = new ProtocoloDTO();
                    $protocoloDTO->setDblIdProtocolo($relProtocoloProtocoloDTO->getDblIdProtocolo1());
                    $protocoloDTO->retStrNomeTipoProcedimentoProcedimento();
                    $protocoloDTO->retStrProtocoloFormatado();
                    $protocoloDTO->retDblIdProtocolo();
                    $protocoloDTO->retDtaGeracao();
                    $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
                    $assinaturaDTOConsulta = new AssinaturaDTO();
                    $assinaturaDTOConsulta->setDblIdDocumento($relBlocoProtocoloDTO->getDblIdProtocolo());
                    $assinaturaDTOConsulta->retStrNome();
                    $assinaturaDTOConsulta->retStrTratamento();
                    $arrAssinatura = $assinaturaRN->listarRN1323($assinaturaDTOConsulta);
                    /** @var AssinaturaDTO $assinaturaDTO */
                    foreach($arrAssinatura as $assinaturaDTO){
                        $arrResultAssinatura[] = array(
                            'nome' => $assinaturaDTO->getStrNome(),
                            'cargo' => $assinaturaDTO->getStrTratamento()
                        );
                    }
                    $anexoDTOConsulta = new AnexoDTO();
                    $anexoDTOConsulta->retTodos();
                    $anexoDTOConsulta->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
                    $anexoDTOConsulta->setStrSinAtivo('S');
                    $anexoDTOConsulta->setNumMaxRegistrosRetorno(1);
                    $retAnexo = $anexoRN->listarRN0218($anexoDTOConsulta);
                    $mimetype = null;
                    if($retAnexo){
                        $mimetype = $retAnexo[0]->getStrNome();
                        $mimetype = substr($mimetype, strrpos($mimetype, '.')+1);
                    }
                    $result[] = array(
                        'id' => $protocoloDTO->getDblIdProtocolo(),
                        'atributos' => array(
                            'idDocumento' => $protocoloDTO->getDblIdProtocolo(),
                            'mimeType' => ($mimetype)?$mimetype:'html',
                            'data' => $protocoloDTO->getDtaGeracao(),
                            'numero' => $relBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo(),
                            'numeroProcesso' => $protocoloDTO->getStrProtocoloFormatado(),
                            'tipo' => $protocoloDTO->getStrNomeTipoProcedimentoProcedimento(),
                            'assinaturas' => $arrResultAssinatura
                        ),
                        'anotacao' => $relBlocoProtocoloDTO->getStrAnotacao()
                    );
                }
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $relBlocoProtocoloDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo publico que cadastra a anotacao em um bloco
     * @param array $post
     * @return array
     */
    public function cadastrarAnotacaoBlocoFromRequest(array $post){
        $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
        if($post['protocolo']){
            $relBlocoProtocoloDTO->setDblIdProtocolo($post['protocolo']);
        }
        if($post['bloco']){
            $relBlocoProtocoloDTO->setNumIdBloco($post['bloco']);
        }
        if($post['anotacao']){
            $relBlocoProtocoloDTO->setStrAnotacao($post['anotacao']);
        }

        return $this->cadastrarAnotacaoBloco($relBlocoProtocoloDTO);
    }

    /**
     * Cadastrar Anotacao documento do Bloco
     * @param RelBlocoProtocoloDTO $relBlocoProtocoloDTOParam
     * @return array
     */
    protected function cadastrarAnotacaoBlocoControlado(RelBlocoProtocoloDTO $relBlocoProtocoloDTOParam){

        try {
            if (!$relBlocoProtocoloDTOParam->isSetNumIdBloco()) {
                throw new InfraException('O bloco deve ser informado.');
            }
            if (!$relBlocoProtocoloDTOParam->isSetNumIdBloco()) {
                throw new InfraException('O protocolo deve ser informado.');
            }
            if (!$relBlocoProtocoloDTOParam->isSetStrAnotacao()) {
                throw new InfraException('A anotação deve ser informada.');
            }
            $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
            $relBlocoProtocoloDTO->setNumIdBloco($relBlocoProtocoloDTOParam->getNumIdBloco());
            $relBlocoProtocoloDTO->setDblIdProtocolo($relBlocoProtocoloDTOParam->getDblIdProtocolo());
            $relBlocoProtocoloDTO->retTodos();
            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            $relBlocoProtocoloDTO = $relBlocoProtocoloRN->consultarRN1290($relBlocoProtocoloDTO);
            if (!$relBlocoProtocoloDTO) {
                throw new InfraException('Documento não encontrado no bloco informado.');
            }
            $relBlocoProtocoloDTO->setStrAnotacao($relBlocoProtocoloDTOParam->getStrAnotacao());
            $relBlocoProtocoloRN->alterarRN1288($relBlocoProtocoloDTO);

            return MdWsSeiRest::formataRetornoSucessoREST('Anotação realizada com sucesso.');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}