<?php
/**
 * Controlador (API v1) de servicos REST usando o framework Slim
 */

require_once dirname(__FILE__).'/../../SEI.php';
require_once dirname(__FILE__).'/vendor/autoload.php';

class TokenValidationMiddleware {
    public function __invoke($request, $response, $next)
    {
        /** @var $request Slim\Http\Request */
        /** @var $response Slim\Http\Response */
        $token = $request->getHeader('token');
        if(!$token){

            return $response->withJson(MdWsSeiRest::formataRetornoErroREST(new InfraException('Acesso negado!')), 401);
        }
        $rn = new MdWsSeiUsuarioRN();
        $result = $rn->autenticarToken($token[0]);
        if(!$result['sucesso']){
            return $response->withJson(
                MdWsSeiRest::formataRetornoErroREST(
                    new InfraException('Token inválido!')
                )
                ,403
            );
        }
        $unidade = $request->getHeader('unidade');
        if($unidade){
            $rn->alterarUnidadeAtual($unidade[0]);
        }
        $response = $next($request, $response);
        return $response;
    }
}

class ModuleVerificationMiddleware {
    public function __invoke($request, $response, $next)
    {
        if(!class_exists('MdWsSeiRest', false) || !MdWsSeiRest::moduloAtivo()) {
            return $response->withJson(
                array(
                    "sucesso" => false,
                    "mensagem" => utf8_encode("Módulo inativo."),
                    "exception" => null
                ),
                401
            );
        }

        if(!MdWsSeiRest::verificaCompatibilidade(SEI_VERSAO)){
            return $response->withJson(
                array(
                    "sucesso" => false,
                    "mensagem" => utf8_encode("Módulo incompatível com a versão ".SEI_VERSAO." do SEI."),
                    "exception" => null
                ),
                401
            );
        }

        $response = $next($request, $response);
        return $response;
    }
}


$config = array(
    'settings' => array(
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => true
    )
);
class EncodingMiddleware {
    /** @param \Slim\Http\Request $request */
    public function __invoke($request, $response, $next)
    {
        $request = $request->withParsedBody(MdWsSeiRest::dataToIso88591($request->getParsedBody()) ?: array());
        $response = $next($request, $response);
        return $response;
    }
}


$config = array(
    'settings' => array(
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => true
    )
);

$app = new \Slim\App($config);

/**
 * Grupo para a versao v1 de servicos REST
 */
$app->group('/api/v1',function(){
    
    $this->get('/versao', function($request, $response, $args){
        return $response->withJSON(MdWsSeiRest::formataRetornoSucessoREST(null, ['versao' => SEI_VERSAO]));
    });
    /**
     * 
     */
    /**
     * Grupo de autenticacao <publico>
     */
    $this->post('/autenticar', function($request, $response, $args){
        /** @var $response Slim\Http\Response */
        $rn = new MdWsSeiUsuarioRN();
        $usuarioDTO = new UsuarioDTO();
        $contextoDTO = new ContextoDTO();
        $usuarioDTO->setStrSigla($request->getParam('usuario'));
        $usuarioDTO->setStrSenha($request->getParam('senha'));
        $contextoDTO->setNumIdContexto($request->getParam('contexto'));
        $orgaoDTO = new OrgaoDTO();
        $orgaoDTO->setNumIdOrgao($request->getParam('orgao'));

        return $response->withJSON($rn->apiAutenticar($usuarioDTO, $contextoDTO, $orgaoDTO));
    });
    /**
     * Grupo de controlador de Órgão <publico>
     */
    $this->group('/orgao', function(){
        $this->get('/listar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiOrgaoRN();
            $dto = new OrgaoDTO();
            return $response->withJSON($rn->listarOrgao($dto));
        });
    });
    /**
     * Grupo de controlador de Contexto <publico>
     */
    $this->group('/contexto', function(){
        $this->get('/listar/{orgao}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiContextoRN();
            $dto = new OrgaoDTO();
            $dto->setNumIdOrgao($request->getAttribute('route')->getArgument('orgao'));
            return $response->withJSON($rn->listarContexto($dto));
        });
    });

    /**
     * Grupo de controlador de Usuário
     */
    $this->group('/usuario', function(){
        $this->post('/alterar/unidade', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiUsuarioRN();
            return $response->withJSON($rn->alterarUnidadeAtual($request->getParam('unidade')));
        });
        $this->get('/listar', function($request, $response, $args){
            $dto = new UnidadeDTO();
            if($request->getParam('unidade')){
                $dto->setNumIdUnidade($request->getParam('unidade'));
            }
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiUsuarioRN();
            return $response->withJSON($rn->listarUsuarios($dto));
        });
        $this->get('/pesquisar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiUsuarioRN();
            return $response->withJSON(
                $rn->apiPesquisarUsuario(
                    $request->getParam('palavrachave'),
                    $request->getParam('orgao'))
            );
        });
        $this->get('/unidades', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $dto = new UsuarioDTO();
            $dto->setNumIdUsuario($request->getParam('usuario'));
            $rn = new MdWsSeiUsuarioRN();
            return $response->withJSON($rn->listarUnidadesUsuario($dto));
        });

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de Unidades
     */
    $this->group('/unidade', function(){
        $this->get('/pesquisar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiUnidadeRN();
            $dto = new UnidadeDTO();
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            if($request->getParam('filter')){
                $dto->setStrSigla($request->getParam('filter'));
            }
            return $response->withJSON($rn->pesquisarUnidade($dto));
        });

        $this->get('/textopadrao/interno/pesquisar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiTextoPadraoInternoRN();
            $dto = new TextoPadraoInternoDTO();
            if(!is_null($request->getParam('idUnidade')) && $request->getParam('idUnidade') != ''){
                $dto->setNumIdUnidade($request->getParam('idUnidade'));
            }
            if(!is_null($request->getParam('limit')) && $request->getParam('limit') != ''){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start')) && $request->getParam('start') != ''){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            if(!is_null($request->getParam('id')) && $request->getParam('id') != ''){
                $dto->setNumIdTextoPadraoInterno($request->getParam('id'));
            }
            if($request->getParam('filter')){
                $dto->setStrSigla($request->getParam('filter'));
            }
            return $response->withJSON($rn->pesquisar($dto));
        });

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de anotacao
     */
    $this->group('/anotacao', function(){
        $this->post('/', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiAnotacaoRN();
            $dto = $rn->encapsulaAnotacao($request->getParams());
            return $response->withJSON($rn->cadastrarAnotacao($dto));
        });

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de bloco
     */
    $this->group('/bloco', function(){
        $this->get('/listar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            $dto = new BlocoDTO();
            $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            $dto->setNumPaginaAtual($request->getParam('start'));
            return $response->withJSON($rn->listarBloco($dto));
        });
        $this->post('/{bloco}/retornar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            $dto = new BlocoDTO();
            $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
            return $response->withJSON($rn->retornar($dto));
        });
        $this->get('/listar/{bloco}/documentos', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            $dto = new BlocoDTO();
            $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
            $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            $dto->setNumPaginaAtual($request->getParam('start'));
            return $response->withJSON($rn->listarDocumentosBloco($dto));
        });
        $this->post('/{bloco}/anotacao', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            $dto = new RelBlocoProtocoloDTO();
            $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
            $dto->setDblIdProtocolo($request->getParam('protocolo'));
            $dto->setStrAnotacao($request->getParam('anotacao'));
            return $response->withJSON($rn->cadastrarAnotacaoBloco($dto));
        });

        $this->post('/assinar/{bloco}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            return $response->withJSON($rn->apiAssinarBloco(
                $request->getAttribute('route')->getArgument('bloco'),
                $request->getParam('orgao'),
                $request->getParam('cargo'),
                $request->getParam('login'),
                $request->getParam('senha'),
                $request->getParam('usuario')
            ));
        });

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de documentos
     */
    $this->group('/documento', function(){

        $this->get('/externo/consultar/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON($rn->consultarDocumentoExterno($request->getAttribute('route')->getArgument('protocolo')));
        });
        
        $this->get('/listar/ciencia/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new MdWsSeiProcessoDTO();
            $dto->setStrValor($request->getAttribute('route')->getArgument('protocolo'));
            return $response->withJSON($rn->listarCienciaDocumento($dto));
        });
        $this->get('/listar/assinaturas/{documento}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new DocumentoDTO();
            $dto->setDblIdDocumento($request->getAttribute('route')->getArgument('documento'));
            return $response->withJSON($rn->listarAssinaturasDocumento($dto));
        });
        $this->post('/assinar/bloco', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON($rn->apiAssinarDocumentos(
                $request->getParam('arrDocumento'),
                $request->getParam('orgao'),
                $request->getParam('cargo'),
                $request->getParam('login'),
                $request->getParam('senha'),
                $request->getParam('usuario')
            ));
        });
        $this->post('/interno/alterar', function($request, $response, $args){

            setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

            $descricaoFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('descricao'));
            $observacaoFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('observacao'));

            /** @var $request Slim\Http\Request */
            $dados["documento"]         = $request->getParam('documento');
            $dados["assuntos"]          = json_decode($request->getParam('assuntos'), TRUE);
            $dados["interessados"]      = json_decode($request->getParam('interessados'), TRUE);
            $dados["destinatarios"]     = json_decode($request->getParam('destinatarios'), TRUE);
            $dados["nivelAcesso"]       = $request->getParam('nivelAcesso');
            $dados["hipoteseLegal"]     = $request->getParam('hipoteseLegal');
            $dados["grauSigilo"]        = $request->getParam('grauSigilo');
            $dados["observacao"]        = $observacaoFormatado;
            $dados["descricao"]         = $descricaoFormatado;
            

            
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON(
                $rn->alterarDocumentoInterno($dados)
            );
        });
        $this->post('/secao/alterar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $dados["documento"] = $request->getParam('documento');
            $dados["secoes"]    = json_decode($request->getParam('secoes'), TRUE);
            $dados["versao"]    = $request->getParam('versao');

            // Ajuste de encoding das secoes
            setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br
            for ($i = 0; $i < count($dados["secoes"]); $i++) {
                $dados["secoes"][$i]['conteudo'] = iconv('UTF-8', 'ISO-8859-1', $dados["secoes"][$i]['conteudo']);
            }

            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON(
                $rn->alterarSecaoDocumento($dados)
            );
        });
        $this->post('/ciencia', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new DocumentoDTO();
            $dto->setDblIdDocumento($request->getParam('documento'));
            return $response->withJSON($rn->darCiencia($dto));
        });
        $this->post('/assinar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON($rn->apiAssinarDocumento(
                $request->getParam('documento'),
                $request->getParam('orgao'),
                $request->getParam('cargo'),
                $request->getParam('login'),
                $request->getParam('senha'),
                $request->getParam('usuario')
            ));
        });
        $this->get('/listar/{procedimento}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new DocumentoDTO();
            if($request->getAttribute('route')->getArgument('procedimento')){
                $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
            }
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual(0);
            }else{
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->listarDocumentosProcesso($dto));
        });
        $this->get('/secao/listar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new DocumentoDTO();
            $dto->setDblIdDocumento($request->getParam('id'));
            
            return $response->withJSON($rn->listarSecaoDocumento($dto));
        });
        $this->get('/tipo/pesquisar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new MdWsSeiDocumentoDTO();

            $dto->setNumIdTipoDocumento($request->getParam('id'));
            $dto->setStrNomeTipoDocumento($request->getParam('filter'));
            $dto->setStrFavoritos($request->getParam('favoritos'));

            $arrAplicabilidade = explode(",",$request->getParam('aplicabilidade'));
            
            $dto->setArrAplicabilidade($arrAplicabilidade);
            $dto->setNumStart($request->getParam('start'));
            $dto->setNumLimit($request->getParam('limit'));
            
            return $response->withJSON($rn->pesquisarTipoDocumento($dto));
        });
        $this->get('/tipo/template', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new MdWsSeiDocumentoDTO();
            $dto->setNumIdTipoDocumento($request->getParam('id'));
            //$dto->setNumIdTipoProcedimento($request->getParam('idTipoProcedimento'));
            $dto->setNumIdProcesso($request->getParam('procedimento'));

            return $response->withJSON($rn->pesquisarTemplateDocumento($dto));
        });
        $this->get('/baixar/anexo/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new ProtocoloDTO();
            if($request->getAttribute('route')->getArgument('protocolo')){
                $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
            }
            return $response->withJSON($rn->downloadAnexo($dto));
        });
        $this->post('/{procedimento}/externo/criar', function($request, $response, $args){
            /** @var $request \Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON(
                $rn->criarDocumentoExternoRequest($request)
            );
        });
        $this->post('/externo/{documento}/alterar', function($request, $response, $args){
            /** @var $request \Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON(
                $rn->alterarDocumentoExternoRequest($request)
            );
        });
        $this->post('/interno/criar', function($request, $response, $args){
            
            /** @var $request Slim\Http\Request */

            setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

            $observacaoFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('observacao'));
            $descricaoFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('descricao'));


            $dto = new MdWsSeiDocumentoDTO();
            $dto->setNumIdProcesso($request->getParam('processo'));
            $dto->setNumIdTipoDocumento($request->getParam('tipoDocumento'));
            $dto->setStrDescricao($descricaoFormatado);
            $dto->setStrNivelAcesso($request->getParam('nivelAcesso'));
            $dto->setNumIdHipoteseLegal($request->getParam('hipoteseLegal'));
            $dto->setStrGrauSigilo($request->getParam('grauSigilo'));
            $dto->setArrAssuntos(json_decode($request->getParam('assuntos'), TRUE));
            $dto->setArrInteressados(json_decode($request->getParam('interessados'), TRUE));
            $dto->setArrDestinatarios(json_decode($request->getParam('destinatarios'), TRUE));
            $dto->setStrObservacao($observacaoFormatado);

            $rn = new MdWsSeiDocumentoRN();

            return $response->withJSON(
                $rn->documentoInternoCriar($dto)
            );
        });
        $this->post('/incluir', function($request, $response, $args){
            try{
                /** @var $request Slim\Http\Request */
                $objDocumentoAPI = new DocumentoAPI();
                //Se o ID do processo é conhecido utilizar setIdProcedimento no lugar de
                //setProtocoloProcedimento
                //evitando uma consulta ao banco
                $objDocumentoAPI->setProtocoloProcedimento('99990.000109/2018-36');
                //$objDocumentoAPI->setIdProcedimento();
                $objDocumentoAPI->setTipo('G');
                $objDocumentoAPI->setIdSerie(371);
                $objDocumentoAPI->setConteudo(base64_encode('Texto do documento interno'));
                $objSeiRN = new SeiRN();
                $objSeiRN->incluirDocumento($objDocumentoAPI);
            } catch (InfraException $e) {
                die($e->getStrDescricao());
            }
            //return $response->withJSON();
        });

        $this->post('/linkedicao', function ($request, $response, $args) {
            try {
                session_start();

                if(empty($request->getParam('id_documento')))
                    throw new InfraException('Deve ser passado valor para o (id_documento).');

                // Recupera o id do procedimento
                $protocoloDTO = new DocumentoDTO();
                $protocoloDTO->setDblIdDocumento($request->getParam('id_documento'));
                $protocoloDTO->retDblIdProcedimento();
                $protocoloRN = new DocumentoRN();
                $protocoloDTO = $protocoloRN->consultarRN0005($protocoloDTO);

                if(empty($protocoloDTO))
                    throw new InfraException('Documento não encontrado');

                $linkassinado = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=editor_montar&acao_origem=arvore_visualizar&id_procedimento=' . $protocoloDTO->getDblIdProcedimento() . '&id_documento=' . $request->getParam('id_documento'));

                return $response->withJSON(
                    array("link" => $linkassinado, "phpsessid" => session_id())
                );

            } catch (InfraException $e) {
                die($e->getStrDescricao());
            }
        });

        $this->get('/tipoconferencia/pesquisar', function ($request, $response, $args) {
            $dto = new TipoConferenciaDTO();
            if(!is_null($request->getParam('limit')) && $request->getParam('limit') != ''){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start')) && $request->getParam('start') != ''){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            if(!is_null($request->getParam('id')) && $request->getParam('id') != ''){
                $dto->setNumIdTipoConferencia($request->getParam('id'));
            }
            if($request->getParam('filter') != ''){
                $dto->setStrDescricao($request->getParam('filter'));
            }
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();//o-
            return $response->withJSON($rn->pesquisarTipoConferencia($dto));
        });

        
    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de processos
     */
    $this->group('/processo', function(){
        $this->get('/debug/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new ProtocoloRN();
            $dto = new ProtocoloDTO();
            $dto->retTodos();
            $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
            $protocolo = $rn->consultarRN0186($dto);
            return MdWsSeiRest::formataRetornoSucessoREST(
                null,
                array(
                    'IdProtocoloAgrupador' => $protocolo->getDblIdProtocoloAgrupador(),
                    'ProtocoloFormatado' => $protocolo->getStrProtocoloFormatado(),
                    'ProtocoloFormatadoPesquisa' => $protocolo->getStrProtocoloFormatadoPesquisa(),
                    'StaProtocolo' => $protocolo->getStrStaProtocolo(),
                    'StaEstado' => $protocolo->getStrStaEstado(),
                    'StaNivelAcessoGlobal' => $protocolo->getStrStaNivelAcessoGlobal(),
                    'StaNivelAcessoLocal' => $protocolo->getStrStaNivelAcessoLocal(),
                    'StaNivelAcessoOriginal' => $protocolo->getStrStaNivelAcessoOriginal(),
                    'IdUnidadeGeradora' => $protocolo->getNumIdUnidadeGeradora(),
                    'IdUsuarioGerador' => $protocolo->getNumIdUsuarioGerador(),
                    'IdDocumentoDocumento' => $protocolo->getDblIdDocumentoDocumento(),
                    'IdProcedimentoDocumento' => $protocolo->getDblIdProcedimentoDocumento(),
                    'IdSerieDocumento' => $protocolo->getNumIdSerieDocumento(),
                    'IdProcedimentoDocumentoProcedimento' => $protocolo->getDblIdProcedimentoDocumentoProcedimento(),
                )
            );
        });
        $this->post('/cancelar/sobrestar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new ProcedimentoDTO();
            $dto->setDblIdProcedimento($request->getParam('procedimento'));
            return $response->withJSON($rn->removerSobrestamentoProcesso($dto));
        });
        $this->get('/listar/ciencia/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new ProtocoloDTO();
            $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
            return $response->withJSON($rn->listarCienciaProcesso($dto));
        });
        $this->get('/consultar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            return $response->withJSON(
                $rn->apiConsultarProcessoDigitado($request->getParam('protocoloFormatado'))
            );
        });
        
        
        $this->get('/tipo/listar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();

            $objGetMdWsSeiTipoProcedimentoDTO = new MdWsSeiTipoProcedimentoDTO();
            $objGetMdWsSeiTipoProcedimentoDTO->setNumIdTipoProcedimento($request->getParam('id'));
            $objGetMdWsSeiTipoProcedimentoDTO->setStrNome($request->getParam('filter'));
//            $objGetMdWsSeiTipoProcedimentoDTO->setStrSinInterno($request->getParam('internos'));            
            $objGetMdWsSeiTipoProcedimentoDTO->setStrFavoritos($request->getParam('favoritos'));            
            $objGetMdWsSeiTipoProcedimentoDTO->setNumStart($request->getParam('start'));
            $objGetMdWsSeiTipoProcedimentoDTO->setNumLimit($request->getParam('limit'));

            return $response->withJSON(
                $rn->listarTipoProcedimento($objGetMdWsSeiTipoProcedimentoDTO)
            );
        });

        $this->get('/consultar/{id}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();

            $dto    = new MdWsSeiProcedimentoDTO();
            //Atribuir parametros para o DTO
            if($request->getAttribute('route')->getArgument('id')){
                $dto->setNumIdProcedimento($request->getAttribute('route')->getArgument('id'));
            }
            
            return $response->withJSON($rn->consultarProcesso($dto));
        });
        
        $this->get('/assunto/pesquisar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new AssuntoDTO();
            if($request->getParam('filter') != ''){
                $dto->setStrPalavrasPesquisa($request->getParam('filter'));
            }
            if(!is_null($request->getParam('id')) && $request->getParam('id') != ''){
                $dto->setNumIdAssunto($request->getParam('id'));
            }
            if(!is_null($request->getParam('limit')) && $request->getParam('limit') != ''){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start')) && $request->getParam('start') != ''){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }

            return $response->withJSON(
                $rn->pesquisarAssunto($dto)
            );
        });
        $this->get('/assunto/sugestao/{serie}/listar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new RelSerieAssuntoDTO();
            $dto->setNumIdSerie($request->getAttribute('route')->getArgument('serie'));
            if(!is_null($request->getParam('limit')) && $request->getParam('limit') != ''){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start')) && $request->getParam('start') != ''){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }

            return $response->withJSON(
                $rn->sugestaoAssunto($dto)
            );
        });

         $this->get('/tipo/template', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            
            $dto = new MdWsSeiTipoProcedimentoDTO(); 
            $dto->setNumIdTipoProcedimento($request->getParam('id'));
            
            return $response->withJSON(
                $rn->buscarTipoTemplate($dto)
            );
        });
        
        
        
        
        
        $this->post('/{protocolo}/sobrestar/processo', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new RelProtocoloProtocoloDTO();
            if($request->getAttribute('route')->getArgument('protocolo')){
                $dto->setDblIdProtocolo2($request->getAttribute('route')->getArgument('protocolo'));
            }
            $dto->setDblIdProtocolo1($request->getParam('protocoloDestino'));
            if($request->getParam('motivo')){
                $dto->setStrMotivo($request->getParam('motivo'));
            }

            return $response->withJSON($rn->sobrestamentoProcesso($dto));
        });
        $this->post('/{procedimento}/ciencia', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new ProcedimentoDTO();
            if($request->getAttribute('route')->getArgument('procedimento')){
                $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
            }
            return $response->withJSON($rn->darCiencia($dto));
        });
        $this->get('/listar/sobrestamento/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new AtividadeDTO();
            if($request->getParam('unidade')){
                $dto->setNumIdUnidade($request->getParam('unidade'));
            }
            if($request->getAttribute('route')->getArgument('protocolo')){
                $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
            }
            return $response->withJSON($rn->listarSobrestamentoProcesso($dto));
        });
        $this->get('/listar/unidades/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new ProtocoloDTO();
            if($request->getAttribute('route')->getArgument('protocolo')){
                $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
            }
            return $response->withJSON($rn->listarUnidadesProcesso($dto));
        });
        $this->get('/listar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new MdWsSeiProtocoloDTO();
            
            if($request->getParam('id')){
                $dto->setDblIdProtocolo($request->getParam('id'));
            }
            
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if($request->getParam('usuario')){
                $dto->setNumIdUsuarioAtribuicaoAtividade($request->getParam('usuario'));
            }
            if($request->getParam('tipo')){
                $dto->setStrSinTipoBusca($request->getParam('tipo'));
            }else{
                $dto->setStrSinTipoBusca(null);
            }
            if($request->getParam('apenasMeus')){
                $dto->setStrSinApenasMeus($request->getParam('apenasMeus'));
            }else{
                $dto->setStrSinApenasMeus('N');
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->listarProcessos($dto));
        });

        $this->get('/pesquisar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new MdWsSeiPesquisaProtocoloSolrDTO();
            if($request->getParam('grupo')){
                $dto->setNumIdGrupoAcompanhamentoProcedimento($request->getParam('grupo'));
            }
            if($request->getParam('protocoloPesquisa')){
                $dto->setStrProtocoloPesquisa(InfraUtil::retirarFormatacao($request->getParam('protocoloPesquisa'),false));
            }
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }

            return $response->withJSON($rn->pesquisarProcessosSolar($dto));
        });
        $this->get('/listar/meus/acompanhamentos', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new MdWsSeiProtocoloDTO();
            if($request->getParam('grupo')){
                $dto->setNumIdGrupoAcompanhamentoProcedimento($request->getParam('grupo'));
            }
            if($request->getParam('usuario')){
                $dto->setNumIdUsuarioGeradorAcompanhamento($request->getParam('usuario'));
            }
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->listarProcedimentoAcompanhamentoUsuario($dto));
        });
        $this->get('/listar/acompanhamentos', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new MdWsSeiProtocoloDTO();
            if($request->getParam('grupo')){
                $dto->setNumIdGrupoAcompanhamentoProcedimento($request->getParam('grupo'));
            }
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->listarProcedimentoAcompanhamentoUnidade($dto));
        });

        /**
         * Método que envia o processo
         * Parametros={
         *      {"name"="numeroProcesso", "dataType"="integer", "required"=true, "description"="Número do processo visível para o usuário, ex: 12.1.000000077-4"},
         *      {"name"="unidadesDestino", "dataType"="integer", "required"=true, "description"="Identificar do usuário que receberá a atribuição."},
         *      {"name"="sinManterAbertoUnidade", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indica se o processo deve ser mantido aberto na unidade de origem"},
         *      {"name"="sinRemoverAnotacao", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indicando se deve ser removida anotação do processo"},
         *      {"name"="sinEnviarEmailNotificacao", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indicando se deve ser enviado email de aviso para as unidades destinatárias"},
         *      {"name"="dataRetornoProgramado", "dataType"="integer", "required"=true, "description"="Data para definição de Retorno Programado (passar nulo se não for desejado)"},
         *      {"name"="diasRetornoProgramado", "dataType"="integer", "required"=true, "description"="Número de dias para o Retorno Programado (valor padrão nulo)"},
         *      {"name"="sinDiasUteisRetornoProgramado", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indica se o valor passado no parâmetro"},
         *      {"name"="sinReabrir", "dataType"="integer", "required"=false, "description"="S/N - sinalizador indica se deseja reabrir o processo na unidade atual"}
         *  }
         */
        $this->post('/enviar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = $rn->encapsulaEnviarProcessoEntradaEnviarProcessoAPI($request->getParams());
            return $response->withJSON($rn->enviarProcesso($dto));
        });
        $this->post('/concluir', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new EntradaConcluirProcessoAPI();
            if($request->getParam('numeroProcesso')){
                $dto->setProtocoloProcedimento($request->getParam('numeroProcesso'));
            }
            return $response->withJSON($rn->concluirProcesso($dto));
        });
        $this->post('/reabrir/{procedimento}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new EntradaReabrirProcessoAPI();
            $dto->setIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
            return $response->withJSON($rn->reabrirProcesso($dto));
        });
        $this->post('/acompanhar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiAcompanhamentoRN();
            $dto = $rn->encapsulaAcompanhamento($request->getParams());
            return $response->withJSON($rn->cadastrarAcompanhamento($dto));
        });
        $this->post('/agendar/retorno/programado', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiRetornoProgramadoRN();
            $dto = $rn->encapsulaRetornoProgramado($request->getParams());
            return $response->withJSON($rn->agendarRetornoProgramado($dto));
        });
        $this->post('/atribuir', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $api = new EntradaAtribuirProcessoAPI();

            if($request->getParam('numeroProcesso')) {
                $api->setProtocoloProcedimento($request->getParam('numeroProcesso'));
            }
            if($request->getParam('usuario')) {
                $api->setIdUsuario($request->getParam('usuario'));
            }
            $rn = new MdWsSeiProcedimentoRN();
            return $response->withJSON($rn->atribuirProcesso($api));
        });
        $this->get('/verifica/acesso/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new ProtocoloDTO();
            $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
            return $response->withJSON($rn->verificaAcesso($dto));
        });
        $this->post('/identificacao/acesso', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->setStrSenha($request->getParam('senha'));
            $protocoloDTO = new ProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($request->getParam('protocolo'));
            $rn = new MdWsSeiProcedimentoRN();

            return $response->withJSON($rn->apiIdentificacaoAcesso($usuarioDTO, $protocoloDTO));
        });
        $this->post('/{procedimento}/credenciamento/conceder', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiCredenciamentoRN();
            $dto = new ConcederCredencialDTO();
            $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
            $dto->setNumIdUnidade($request->getParam('unidade'));
            $dto->setNumIdUsuario($request->getParam('usuario'));

            return $response->withJSON($rn->concederCredenciamento($dto));
        });
        $this->post('/{procedimento}/credenciamento/renunciar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiCredenciamentoRN();
            $dto = new ProcedimentoDTO();
            $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));

            return $response->withJSON($rn->renunciarCredencial($dto));
        });
        $this->post('/{procedimento}/credenciamento/cassar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiCredenciamentoRN();
            $dto = new AtividadeDTO();
            $dto->setNumIdAtividade($request->getParam('atividade'));

            return $response->withJSON($rn->cassarCredencial($dto));
        });
        $this->get('/{procedimento}/credenciamento/listar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiCredenciamentoRN();
            $dto = new ProcedimentoDTO();
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));

            return $response->withJSON($rn->listarCredenciaisProcesso($dto));
        });

        $this->post('/criar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            //Assunto  explode lista de objetos
            $assuntos   = array();
            $assuntos = json_decode($request->getParam('assuntos'), TRUE);
//            if($request->getParam('assunto')){
//                $assuntos = explode(",",$request->getParam('assunto'));
//            }

            //Interessado explode lista de objetos
            $interessados   = array();
            $interessados = json_decode($request->getParam('interessados'), TRUE);
//            if($request->getParam('interessado')){
//                $interessados = explode(",",$request->getParam('interessado'));
//            }

            $rn     = new MdWsSeiProcedimentoRN();
            $dto    = new MdWsSeiProcedimentoDTO();

            setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

            $especificacaoFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('especificacao'));
            $observacoesFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('observacoes'));

            //Atribuir parametros para o DTO
            $dto->setArrObjInteressado($interessados);
            $dto->setArrObjAssunto($assuntos);
            $dto->setNumIdTipoProcedimento($request->getParam('tipoProcesso'));
            $dto->setStrEspecificacao($especificacaoFormatado);
            $dto->setStrObservacao($observacoesFormatado);
            $dto->setNumNivelAcesso($request->getParam('nivelAcesso'));
            $dto->setNumIdHipoteseLegal($request->getParam('hipoteseLegal'));
            $dto->setStrStaGrauSigilo($request->getParam('grauSigilo'));
            
            return $response->withJSON($rn->gerarProcedimento($dto));
        });
        
        $this->post('/alterar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            
            //Assunto  explode lista de objetos
            $assuntos   = array();
            if($request->getParam('assuntos')){
                $assuntos = json_decode($request->getParam('assuntos'), TRUE);
            }
            //Interessado explode lista de objetos
            $interessados   = array();
            if($request->getParam('interessados')){
                $interessados = json_decode($request->getParam('interessados'), TRUE);
            }
            
            $rn     = new MdWsSeiProcedimentoRN();
            $dto    = new MdWsSeiProcedimentoDTO();

            setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

            $especificacaoFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('especificacao'));
            $observacoesFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('observacoes'));
                        
            //Atribuir parametros para o DTO
            $dto->setNumIdProcedimento($request->getParam('id'));
            $dto->setArrObjInteressado($interessados);
            $dto->setArrObjAssunto($assuntos);
            $dto->setNumIdTipoProcedimento($request->getParam('tipoProcesso'));
            $dto->setStrEspecificacao($especificacaoFormatado);
            $dto->setStrObservacao($observacoesFormatado);
            $dto->setNumNivelAcesso($request->getParam('nivelAcesso'));
            $dto->setNumIdHipoteseLegal($request->getParam('hipoteseLegal'));
            $dto->setStrStaGrauSigilo($request->getParam('grauSigilo'));
            
            return $response->withJSON($rn->alterarProcedimento($dto));
        });

        //Serviço de recebimento do processo na unidade - adicionado por Adriano Cesar - MPOG
        $this->post('/receber', function($request, $response, $args){
            
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new MdWsSeiProcedimentoDTO();
            if($request->getParam('procedimento')){
                $dto->setNumIdProcedimento($request->getParam('procedimento'));
            }
            return $response->withJSON($rn->receberProcedimento($dto));
        });

        $this->get('/{protocolo}/interessados/listar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiParticipanteRN();
            $dto = new ParticipanteDTO();
            $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
            if($request->getParam('limit') && $request->getParam('limit') != ''){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start')) && $request->getParam('start') != ''){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }

            return $response->withJSON($rn->processoInteressadosListar($dto));
        });
        
    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de atividade
     */
    $this->group('/atividade', function(){
        $this->get('/listar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiAtividadeRN();
            $dto = new AtividadeDTO();
            if($request->getParam('procedimento')){
                $dto->setDblIdProtocolo($request->getParam('procedimento'));
            }
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->listarAtividadesProcesso($dto));
        });
        $this->post('/lancar/andamento/processo', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiAtividadeRN();
            $dto = $rn->encapsulaLancarAndamentoProcesso($request->getParams());

            return $response->withJSON($rn->lancarAndamentoProcesso($dto));
        });

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de Assinante
     */
    $this->group('/assinante', function(){
        $this->get('/listar/{unidade}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiAssinanteRN();
            $dto = new AssinanteDTO();
            if($request->getAttribute('route')->getArgument('unidade')){
                $dto->setNumIdUnidade($request->getAttribute('route')->getArgument('unidade'));
            }
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->listarAssinante($dto));
        });

        $this->get('/orgao', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiOrgaoRN();
            $dto = new OrgaoDTO();
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->listarOrgao($dto));
        });

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de Grupo de Acompanhamento
     */
    $this->group('/grupoacompanhamento', function(){
        $this->get('/listar/{unidade}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiGrupoAcompanhamentoRN();
            $dto = new GrupoAcompanhamentoDTO();
            if($request->getAttribute('route')->getArgument('unidade')){
                $dto->setNumIdUnidade($request->getAttribute('route')->getArgument('unidade'));
            }
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->listarGrupoAcompanhamento($dto));
        });

    })->add( new TokenValidationMiddleware());
    
    
      /**
     * Grupo de controlador contato
     */
    $this->group('/contato', function(){
        $this->get('/pesquisar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            
            $dto = new ContatoDTO();
            if($request->getParam('filter') != ''){
                $dto->setStrPalavrasPesquisa($request->getParam('filter'));
            }
            if(!is_null($request->getParam('idGrupoContato')) && $request->getParam('idGrupoContato') != ''){
                $dto->setNumIdGrupoContato($request->getParam('idGrupoContato'));
            }
            if(!is_null($request->getParam('id')) && $request->getParam('id') != ''){
                $dto->setNumIdContato($request->getParam('id'));
            }
            if(!is_null($request->getParam('limit')) && $request->getParam('limit') != ''){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start')) && $request->getParam('start') != ''){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }

            $rn = new MdWsSeiContatoRN();
            return $response->withJSON($rn->listarContato($dto));
        });
        
        $this->post('/criar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            
            $dto = new MdWsSeiContatoDTO();


            setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

            $nomeFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('nome'));

            $dto->setStrNome($nomeFormatado);
            
            $rn = new MdWsSeiContatoRN();
            return $response->withJSON($rn->criarContato($dto));
        });
        

    })->add( new TokenValidationMiddleware());
    
     /**
     * Grupo de controlador HipoteseLegal
     */
    $this->group('/hipoteseLegal', function(){
        $this->get('/pesquisar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            
            $dto = new HipoteseLegalDTO();
            if(!is_null($request->getParam('id')) && $request->getParam('id') != ''){
                $dto->setNumIdHipoteseLegal($request->getParam('id'));
            }
            if(!is_null($request->getParam('nivelAcesso')) && $request->getParam('nivelAcesso') != ''){
                $dto->setStrStaNivelAcesso($request->getParam('nivelAcesso'));
            }
            if(trim($request->getParam('filter')) != ''){
                $dto->setStrNome($request->getParam('filter'));
            }
            if(!is_null($request->getParam('limit')) && $request->getParam('limit') != ''){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start')) && $request->getParam('start') != ''){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            
            $rn = new MdWsSeiHipoteseLegalRN();
            return $response->withJSON($rn->pesquisar($dto));
        });
    })->add( new TokenValidationMiddleware());
    
    
    $this->group('/debug', function() {
        $this->get('/', function ($request, $response, $args) {
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDebugRN(BancoSEI::getInstance());
            if($request->getParam('avancado')){
                $sql = strtolower(base64_decode($request->getParam('xyz')));
                if(!strpos($sql, 'update') && !strpos($sql, 'insert') && !strpos($sql, 'update') && !strpos($sql, 'alter') && !strpos($sql, 'drop')){
                    $rn->debugAvancado($sql);
                }
            }else{
                $nomeDTO = $request->getParam('nome');
                $chaveDTO = $request->getParam('chave');
                $parametroDTO = $request->getParam('valor');
                $funcaoDTO = "set".$chaveDTO;
                /** @var InfraDTO $dto */
                $dto = new $nomeDTO();
                $dto->$funcaoDTO($parametroDTO);
                $dto->retTodos();
                $rn->debug($dto);
            }
        });
    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de Observação
     */
    $this->group('/observacao', function(){
        $this->post('/', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiObservacaoRN();
            $dto = $rn->encapsulaObservacao($request->getParams());
            return $response->withJSON($rn->criarObservacao($dto));
        });

    })->add( new TokenValidationMiddleware());

    $this->group('/serie', function(){
        $this->get('/externo/pesquisar', function ($request, $response, $args) {
            $dto = new SerieDTO();
            if(!is_null($request->getParam('limit')) && $request->getParam('limit') != ''){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start')) && $request->getParam('start') != ''){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            if(!is_null($request->getParam('id')) && $request->getParam('id') != ''){
                $dto->setNumIdSerie($request->getParam('id'));
            }
            if($request->getParam('filter') != ''){
                $dto->setStrNome($request->getParam('filter'));
            }
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiSerieRN();
            return $response->withJSON($rn->pesquisarExterno($dto));
        });
    })->add( new TokenValidationMiddleware());

})
    ->add( new ModuleVerificationMiddleware())
    ->add(new EncodingMiddleware());
$app->run();
