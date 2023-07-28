<?
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiBlocoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * M�todo que retorna o bloco de assinatura
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function retornarBlocoControlado(BlocoDTO $blocoDTO){
        try{
            if(!$blocoDTO->getNumIdBloco()){
                throw new Exception('Bloco n�o informado!');
            }
            $blocoRN = new BlocoRN();
            /** Chamada ao componente SEI para retorno de bloco de assinatura */
            $blocoRN->retornar(array($blocoDTO));

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco retornado com sucesso!');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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
            sleep(3);
            $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
            $objRelBlocoProtocoloDTO->setNumIdBloco($idBloco);
            $objRelBlocoProtocoloDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

            $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
            /** Chama o componente SEI para retornar todos os IDs dos documentos do bloco para assinatura */
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
            // $assinaturaDTO->setNumIdContextoUsuario(null);
            $assinaturaDTO->setArrObjDocumentoDTO(InfraArray::gerarArrInfraDTO('DocumentoDTO','IdDocumento',$arrIdDocumentos));
            $documentoRN = new DocumentoRN();
            /** Chama o componente SEI para assinar os documentos */
            $documentoRN->assinarInterno($assinaturaDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Bloco assinado com sucesso.');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Assina os documentos selecionados do bloco
     * @param $strCargoFuncao
     * @param $siglaUsuario
     * @param $senhaUsuario
     * @param $idUsuario
     * @param $arrIdDocumentos
     * @return array
     */
    public function apiAssinarDocumentos($idOrgao, $strCargoFuncao, $siglaUsuario, $senhaUsuario, $idUsuario, $arrIdDocumentos)
    {
        try{
            sleep(3);
            if(!$arrIdDocumentos){
                return MdWsSeiRest::formataRetornoSucessoREST('Nenhum documento foi informado para ser assinado.');
            }
            $assinaturaDTO = new AssinaturaDTO();
            $assinaturaDTO->setStrSiglaUsuario($siglaUsuario);
            $assinaturaDTO->setStrSenhaUsuario($senhaUsuario);
            $assinaturaDTO->setNumIdUsuario($idUsuario);
            $assinaturaDTO->setNumIdOrgaoUsuario($idOrgao);
            $assinaturaDTO->setStrCargoFuncao($strCargoFuncao);
            $assinaturaDTO->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
            // $assinaturaDTO->setNumIdContextoUsuario(null);
            $assinaturaDTO->setArrObjDocumentoDTO(InfraArray::gerarArrInfraDTO('DocumentoDTO','IdDocumento',$arrIdDocumentos));
            $documentoRN = new DocumentoRN();
            /** Chama o componente SEI para realizar a assinatura dos documentos */
            $documentoRN->assinarInterno($assinaturaDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Documento(s) assinado(s) com sucesso.');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Retira os documentos selecionados do bloco
     * @param $idBloco
     * @param $arrIdDocumentos
     * @return array
     */
    public function apiRetirarDocumentos($idBloco, $arrIdDocumentos)
    {
        try{
            if(!$arrIdDocumentos){
                return MdWsSeiRest::formataRetornoSucessoREST('Nenhum documento foi informado.');
            }
            $arrObjRelBlocoProtocoloDTO = array();
            foreach($arrIdDocumentos as $idDocumento) {
                $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
                $relBlocoProtocoloDTO->setDblIdProtocolo($idDocumento);
                $relBlocoProtocoloDTO->setNumIdBloco($idBloco);
                $arrObjRelBlocoProtocoloDTO[] = $relBlocoProtocoloDTO;
            }
            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            /** Chama o componente SEI para exclus�o dos documentos do bloco */
            $relBlocoProtocoloRN->excluirRN1289($arrObjRelBlocoProtocoloDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Documento(s) removido(s) com sucesso.');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Retira os Processos selecionados do bloco
     * @param $idBloco
     * @param $arrIdProtocolos
     * @return array
     */
    public function apiRetirarProcessos($idBloco, $arrIdProtocolos)
    {
        try{
            if(!$arrIdProtocolos){
                return MdWsSeiRest::formataRetornoSucessoREST('Nenhum processo foi informado.');
            }
            $blocoDTOConsulta = new BlocoDTO();
            $blocoDTOConsulta->retTodos();
            $blocoDTOConsulta->setNumIdBloco($idBloco);
            $blocoRN = new BlocoRN();
            /** Chamando componente SEI para consulta de dados do bloco para valida��o **/
            $blocoDTOConsulta = $blocoRN->consultarRN1276($blocoDTOConsulta);
            if(!$blocoDTOConsulta || $blocoDTOConsulta->getNumIdUnidade() != SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
                throw new Exception('Bloco n�o encontrado.');
            }
            $arrObjRelBlocoProtocoloDTO = array();
            foreach($arrIdProtocolos as $idProtocolo) {
                $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
                $relBlocoProtocoloDTO->setDblIdProtocolo($idProtocolo);
                $relBlocoProtocoloDTO->setNumIdBloco($idBloco);
                $arrObjRelBlocoProtocoloDTO[] = $relBlocoProtocoloDTO;
            }
            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            /** Chama o componente SEI para exclus�o dos processos do bloco */
            $relBlocoProtocoloRN->excluirRN1289($arrObjRelBlocoProtocoloDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Processo(s) removido(s) com sucesso.');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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
            //$blocoDTOConsulta->setStrStaEstado(BlocoRN::$TE_CONCLUIDO,InfraDTO::$OPER_DIFERENTE);
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
            // $blocoDTOConsulta->retStrSinVazio();
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
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Consultar Documentos de um Bloco de Assinatura
     * @param RelBlocoProtocoloDTO $relBlocoProtocoloDTOConsulta
     * @return array
     */
    protected function listarDocumentosBlocoAssinaturaConectado(RelBlocoProtocoloDTO $relBlocoProtocoloDTOConsulta){
        try{
            if(!$relBlocoProtocoloDTOConsulta->getNumIdBloco()){
                throw new InfraException('Bloco n�o informado.');
            }
            $blocoDTO = new BlocoDTO();
            $blocoDTO->retStrStaTipo();
            $blocoDTO->retStrStaEstado();
            $blocoDTO->retStrTipoDescricao();
            $blocoDTO->retNumIdUnidade();
            $blocoDTO->setNumIdBloco($relBlocoProtocoloDTOConsulta->getNumIdBloco());

            $blocoRN = new BlocoRN();
            $blocoDTO = $blocoRN->consultarRN1276($blocoDTO);
            if(!$blocoDTO){
                throw new InfraException('Bloco n�o encontrado.');
            }
            $result = array();
            $arrAtributos = array(
                'assinar' => (
                    SessaoSEI::getInstance()->verificarPermissao('documento_assinar') &&
                    !($blocoDTO->getNumIdUnidade()==SessaoSEI::getInstance()->getNumIdUnidadeAtual() && $blocoDTO->getStrStaEstado()==BlocoRN::$TE_DISPONIBILIZADO)
                ),
                'retirar' => (
                    SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_excluir') &&
                    $blocoDTO->getStrStaEstado() != BlocoRN::$TE_DISPONIBILIZADO &&
                    $blocoDTO->getNumIdUnidade() == SessaoSEI::getInstance()->getNumIdUnidadeAtual()
                ),
                'anotar' => (
                    SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_alterar')
                )
            );

            $result['permissoes'] = $arrAtributos;

            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            $relBlocoProtocoloDTOConsulta->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);
            $relBlocoProtocoloDTOConsulta->retDblIdProtocolo();
            $relBlocoProtocoloDTOConsulta->retNumIdBloco();
            $relBlocoProtocoloDTOConsulta->retNumSequencia();
            $relBlocoProtocoloDTOConsulta->retNumIdUnidadeBloco();
            $relBlocoProtocoloDTOConsulta->retStrProtocoloFormatadoProtocolo();
            $relBlocoProtocoloDTOConsulta->retStrStaProtocoloProtocolo();
            $relBlocoProtocoloDTOConsulta->retStrAnotacao();
            $relBlocoProtocoloDTOConsulta->retDblIdProcedimentoDocumento();
            $relBlocoProtocoloDTOConsulta->retArrObjAssinaturaDTO();
            $relBlocoProtocoloDTOConsulta->retObjProtocoloDTO();

            /** Acessa o componente SEI para consulta dos documentos de um bloco */
            $ret = $relBlocoProtocoloRN->listarProtocolosBloco($relBlocoProtocoloDTOConsulta);
                /** @var RelBlocoProtocoloDTO $relBlocoProtocoloDTO */
            foreach($ret as $relBlocoProtocoloDTO){
                /** @var AssinaturaDTO $assinaturaDTO */
                $arrAssinaturas = array();
                foreach($relBlocoProtocoloDTO->getArrObjAssinaturaDTO() as $assinaturaDTO){
                    $arrAssinaturas[] = array(
                        'nome' => $assinaturaDTO->getStrNome(),
                        'cargo' => $assinaturaDTO->getStrTratamento(),
                        'idUsuario' => $assinaturaDTO->getNumIdUsuario(),
                    );
                }
                $result['dados'][] = array(
                    'sequencia' => $relBlocoProtocoloDTO->getNumSequencia(),
                    'id' => $relBlocoProtocoloDTO->getDblIdProtocolo(),
                    'aberto' => $relBlocoProtocoloDTO->getObjProtocoloDTO()->isRetStrSinAberto() ?
                            $relBlocoProtocoloDTO->getObjProtocoloDTO()->getStrSinAberto() : 'N',
                    'data' => $relBlocoProtocoloDTO->getObjProtocoloDTO()->getDtaGeracao(),
                    'idDocumento' => $relBlocoProtocoloDTO->getDblIdProtocolo(),
                    'idProcesso' => $relBlocoProtocoloDTO->getDblIdProcedimentoDocumento(),
                    'nomeTipoProcesso' => $relBlocoProtocoloDTO->getObjProtocoloDTO()->getStrNomeTipoProcedimentoDocumento(),
                    'protocoloFormatado' => $relBlocoProtocoloDTO->getObjProtocoloDTO()->getStrProtocoloFormatadoProcedimentoDocumento(),
                    'numeroDocumento' => $relBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo(),
                    'tipoDocumento' => $relBlocoProtocoloDTO->getObjProtocoloDTO()->getStrNomeSerieDocumento(),
                    'assinaturas' => $arrAssinaturas,
                    'anotacao' => $relBlocoProtocoloDTO->getStrAnotacao(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $relBlocoProtocoloDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que disponibiliza o bloco de assinatura para outra unidade
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function disponibilizarBlocoAssinaturaControlado(BlocoDTO $blocoDTO)
    {
        try{
            if(!$blocoDTO->getNumIdBloco()){
                throw new InfraException('Bloco n�o informado.');
            }
            $blocoRN = new BlocoRN();
            /** Chama o componente SEI para disponibilizar um bloco de assinatura */
            $blocoRN->disponibilizar(array($blocoDTO));
            return MdWsSeiRest::formataRetornoSucessoREST('Bloco disponibilizado com sucesso.');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que cancela a disponibiliza��o do bloco de assinatura
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function cancelarDisponibilizacaoBlocoAssinaturaControlado(BlocoDTO $blocoDTO)
    {
        try{
            if(!$blocoDTO->getNumIdBloco()){
                throw new InfraException('Bloco n�o informado.');
            }
            $blocoRN = new BlocoRN();
            /** Chama o componente SEI para cancelar a disponibiliza��o de um bloco de assinatura */
            $blocoRN->cancelarDisponibilizacao(array($blocoDTO));
            return MdWsSeiRest::formataRetornoSucessoREST('Disponibiliza��o cancelada com sucesso.');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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

        return $this->salvarAnotacaoBloco($relBlocoProtocoloDTO);
    }

    /**
     * Salvar Anotacao documento do Bloco
     * @param RelBlocoProtocoloDTO $relBlocoProtocoloDTOParam
     * @return array
     */
    protected function salvarAnotacaoBlocoControlado(RelBlocoProtocoloDTO $relBlocoProtocoloDTOParam){

        try {
            if (!$relBlocoProtocoloDTOParam->isSetNumIdBloco()) {
                throw new InfraException('O bloco deve ser informado.');
            }
            if (!$relBlocoProtocoloDTOParam->isSetDblIdProtocolo()) {
                throw new InfraException('O protocolo deve ser informado.');
            }

            $blocoDTOConsulta = new BlocoDTO();
            $blocoDTOConsulta->retTodos();
            $blocoDTOConsulta->setNumIdBloco($relBlocoProtocoloDTOParam->getNumIdBloco());
            $blocoRN = new BlocoRN();
            /** Acessando o componente SEI para retorno de dados do bloco para valida��o de permiss�o de acesso **/
            $blocoDTOConsulta = $blocoRN->consultarRN1276($blocoDTOConsulta);
            if(!$blocoDTOConsulta){
                throw new Exception('Bloco n�o encontrado.');
            }

            $relBlocoUnidadeDTO = new RelBlocoUnidadeDTO();
            $relBlocoUnidadeDTO->retNumIdBloco();
            $relBlocoUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $relBlocoUnidadeDTO->setNumIdBloco($blocoDTOConsulta->getNumIdBloco());
            $relBlocoUnidadeDTO->setStrSinRetornado('N');

            $relBlocoUnidadeRN = new RelBlocoUnidadeRN();

            /** Acessando o componente SEI para verifica��o de disponibiliza��o de bloco */
            $relBlocoUnidadeDTO = $relBlocoUnidadeRN->consultarRN1303($relBlocoUnidadeDTO);

            if($blocoDTOConsulta->getNumIdUnidade() != SessaoSEI::getInstance()->getNumIdUnidadeAtual() && !$relBlocoUnidadeDTO){
                throw new Exception('Bloco n�o encontrado.');
            }

            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
            $relBlocoProtocoloDTO->setNumIdBloco($relBlocoProtocoloDTOParam->getNumIdBloco());
            $relBlocoProtocoloDTO->setDblIdProtocolo($relBlocoProtocoloDTOParam->getDblIdProtocolo());
            $relBlocoProtocoloDTO->retTodos();

            /** Acessando o componente SEI para consulta de Documento no Bloco */
            $relBlocoProtocoloDTO = $relBlocoProtocoloRN->consultarRN1290($relBlocoProtocoloDTO);
            if (!$relBlocoProtocoloDTO) {
                throw new InfraException('Documento n�o encontrado no bloco informado.');
            }
            $relBlocoProtocoloDTO->setStrAnotacao($relBlocoProtocoloDTOParam->getStrAnotacao());
            /** Chamando o componente SEI para salvar a anota��o */
            $relBlocoProtocoloRN->alterarRN1288($relBlocoProtocoloDTO);

            return MdWsSeiRest::formataRetornoSucessoREST('Opera��o realizada com sucesso.');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que cadastra um bloco de assinatura
     * @param \Slim\Http\Request $request
     * @return array
     */
    public function cadastrarBlocoAssinaturaRequest(\Slim\Http\Request $request)
    {
        try{
            $result = array();
            if(!$request->getParam('descricao')){
                throw new Exception('Descri��o n�o informada.');
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
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que cadastra um bloco interno
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function cadastrarBlocoInternoControlado(BlocoDTO $blocoDTO)
    {
        try{
            $result = array();
            if(!$blocoDTO->isSetStrDescricao()){
                throw new Exception('Descri��o n�o informada.');
            }
            $blocoDTO->setStrStaTipo(BlocoRN::$TB_INTERNO);
            $blocoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $blocoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $blocoDTO->setStrIdxBloco(null);
            $blocoDTO->setStrStaEstado(BlocoRN::$TE_ABERTO);
            $blocoDTO->setArrObjRelBlocoUnidadeDTO(array());
            $blocoRN = new BlocoRN();
            /** Acessa o componente SEI para cadastro de Bloco Interno */
            $blocoRN->cadastrarRN1273($blocoDTO);

            $result = array(
                'id' => $blocoDTO->getNumIdBloco(),
                'descricao' => $blocoDTO->getStrDescricao(),
            );

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco Interno cadastrado com sucesso.', $result);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo generico para excluir blocos
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    public function excluirBlocos(array $arrIdBlocos)
    {
        try{
            if(empty($arrIdBlocos)){
                throw new Exception('Bloco n�o informado.');
            }
            $blocoRN = new BlocoRN();
            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();

            $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
            $relBlocoProtocoloDTO->setNumMaxRegistrosRetorno(1);
            $relBlocoProtocoloDTO->setNumIdBloco($arrIdBlocos, InfraDTO::$OPER_IN);
            $relBlocoProtocoloDTO->retDblIdProtocolo();

            /** Consultando componente SEI para verificar existencia de documentos/processos dentro do bloco */
            if($relBlocoProtocoloRN->listarRN1291($relBlocoProtocoloDTO)){
                throw new Exception('N�o � permitido excluir um bloco com processos/documentos dentro.');
            }

            $arrBlocosExclusao = array();
            foreach($arrIdBlocos as $idBloco) {
                $blocoDTO = new BlocoDTO();
                $blocoDTO->setNumIdBloco($idBloco);
                $arrBlocosExclusao[] = $blocoDTO;
            }
            /** Chama o componente SEI para exclus�o de blocos */
            $blocoRN->excluirRN1275($arrBlocosExclusao);

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco(s) exclu�do(s) com sucesso.', null);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo generico para concluir blocos
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    public function concluirBlocos(array $arrIdBlocos)
    {
        try{
            if(empty($arrIdBlocos)){
                throw new Exception('Bloco n�o informado.');
            }
            $blocoRN = new BlocoRN();
            $arrBlocosExclusao = array();
            foreach($arrIdBlocos as $idBloco) {
                $blocoDTO = new BlocoDTO();
                $blocoDTO->setNumIdBloco($idBloco);
                $arrBlocosExclusao[] = $blocoDTO;
            }
            /** Chama o componente SEI para conclus�o de blocos */
            $blocoRN->concluir($arrBlocosExclusao);

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco(s) conclu�do(s) com sucesso.', null);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo generico reabrir bloco
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function reabrirBlocoControlado(BlocoDTO $blocoDTO)
    {
        try{
            if(!$blocoDTO->getNumIdBloco()){
                throw new Exception('Bloco n�o informado.');
            }
            $blocoRN = new BlocoRN();
            $blocoDTO->retNumIdBloco();
            $blocoDTO->retStrStaEstado();
            $blocoDTO->retStrDescricao();
            /** Chama o componente SEI para consultar o Bloco e validar existencia */
            $blocoDTO = $blocoRN->consultarRN1276($blocoDTO);
            if(!$blocoDTO){
                throw new Exception('Bloco n�o encontrado.');
            }
            /** Chama o componente SEI para reabrir o bloco */
            $blocoRN->reabrir($blocoDTO);

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco reaberto com sucesso.', null);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que altera um bloco de assinatura
     * @param \Slim\Http\Request $request
     * @return array
     */
    public function alterarBlocoAssinaturaRequest(\Slim\Http\Request $request)
    {
        try{
            $result = array();
            if(!$request->getParam('descricao')){
                throw new Exception('Descri��o n�o informada.');
            }
            if(!$request->getAttribute('route')->getArgument('bloco')){
                throw new Exception('Bloco n�o informado.');
            }
            $blocoDTO = new BlocoDTO();
            $blocoDTO->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
            $blocoDTO->retTodos();
            $blocoRN = new BlocoRN();
            /** Chama o componente SEI para valida��o do tipo de bloco de assinatura */
            $blocoDTO = $blocoRN->consultarRN1276($blocoDTO);
            if(!$blocoDTO){
                throw new Exception('Bloco n�o encontrado.');
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
            /** Acessa o componente SEI para altera��o de Bloco de assinatura */
            $blocoRN->alterarRN1274($blocoDTO);

            $result = array(
                'id' => $blocoDTO->getNumIdBloco(),
                'descricao' => $blocoDTO->getStrDescricao(),
                'unidades' => $arrUnidades,
            );

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco de assinatura alterado com sucesso.', $result);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Inclui os Documentos selecionados no bloco
     * @param $idBloco
     * @param $arrIdDocumentos
     * @return array
     */
    public function apiIncluirDocumentosBlocoAssinatura($idBloco, $arrIdDocumentos)
    {
        try{
            if(!$arrIdDocumentos){
                return MdWsSeiRest::formataRetornoSucessoREST('Nenhum documento foi informado.');
            }
            $blocoDTO = new BlocoDTO();
            $blocoDTO->retNumIdBloco();
            $blocoDTO->setNumIdBloco($idBloco);
            $blocoDTO->setStrStaTipo(BlocoRN::$TB_ASSINATURA);
            $blocoRN = new BlocoRN();
            /** Chamando componente SEI para verifica��o de existencia de bloco **/
            $blocoDTO = $blocoRN->consultarRN1276($blocoDTO);
            if(!$blocoDTO){
                throw new InfraException('Bloco n�o encontrado.');
            }
            $arrObjRelBlocoProtocoloDTO = array();
            foreach($arrIdDocumentos as $idProtocolo) {
                $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
                $relBlocoProtocoloDTO->setDblIdProtocolo($idProtocolo);
                $relBlocoProtocoloDTO->setNumIdBloco($idBloco);
                $relBlocoProtocoloDTO->setStrAnotacao(null);
                $arrObjRelBlocoProtocoloDTO[] = $relBlocoProtocoloDTO;
            }
            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            /** Chama o componente SEI para inclus�o dos documentos no bloco */
            $relBlocoProtocoloRN->cadastrarMultiplo($arrObjRelBlocoProtocoloDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Documento(s) inclu�do(s) com sucesso.');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que altera um bloco interno
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    public function alterarBlocoInternoControlado(BlocoDTO $blocoDTO)
    {
        try{
            $result = array();
            if(!$blocoDTO->getNumIdBloco()){
                throw new Exception('Bloco n�o informado.');
            }
            $blocoDTOConsulta = new BlocoDTO();
            $blocoDTOConsulta->retTodos();
            $blocoDTOConsulta->setNumIdBloco($blocoDTO->getNumIdBloco());
            $blocoRN = new BlocoRN();
            $blocoDTOConsulta = $blocoRN->consultarRN1276($blocoDTOConsulta);
            if(!$blocoDTOConsulta || $blocoDTOConsulta->getNumIdUnidade() != SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
                throw new Exception('Bloco n�o encontrado.');
            }
            if($blocoDTOConsulta->getStrStaTipo() != BlocoRN::$TB_INTERNO){
                throw new Exception('Bloco diferente do informado.');
            }

            $blocoDTOConsulta->setStrDescricao($blocoDTO->getStrDescricao());
            $blocoDTOConsulta->setArrObjRelBlocoUnidadeDTO(array());
            /** Acessa o componente SEI para altera��o de Bloco interno */
            $blocoRN->alterarRN1274($blocoDTOConsulta);

            $result = array(
                'id' => $blocoDTO->getNumIdBloco(),
                'descricao' => $blocoDTO->getStrDescricao(),
            );

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco de interno alterado com sucesso.', $result);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }


    /**
     * Consultar Processos de um Bloco de Interno
     * @param RelBlocoProtocoloDTO $relBlocoProtocoloDTOConsulta
     * @return array
     */
    protected function listarProcessosBlocoInternoConectado(RelBlocoProtocoloDTO $relBlocoProtocoloDTOConsulta){
        try{
            if(!$relBlocoProtocoloDTOConsulta->getNumIdBloco()){
                throw new InfraException('Bloco n�o informado.');
            }
            $blocoDTO = new BlocoDTO();
            $blocoDTO->retStrStaTipo();
            $blocoDTO->retStrStaEstado();
            $blocoDTO->retStrTipoDescricao();
            $blocoDTO->retNumIdUnidade();
            $blocoDTO->setNumIdBloco($relBlocoProtocoloDTOConsulta->getNumIdBloco());
            $blocoDTO->setStrStaTipo(BlocoRN::$TB_INTERNO);

            $blocoRN = new BlocoRN();
            $blocoDTO = $blocoRN->consultarRN1276($blocoDTO);
            if(!$blocoDTO){
                throw new InfraException('Bloco n�o encontrado.');
            }
            $result = array();
            $arrAtributos = array(
                'retirar' => (
                    SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_excluir') &&
                    $blocoDTO->getStrStaEstado() != BlocoRN::$TE_DISPONIBILIZADO &&
                    $blocoDTO->getNumIdUnidade() == SessaoSEI::getInstance()->getNumIdUnidadeAtual()
                ),
                'anotar' => (
                    SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_alterar')
                )
            );

            $result['permissoes'] = $arrAtributos;

            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            $relBlocoProtocoloDTOConsulta->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);
            $relBlocoProtocoloDTOConsulta->retDblIdProtocolo();
            $relBlocoProtocoloDTOConsulta->retNumIdBloco();
            $relBlocoProtocoloDTOConsulta->retNumSequencia();
            $relBlocoProtocoloDTOConsulta->retNumIdUnidadeBloco();
            $relBlocoProtocoloDTOConsulta->retStrProtocoloFormatadoProtocolo();
            $relBlocoProtocoloDTOConsulta->retStrStaProtocoloProtocolo();
            $relBlocoProtocoloDTOConsulta->retStrAnotacao();
            $relBlocoProtocoloDTOConsulta->retObjProtocoloDTO();
            /** Acessa o componente SEI para consulta dos processos de um bloco */
            $ret = $relBlocoProtocoloRN->listarProtocolosBloco($relBlocoProtocoloDTOConsulta);
            /** @var RelBlocoProtocoloDTO $relBlocoProtocoloDTO */
            foreach($ret as $relBlocoProtocoloDTO){
                $result['dados'][] = array(
                    'sequencia' => $relBlocoProtocoloDTO->getNumSequencia(),
                    'id' => $relBlocoProtocoloDTO->getDblIdProtocolo(),
                    'data' => $relBlocoProtocoloDTO->getObjProtocoloDTO()->getDtaGeracao(),
                    'nomeTipoProcesso' => $relBlocoProtocoloDTO->getObjProtocoloDTO()->getStrNomeTipoProcedimentoProcedimento(),
                    'protocoloFormatado' => $relBlocoProtocoloDTO->getStrProtocoloFormatadoProtocolo(),
                    'anotacao' => $relBlocoProtocoloDTO->getStrAnotacao(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $relBlocoProtocoloDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Pesquisa blocos de interno
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function pesquisarBlocoInternoConectado(BlocoDTO $blocoDTOConsulta){
        try{
            $result = array();
            $blocoRN = new BlocoRN();
            $blocoDTOConsulta->setStrStaTipo(BlocoRN::$TB_INTERNO);
            $blocoDTOConsulta->retNumIdBloco();
            $blocoDTOConsulta->retNumIdUnidade();
            $blocoDTOConsulta->retStrDescricao();
            $blocoDTOConsulta->retStrStaTipo();
            $blocoDTOConsulta->retStrStaEstado();
            $blocoDTOConsulta->retStrStaEstadoDescricao();
            $blocoDTOConsulta->retStrTipoDescricao();
            $blocoDTOConsulta->retStrSiglaUnidade();
            $blocoDTOConsulta->retStrDescricaoUnidade();
            $blocoDTOConsulta->retArrObjRelBlocoUnidadeDTO();
            $blocoDTOConsulta->setOrdNumIdBloco(InfraDTO::$TIPO_ORDENACAO_DESC);

            /** Acessa o componente SEI para realizar a pesquisa de blocos internos */
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
                /** Acessa o componente SEI para consultar o total de processos dentro de um bloco interno */
                $relBlocoProtocoloRN->listarRN1291($relBlocoProtocoloDTOConsulta);
                $numeroProcessos = $relBlocoProtocoloDTOConsulta->getNumTotalRegistros();
                $result[] = array(
                    'id' => $blocoDTO->getNumIdBloco(),
                    'idUnidade' => $blocoDTO->getNumIdUnidade(),
                    'siglaUnidade' => $blocoDTO->getStrSiglaUnidade(),
                    'estado' => $blocoDTO->getStrStaEstado(),
                    'descricao' => $blocoDTO->getStrDescricao(),
                    'numeroProcessos' => $numeroProcessos
                );
            }
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $blocoDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }


    /**
     * Inclui os Processos selecionados no bloco
     * @param $idBloco
     * @param $arrIdProtocolos
     * @return array
     */
    public function apiIncluirProcessosBlocoInterno($idBloco, $arrIdProtocolos)
    {
        try{
            if(!$arrIdProtocolos){
                return MdWsSeiRest::formataRetornoSucessoREST('Nenhum processo foi informado.');
            }
            $blocoDTO = new BlocoDTO();
            $blocoDTO->retNumIdBloco();
            $blocoDTO->setNumIdBloco($idBloco);
            $blocoDTO->setStrStaTipo(BlocoRN::$TB_INTERNO);
            $blocoRN = new BlocoRN();
            /** Chamando componente SEI para verifica��o de existencia de bloco **/
            $blocoDTO = $blocoRN->consultarRN1276($blocoDTO);
            if(!$blocoDTO){
                throw new InfraException('Bloco n�o encontrado.');
            }
            $arrObjRelBlocoProtocoloDTO = array();
            foreach($arrIdProtocolos as $idProtocolo) {
                $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
                $relBlocoProtocoloDTO->setDblIdProtocolo($idProtocolo);
                $relBlocoProtocoloDTO->setNumIdBloco($idBloco);
                $relBlocoProtocoloDTO->setStrAnotacao(null);
                $arrObjRelBlocoProtocoloDTO[] = $relBlocoProtocoloDTO;
            }
            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            /** Chama o componente SEI para inclus�o dos processos no bloco */
            $relBlocoProtocoloRN->cadastrarMultiplo($arrObjRelBlocoProtocoloDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Processo(s) inclu�do(s) com sucesso.');
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}