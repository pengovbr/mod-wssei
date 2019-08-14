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
    protected function retornarBlocoControlado(BlocoDTO $blocoDTO){
        try{
            if(!$blocoDTO->getNumIdBloco()){
                throw new Exception('Bloco não informado!');
            }
            $blocoRN = new BlocoRN();
            /** Chamada ao componente SEI para retorno de bloco de assinatura */
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
            $assinaturaDTO->setNumIdContextoUsuario(null);
            $assinaturaDTO->setArrObjDocumentoDTO(InfraArray::gerarArrInfraDTO('DocumentoDTO','IdDocumento',$arrIdDocumentos));
            $documentoRN = new DocumentoRN();
            /** Chama o componente SEI para assinar os documentos */
            $documentoRN->assinarInterno($assinaturaDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Bloco assinado com sucesso.');
        }catch (Exception $e){
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
            $assinaturaDTO->setNumIdContextoUsuario(null);
            $assinaturaDTO->setArrObjDocumentoDTO(InfraArray::gerarArrInfraDTO('DocumentoDTO','IdDocumento',$arrIdDocumentos));
            $documentoRN = new DocumentoRN();
            /** Chama o componente SEI para realizar a assinatura dos documentos */
            $documentoRN->assinarInterno($assinaturaDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Documento(s) assinado(s) com sucesso.');
        }catch (Exception $e){
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
            /** Chama o componente SEI para exclusão dos documentos do bloco */
            $relBlocoProtocoloRN->excluirRN1289($arrObjRelBlocoProtocoloDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Documento(s) removido(s) com sucesso.');
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
     * Consultar Documentos de um Bloco de Assinatura
     * @param RelBlocoProtocoloDTO $relBlocoProtocoloDTOConsulta
     * @return array
     */
    protected function listarDocumentosBlocoAssinaturaConectado(RelBlocoProtocoloDTO $relBlocoProtocoloDTOConsulta){
        try{
            if(!$relBlocoProtocoloDTOConsulta->getNumIdBloco()){
                throw new InfraException('Bloco não informado.');
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
                throw new InfraException('Bloco não encontrado.');
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
                    'aberto' => $relBlocoProtocoloDTO->getObjProtocoloDTO()->getStrSinAberto(),
                    'data' => $relBlocoProtocoloDTO->getObjProtocoloDTO()->getDtaGeracao(),
                    'idDocumento' => $relBlocoProtocoloDTO->getDblIdProtocolo(),
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
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que disponibiliza o bloco de assinatura para outra unidade
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function disponibilizarBlocoAssinaturaControlado(BlocoDTO $blocoDTO)
    {
        try{
            if(!$blocoDTO->getNumIdBloco()){
                throw new InfraException('Bloco não informado.');
            }
            $blocoRN = new BlocoRN();
            /** Chama o componente SEI para disponibilizar um bloco de assinatura */
            $blocoRN->disponibilizar(array($blocoDTO));
            return MdWsSeiRest::formataRetornoSucessoREST('Bloco disponibilizado com sucesso.');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que cancela a disponibilização do bloco de assinatura
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function cancelarDisponibilizacaoBlocoAssinaturaControlado(BlocoDTO $blocoDTO)
    {
        try{
            if(!$blocoDTO->getNumIdBloco()){
                throw new InfraException('Bloco não informado.');
            }
            $blocoRN = new BlocoRN();
            /** Chama o componente SEI para cancelar a disponibilização de um bloco de assinatura */
            $blocoRN->cancelarDisponibilizacao(array($blocoDTO));
            return MdWsSeiRest::formataRetornoSucessoREST('Disponibilização cancelada com sucesso.');
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
            if (!$relBlocoProtocoloDTOParam->isSetStrAnotacao()) {
                throw new InfraException('A anotação deve ser informada.');
            }
            $relBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
            $relBlocoProtocoloDTO->setNumIdBloco($relBlocoProtocoloDTOParam->getNumIdBloco());
            $relBlocoProtocoloDTO->setDblIdProtocolo($relBlocoProtocoloDTOParam->getDblIdProtocolo());
            $relBlocoProtocoloDTO->retTodos();
            $relBlocoProtocoloRN = new RelBlocoProtocoloRN();
            /** Acessando o componente SEI para consulta de Documento no Bloco */
            $relBlocoProtocoloDTO = $relBlocoProtocoloRN->consultarRN1290($relBlocoProtocoloDTO);
            if (!$relBlocoProtocoloDTO) {
                throw new InfraException('Documento não encontrado no bloco informado.');
            }
            $relBlocoProtocoloDTO->setStrAnotacao($relBlocoProtocoloDTOParam->getStrAnotacao());
            /** Chamando o componente SEI para salvar a anotação */
            $relBlocoProtocoloRN->alterarRN1288($relBlocoProtocoloDTO);

            return MdWsSeiRest::formataRetornoSucessoREST('Operação realizada com sucesso.');
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
     * Método que cadastra um bloco interno
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function cadastrarBlocoInternoControlado(BlocoDTO $blocoDTO)
    {
        try{
            $result = array();
            if(!$blocoDTO->isSetStrDescricao() || !trim($blocoDTO->getStrDescricao())){
                throw new Exception('Descrição não informada.');
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
     * Método generico para concluir blocos
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
     * Método generico reabrir bloco
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    protected function reabrirBlocoControlado(BlocoDTO $blocoDTO)
    {
        try{
            if(!$blocoDTO->getNumIdBloco()){
                throw new Exception('Bloco não informado.');
            }
            $blocoRN = new BlocoRN();
            $blocoDTO->retNumIdBloco();
            $blocoDTO->retStrStaEstado();
            $blocoDTO->retStrDescricao();
            /** Chama o componente SEI para consultar o Bloco e validar existencia */
            $blocoDTO = $blocoRN->consultarRN1276($blocoDTO);
            /** Chama o componente SEI para conclusão de blocos */
            if(!$blocoDTO){
                throw new Exception('Bloco não encontrado.');
            }
            $blocoRN->reabrir($blocoDTO);

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco de assinatura reaberto com sucesso.', null);
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

    /**
     * Método que altera um bloco interno
     * @param BlocoDTO $blocoDTO
     * @return array
     */
    public function alterarBlocoInternoControlado(BlocoDTO $blocoDTO)
    {
        try{
            $result = array();
            if(!trim($blocoDTO->getStrDescricao())){
                throw new Exception('Descrição não informada.');
            }
            if(!$blocoDTO->getNumIdBloco()){
                throw new Exception('Bloco não informado.');
            }
            $blocoDTOConsulta = new BlocoDTO();
            $blocoDTOConsulta->retTodos();
            $blocoDTOConsulta->setNumIdBloco($blocoDTO->getNumIdBloco());
            $blocoRN = new BlocoRN();
            $blocoDTOConsulta = $blocoRN->consultarRN1276($blocoDTOConsulta);
            if(!$blocoDTOConsulta){
                throw new Exception('Bloco não encontrado.');
            }
            if($blocoDTOConsulta->getStrStaTipo() != BlocoRN::$TB_INTERNO){
                throw new Exception('Bloco diferente do informado.');
            }

            $blocoDTOConsulta->setStrDescricao($blocoDTO->getStrDescricao());
            $blocoDTOConsulta->setArrObjRelBlocoUnidadeDTO(array());
            /** Acessa o componente SEI para alteração de Bloco interno */
            $blocoRN->alterarRN1274($blocoDTOConsulta);

            $result = array(
                'id' => $blocoDTO->getNumIdBloco(),
                'descricao' => $blocoDTO->getStrDescricao(),
            );

            return MdWsSeiRest::formataRetornoSucessoREST('Bloco de interno alterado com sucesso.', $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}