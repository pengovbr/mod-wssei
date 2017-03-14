<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiBlocoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Consultar Blocos
     * @param UnidadeDTO $unidadeDTO
     * @return array
     */
    protected function listarBlocoUnidadeConectado(UnidadeDTO $unidadeDTO){
        try{
            $result = array();
            $blocoDTOConsulta = new BlocoDTO();
            if(!$unidadeDTO->getNumMaxRegistrosRetorno()){
                $blocoDTOConsulta->setNumMaxRegistrosRetorno(10);
            }else{
                $blocoDTOConsulta->setNumMaxRegistrosRetorno($unidadeDTO->getNumMaxRegistrosRetorno());
            }
            if(is_null($unidadeDTO->getNumPaginaAtual())){
                $blocoDTOConsulta->setNumPaginaAtual(0);
            }else{
                $blocoDTOConsulta->setNumPaginaAtual($unidadeDTO->getNumPaginaAtual());
            }
            if(!$unidadeDTO->isSetNumIdUnidade()){
                $blocoDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            }else{
                $blocoDTOConsulta->setNumIdUnidade($unidadeDTO->getNumIdUnidade());
            }

            $blocoRN = new BlocoRN();
            $blocoDTOConsulta->setNumIdUnidadeRelBlocoUnidade($unidadeDTO->getNumIdUnidade());
            $blocoDTOConsulta->setNumIdUnidade($unidadeDTO->getNumIdUnidade());
            $blocoDTOConsulta->adicionarCriterio(
                array('StaTipo', 'StaEstado'),
                array(InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_IN),
                array('A', array('D', 'C', 'R', 'A')),
                InfraDTO::$OPER_LOGICO_AND
            );
            $blocoDTOConsulta->retNumIdBloco();
            $blocoDTOConsulta->retNumIdUnidade();
            $blocoDTOConsulta->retStrSiglaUnidade();
            $blocoDTOConsulta->retStrStaEstado();
            $blocoDTOConsulta->retStrDescricao();
            $blocoDTOConsulta->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);
            $ret = $blocoRN->listarRN1277($blocoDTOConsulta);
            /** @var BlocoDTO $blocoDTO */
            foreach($ret as $blocoDTO){
                $arrUnidades[] = array(
                    'idUnidade' => $blocoDTO->getNumIdUnidade(),
                    'unidade' => $blocoDTO->getStrSiglaUnidade()
                );
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
            return array(
                'sucesso' => true,
                'data' => $result,
                'total' => $blocoDTOConsulta->getNumTotalRegistros()
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
            $arrRelProtocolo = $relBlocoProtocoloRN->listarRN1291($relBlocoProtocoloDTOConsulta);
            if($arrRelProtocolo){
                $anexoRN = new AnexoRN();
                $assinaturaRN = new AssinaturaRN();
                $protocoloRN = new ProtocoloRN();
                /** @var RelBlocoProtocoloDTO $relBlocoProtocoloDTO */
                foreach($arrRelProtocolo as $relBlocoProtocoloDTO){
                    $relProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
                    $relProtocoloProtocoloDTOConsulta = new RelProtocoloProtocoloDTO();
                    $relProtocoloProtocoloDTOConsulta->adicionarCriterio(
                        array('IdProtocolo1', 'IdProtocolo2'),
                        array(InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_IGUAL),
                        array($relBlocoProtocoloDTO->getDblIdProtocolo(), $relBlocoProtocoloDTO->getDblIdProtocolo()),
                        InfraDTO::$OPER_LOGICO_OR
                    );
                    $relProtocoloProtocoloDTOConsulta->retTodos();
                    $arrProtocoloProtocolo = $relProtocoloProtocoloRN->listarRN0187($relProtocoloProtocoloDTOConsulta);
                    if($arrProtocoloProtocolo){
                        $arrResultAssinatura = array();
                        /** @var RelProtocoloProtocoloDTO $relProtocoloProtocoloDTO */
                        foreach($arrProtocoloProtocolo as $relProtocoloProtocoloDTO){
                            $protocoloDTO = new ProtocoloDTO();
                            $protocoloDTO->setDblIdProtocolo($relProtocoloProtocoloDTO->getDblIdProtocolo1());
                            $protocoloDTO->retTodos();
                            $protocoloDTO->retStrNomeTipoProcedimentoProcedimento();
                            $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
                            $assinaturaDTOConsulta = new AssinaturaDTO();
                            $assinaturaDTOConsulta->setDblIdProcedimentoDocumento($protocoloDTO->getDblIdProtocolo());
                            $assinaturaDTOConsulta->setDblIdDocumento($protocoloDTO->getDblIdProtocolo());
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
                                    'numero' => $protocoloDTO->getStrProtocoloFormatado(),
                                    'numeroProcesso' => $protocoloDTO->getStrProtocoloFormatado(),
                                    'tipo' => $protocoloDTO->getStrNomeTipoProcedimentoProcedimento(),
                                    'assinaturas' => $arrResultAssinatura
                                ),
                                'anotacao' => $relBlocoProtocoloDTO->getStrAnotacao()
                            );
                        }
                    }
                }
            }

            return array(
                'sucesso' => true,
                'data' => $result,
                'total' => $relBlocoProtocoloDTOConsulta->getNumTotalRegistros()
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

            return array(
                'sucesso' => true,
                'mensagem' => 'Anotação realizada com sucesso.'
            );
        }catch (Exception $e){
            $message = $e->getMessage();
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
            return array(
                'sucesso' => false,
                'mensagem' => $message,
                'exception' => $e
            );
        }
    }

}