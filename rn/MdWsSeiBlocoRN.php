<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiBlocoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Método que retorna o bloco de assinatura
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function retornarControlado(BlocoDTO $blocoDTO){
        try{
            if(!$blocoDTO->isSetNumIdBloco()){
                throw new Exception('Bloco não informado!');
            }
            $blocoRN = new BlocoRN();
            $blocoRN->retornar(array($blocoDTO));

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco retornado com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Assina todos os documentos do bloco
     * @param $idOrgao
     * @param $strCargoFuncao
     * @param $siglaUsuario
     * @param $senhaUsuario
     * @param $idUsuario
     * @return array
     */
    public function apiAssinarBloco($idBloco, $idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario)
    {
        try{
            $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
            $objRelBlocoProtocoloDTO->setNumIdBloco($idBloco);
            $objRelBlocoProtocoloDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

            $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
            $arrIdDocumentos = InfraArray::converterArrInfraDTO($objRelBlocoProtocoloRN->listarProtocolosBloco($objRelBlocoProtocoloDTO),'IdProtocolo');
            if(!$arrIdDocumentos){
                return MdWsSeiRest::formataRetornoSucessoREST('Nenhum documento para ser assinado neste bloco.');
            }
            $assinaturaDTO = new AssinaturaDTO();
            $assinaturaDTO->setStrSiglaUsuario($siglaUsuario);
            $assinaturaDTO->setStrSenhaUsuario($senhaUsuario);
            $assinaturaDTO->setNumIdUsuario($idUsuario);
            $assinaturaDTO->setNumIdOrgaoUsuario($idOrgao);
            $assinaturaDTO->setStrCargoFuncao($strCargoFuncao);
            $assinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
            $assinaturaDTO->setNumIdContextoUsuario(null);
            $assinaturaDTO->setArrObjDocumentoDTO(InfraArray::gerarArrInfraDTO('DocumentoDTO','IdDocumento',$arrIdDocumentos));
            $documentoRN = new DocumentoRN();
            $documentoRN->assinarInterno($assinaturaDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Documentos em bloco assinados com sucesso.');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Pesquisa blocos de assinatura
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function pesquisarBlocoAssinaturaConectado(BlocoDTO $blocoDTOConsulta){
        try{
            $result = array();
            $blocoRN = new BlocoRN();
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

            /** Acessa o componente SEI para realizar a pesquisa de blocos de assinatura */
            $ret = $blocoRN->pesquisar($blocoDTOConsulta);

            /** @var BlocoDTO $blocoDTO */
            foreach($ret as $blocoDTO){
                $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
                $relBlocoProtocoloDTOConsulta = new RelBlocoProtocoloDTO();
                $relBlocoProtocoloDTOConsulta->setNumMaxRegistrosRetorno(1);
                $relBlocoProtocoloDTOConsulta->setNumPaginaAtual(0);
                $relBlocoProtocoloDTOConsulta->setNumIdBloco($blocoDTO->getNumIdBloco());
                $relBlocoProtocoloDTOConsulta->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);
                $relBlocoProtocoloDTOConsulta->retDblIdProtocolo();
                /** Acessa o componente SEI para consultar o total de documentos dentro de um bloco de assinatura */
                $relBlocoProtocoloRN->listarRN1291($relBlocoProtocoloDTOConsulta);
                $numeroDocumentos = $relBlocoProtocoloDTOConsulta->getNumTotalRegistros();

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
                        'unidades' => $arrUnidades,
                        'numeroDocumentos' => $numeroDocumentos
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
                $relBlocoProtocoloDTOConsulta->setNumMaxRegistrosRetorno(10000000);
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
                    $relProtocoloProtocoloDTO->setStrStaAssociacao($protocoloProtocoloRN::$TA_DOCUMENTO_CIRCULAR , InfraDTO::$OPER_DIFERENTE);
                    $relProtocoloProtocoloDTO->setDblIdProtocolo2($relBlocoProtocoloDTO->getDblIdProtocolo());
                    $relProtocoloProtocoloDTO->retDblIdProtocolo1();
                    $relProtocoloProtocoloDTO = $protocoloProtocoloRN->consultarRN0841($relProtocoloProtocoloDTO);
                    $arrResultAssinatura = array();
                    $protocoloDTO = new ProtocoloDTO();
                    $protocoloDTO->setDblIdProtocolo($relProtocoloProtocoloDTO->getDblIdProtocolo1());
                    $protocoloDTO->retStrNomeSerieDocumento();
                    $protocoloDTO->retStrProtocoloFormatado();
                    $protocoloDTO->retDblIdProtocolo();
                    $protocoloDTO->retDtaGeracao();
                    $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);

                    $protocoloDTODocumento = new ProtocoloDTO();
                    $protocoloDTODocumento->retStrNomeSerieDocumento();
                    $protocoloDTODocumento->setDblIdProtocolo($relBlocoProtocoloDTO->getDblIdProtocolo());
                    $protocoloDTODocumento = $protocoloRN->consultarRN0186($protocoloDTODocumento);

                    $assinaturaDTOConsulta = new AssinaturaDTO();
                    $assinaturaDTOConsulta->setDblIdDocumento($relBlocoProtocoloDTO->getDblIdProtocolo());
                    $assinaturaDTOConsulta->retStrNome();
                    $assinaturaDTOConsulta->retStrTratamento();
                    $assinaturaDTOConsulta->retNumIdUsuario();
                    $arrAssinatura = $assinaturaRN->listarRN1323($assinaturaDTOConsulta);
                    /** @var AssinaturaDTO $assinaturaDTO */
                    foreach($arrAssinatura as $assinaturaDTO){
                        $arrResultAssinatura[] = array(
                            'nome' => $assinaturaDTO->getStrNome(),
                            'cargo' => $assinaturaDTO->getStrTratamento(),
                            'idUsuario' => $assinaturaDTO->getNumIdUsuario(),
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
                            'idDocumento' => $relBlocoProtocoloDTO->getDblIdProtocolo(),
                            'mimeType' => ($mimetype)?$mimetype:'html',
                            'data' => $protocoloDTO->getDtaGeracao(),
                            'numero' => $relBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo(),
                            'numeroProcesso' => $protocoloDTO->getStrProtocoloFormatado(),
                            'tipo' => $protocoloDTODocumento->getStrNomeSerieDocumento(),
                            'assinaturas' => $arrResultAssinatura
                        ),
                        'anotacao' => $relBlocoProtocoloDTO->getStrAnotacao()
                    );
                }
            }


            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, count($result));
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
            if (!$relBlocoProtocoloDTOParam->isSetDblIdProtocolo()) {
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

    /**
     * Método que cadastra um bloco de assinatura
     * @param \Slim\Http\Request $request
     * @return array
     */
    public function cadastrarBlocoAssinaturaRequest(\Slim\Http\Request $request)
    {
        try{
            $result = array();
            if(!$request->getParam('descricao')){
                throw new Exception('Descrição não informada.');
            }
            $blocoDTO = new BlocoDTO();
            $blocoDTO->setStrStaTipo(BlocoRN::$TB_ASSINATURA);
            $blocoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $blocoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $blocoDTO->setStrIdxBloco(null);
            $blocoDTO->setStrStaEstado(BlocoRN::$TE_ABERTO);
            $blocoDTO->setStrDescricao($request->getParam('descricao'));

            $arrObjRelBlocoUnidadeDTO = array();
            $arrUnidades = array();
            if($request->getParam('unidades') != ''){
                $arrUnidades = explode(',', $request->getParam('unidades'));
                foreach($arrUnidades as $numIdUnidade){
                    $objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
                    $objRelBlocoUnidadeDTO->setNumIdBloco(null);
                    $objRelBlocoUnidadeDTO->setNumIdUnidade($numIdUnidade);
                    $arrObjRelBlocoUnidadeDTO[] = $objRelBlocoUnidadeDTO;
                }
            }
            $blocoDTO->setArrObjRelBlocoUnidadeDTO($arrObjRelBlocoUnidadeDTO);
            $blocoRN = new BlocoRN();
            /** Acessa o componente SEI para cadastro de Bloco de assinatura */
            $blocoRN->cadastrarRN1273($blocoDTO);

            $result = array(
                'id' => $blocoDTO->getNumIdBloco(),
                'descricao' => $blocoDTO->getStrDescricao(),
                'unidades' => $arrUnidades,
            );

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco de assinatura cadastrado com sucesso.', $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método generico para excluir blocos
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    public function excluirBlocos(array $arrIdBlocos)
    {
        try{
            if(empty($arrIdBlocos)){
                throw new Exception('Bloco não informado.');
            }
            $blocoRN = new BlocoRN();
            $arrBlocosExclusao = array();
            foreach($arrIdBlocos as $idBloco) {
                $blocoDTO = new BlocoDTO();
                $blocoDTO->setNumIdBloco($idBloco);
                $arrBlocosExclusao[] = $blocoDTO;
            }
            /** Chama o componente SEI para exclusão de blocos */
            $blocoRN->excluirRN1275($arrBlocosExclusao);

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco de assinatura excluído com sucesso.', null);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método generico para excluir blocos
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    public function concluirBlocos(array $arrIdBlocos)
    {
        try{
            if(empty($arrIdBlocos)){
                throw new Exception('Bloco não informado.');
            }
            $blocoRN = new BlocoRN();
            $arrBlocosExclusao = array();
            foreach($arrIdBlocos as $idBloco) {
                $blocoDTO = new BlocoDTO();
                $blocoDTO->setNumIdBloco($idBloco);
                $arrBlocosExclusao[] = $blocoDTO;
            }
            /** Chama o componente SEI para conclusão de blocos */
            $blocoRN->concluir($arrBlocosExclusao);

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco de assinatura concluído com sucesso.', null);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que altera um bloco de assinatura
     * @param \Slim\Http\Request $request
     * @return array
     */
    public function alterarBlocoAssinaturaRequest(\Slim\Http\Request $request)
    {
        try{
            $result = array();
            if(!$request->getParam('descricao')){
                throw new Exception('Descrição não informada.');
            }
            if(!$request->getAttribute('route')->getArgument('bloco')){
                throw new Exception('Bloco não informado.');
            }
            $blocoDTO = new BlocoDTO();
            $blocoDTO->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
            $blocoDTO->retTodos();
            $blocoRN = new BlocoRN();
            $blocoDTO = $blocoRN->consultarRN1276($blocoDTO);
            if(!$blocoDTO){
                throw new Exception('Bloco não encontrado.');
            }
            if($blocoDTO->getStrStaTipo() != BlocoRN::$TB_ASSINATURA){
                throw new Exception('Bloco diferente do informado.');
            }

            $blocoDTO->setStrDescricao($request->getParam('descricao'));

            $arrObjRelBlocoUnidadeDTO = array();
            $arrUnidades = array();
            if($request->getParam('unidades') != ''){
                $arrUnidades = explode(',', $request->getParam('unidades'));
                foreach($arrUnidades as $numIdUnidade){
                    $objRelBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
                    $objRelBlocoUnidadeDTO->setNumIdBloco(null);
                    $objRelBlocoUnidadeDTO->setNumIdUnidade($numIdUnidade);
                    $arrObjRelBlocoUnidadeDTO[] = $objRelBlocoUnidadeDTO;
                }
            }
            $blocoDTO->setArrObjRelBlocoUnidadeDTO($arrObjRelBlocoUnidadeDTO);
            /** Acessa o componente SEI para alteração de Bloco de assinatura */
            $blocoRN->alterarRN1274($blocoDTO);

            $result = array(
                'id' => $blocoDTO->getNumIdBloco(),
                'descricao' => $blocoDTO->getStrDescricao(),
                'unidades' => $arrUnidades,
            );

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco de assinatura alterado com sucesso.', $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}