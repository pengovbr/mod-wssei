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

$app = new \Slim\App($config);

/**
 * Grupo para a versao v1 de servicos REST
 */
$app->group('/api/v1',function(){
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

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de anotacao
     */
    $this->group('/anotacao', function(){
        $this->post('/', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiAnotacaoRN();
            $dto = $rn->encapsulaAnotacao(MdWsSeiRest::dataToIso88591($request->getParams()));
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
            $dto->setStrAnotacao(MdWsSeiRest::dataToIso88591($request->getParam('anotacao')));
            return $response->withJSON($rn->cadastrarAnotacaoBloco($dto));
        });

        $this->post('/assinar/{bloco}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            return $response->withJSON($rn->apiAssinarBloco(
                $request->getAttribute('route')->getArgument('bloco'),
                $request->getParam('orgao'),
                MdWsSeiRest::dataToIso88591($request->getParam('cargo')),
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
                MdWsSeiRest::dataToIso88591($request->getParam('cargo')),
                $request->getParam('login'),
                $request->getParam('senha'),
                $request->getParam('usuario')
            ));
        });
        
         $this->post('/externo/alterar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $dados["documento"]         = $request->getParam('documento');
            $dados["numero"]            = $request->getParam('numero');
            $dados["data"]              = $request->getParam('data');
            $dados["assuntos"]          = json_decode($request->getParam('assuntos'), TRUE);
            $dados["interessados"]      = json_decode($request->getParam('interessados'), TRUE);
            $dados["destinatarios"]     = json_decode($request->getParam('destinatarios'), TRUE);
            $dados["remetentes"]        = json_decode($request->getParam('remetentes'), TRUE);
            $dados["nivelAcesso"]       = $request->getParam('nivelAcesso');
            $dados["hipoteseLegal"]     = $request->getParam('hipoteseLegal');
            $dados["grauSigilo"]        = $request->getParam('grauSigilo');
            $dados["observacao"]        = $request->getParam('observacao');
            

            
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON(
                $rn->alterarDocumentoExterno($dados)
            );
        });
        
           $this->post('/interno/alterar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $dados["documento"]         = $request->getParam('documento');
            $dados["assuntos"]          = json_decode($request->getParam('assuntos'), TRUE);
            $dados["interessados"]      = json_decode($request->getParam('interessados'), TRUE);
            $dados["destinatarios"]     = json_decode($request->getParam('destinatarios'), TRUE);
            $dados["nivelAcesso"]       = $request->getParam('nivelAcesso');
            $dados["hipoteseLegal"]     = $request->getParam('hipoteseLegal');
            $dados["grauSigilo"]        = $request->getParam('grauSigilo');
            $dados["observacao"]        = $request->getParam('observacao');
            

            
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
                MdWsSeiRest::dataToIso88591($request->getParam('cargo')),
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
            $dto->setStrAplicabilidade($request->getParam('aplicabilidade'));
            $dto->setNumStart($request->getParam('start'));
            $dto->setNumLimit($request->getParam('limit'));
            
            return $response->withJSON($rn->pesquisarTipoDocumento($dto));
        });
        
        
         $this->get('/tipo/template', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new MdWsSeiDocumentoDTO();
            $dto->setNumIdTipoDocumento($request->getParam('id'));
            
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
        
        
        $this->post('/interno/criar', function($request, $response, $args){
            
            /** @var $request Slim\Http\Request */
            $dto = new MdWsSeiDocumentoDTO();
            $dto->setNumIdProcesso($request->getParam('processo'));
            $dto->setNumIdTipoDocumento($request->getParam('tipoDocumento'));
            $dto->setStrDescricao($request->getParam('descricao'));
            $dto->setStrNivelAcesso($request->getParam('nivelAcesso'));
            $dto->setNumIdHipoteseLegal($request->getParam('hipoteseLegal'));
            $dto->setStrGrauSigilo($request->getParam('grauSigilo'));
            $dto->setArrAssuntos(json_decode($request->getParam('assuntos'), TRUE));
            $dto->setArrInteressados(json_decode($request->getParam('interessados'), TRUE));
            $dto->setArrDestinatarios(json_decode($request->getParam('destinatarios'), TRUE));
            $dto->setStrObservacao($request->getParam('observacao'));

            $rn = new MdWsSeiDocumentoRN();

            return $response->withJSON(
                $rn->documentoInternoCriar($dto)
            );
        }); 
        
        $this->post('/externo/criar', function($request, $response, $args){
            
            /** @var $request Slim\Http\Request */
            $dto = new MdWsSeiDocumentoDTO();
            $dto->setNumIdProcesso($request->getParam('processo'));
            $dto->setNumIdTipoDocumento($request->getParam('tipoDocumento'));
            $dto->setDtaDataGeracaoDocumento(InfraData::getStrDataAtual());
            $dto->setStrNumero($request->getParam('numero'));
            $dto->setStrDescricao($request->getParam('descricao'));
            $dto->setStrNomeArquivo($request->getParam('descricao'));
            $dto->setStrNivelAcesso($request->getParam('nivelAcesso'));
            $dto->setNumIdHipoteseLegal($request->getParam('hipoteseLegal'));
            $dto->setStrGrauSigilo($request->getParam('grauSigilo'));
            $dto->setArrAssuntos(json_decode($request->getParam('assuntos'), TRUE));
            $dto->setArrInteressados(json_decode($request->getParam('interessados'), TRUE));
            $dto->setArrDestinatarios(json_decode($request->getParam('destinatarios'), TRUE));
            $dto->setArrRemetentes(json_decode($request->getParam('remetentes'), TRUE));
            $dto->setStrConteudoDocumento($request->getParam('conteudoDocumento'));
            $dto->setStrObservacao($request->getParam('observacao'));

            
            $rn = new MdWsSeiDocumentoRN();

            return $response->withJSON(
                $rn->documentoExternoCriar($dto)
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
                $rn->apiConsultarProcessoDigitado(MdWsSeiRest::dataToIso88591($request->getParam('protocoloFormatado')))
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
            $objGetMdWsSeiAssuntoDTO = new MdWsSeiAssuntoDTO(); 
            $objGetMdWsSeiAssuntoDTO->setNumIdAssunto($request->getParam('id'));
            $objGetMdWsSeiAssuntoDTO->setStrFilter($request->getParam('filter'));
            $objGetMdWsSeiAssuntoDTO->setNumStart($request->getParam('start'));
            $objGetMdWsSeiAssuntoDTO->setNumLimit($request->getParam('limit'));
            
            return $response->withJSON(
                $rn->listarAssunto($objGetMdWsSeiAssuntoDTO)
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
                $dto->setStrMotivo(MdWsSeiRest::dataToIso88591($request->getParam('motivo')));
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
            $dto = $rn->encapsulaEnviarProcessoEntradaEnviarProcessoAPI(MdWsSeiRest::dataToIso88591($request->getParams()));
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
            $dto = $rn->encapsulaAcompanhamento(MdWsSeiRest::dataToIso88591($request->getParams()));
            return $response->withJSON($rn->cadastrarAcompanhamento($dto));
        });
        $this->post('/agendar/retorno/programado', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiRetornoProgramadoRN();
            $dto = $rn->encapsulaRetornoProgramado(MdWsSeiRest::dataToIso88591($request->getParams()));
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
   
            //Atribuir parametros para o DTO
            $dto->setArrObjInteressado($interessados);
            $dto->setArrObjAssunto($assuntos);
            $dto->setNumIdTipoProcedimento($request->getParam('idTipoProcedimento'));
            $dto->setStrEspecificacao($request->getParam('especificacao'));
            $dto->setStrObservacao($request->getParam('observacao'));
            $dto->setNumNivelAcesso($request->getParam('nivelAcesso'));
            $dto->setNumIdHipoteseLegal($request->getParam('idHipoteseLegal'));
            $dto->setStrStaGrauSigilo($request->getParam('grauSigilo'));
            
            return $response->withJSON($rn->gerarProcedimento($dto));
        });
        
        $this->post('/alterar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            
            //Assunto  explode lista de objetos
            $assuntos   = array();
            if($request->getParam('assunto')){
                $assuntos = json_decode($request->getParam('assuntos'), TRUE);
            }
            //Interessado explode lista de objetos
            $interessados   = array();
            if($request->getParam('interessado')){
                $interessados = json_decode($request->getParam('interessados'), TRUE);
            }
            
            $rn     = new MdWsSeiProcedimentoRN();
            $dto    = new MdWsSeiProcedimentoDTO();
   
            //Atribuir parametros para o DTO
            $dto->setNumIdProcedimento($request->getParam('id'));
            $dto->setArrObjInteressado($interessados);
            $dto->setArrObjAssunto($assuntos);
            $dto->setNumIdTipoProcedimento($request->getParam('idTipoProcedimento'));
            $dto->setStrEspecificacao($request->getParam('especificacao'));
            $dto->setStrObservacao($request->getParam('observacao'));
            $dto->setNumNivelAcesso($request->getParam('nivelAcesso'));
            $dto->setNumIdHipoteseLegal($request->getParam('idHipoteseLegal'));
            $dto->setStrStaGrauSigilo($request->getParam('grauSigilo'));
            
            return $response->withJSON($rn->alterarProcedimento($dto));
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
            $dto = $rn->encapsulaLancarAndamentoProcesso(MdWsSeiRest::dataToIso88591($request->getParams()));

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
            
            $dto = new MdWsSeiContatoDTO();
            $dto->setNumIdContato($request->getParam('id'));
            $dto->setStrFilter($request->getParam('filter'));
            $dto->setNumStart($request->getParam('start'));
            $dto->setNumLimit($request->getParam('limit'));
            
            $rn = new MdWsSeiContatoRN();
            return $response->withJSON($rn->listarContato($dto));
        });
        
        $this->post('/criar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            
            $dto = new MdWsSeiContatoDTO();
            $dto->setStrNome($request->getParam('nome'));
            
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
            
            $dto = new MdWsSeiHipoteseLegalDTO();
            $dto->setNumIdHipoteseLegal($request->getParam('id'));
            $dto->setNumNivelAcesso($request->getParam('nivelAcesso'));
            $dto->setStrFilter($request->getParam('filter'));
            $dto->setNumStart($request->getParam('start'));
            $dto->setNumLimit($request->getParam('limit'));
            
            $rn = new MdWsSeiHipoteseLegalRN();
            return $response->withJSON($rn->listarHipoteseLegal($dto));
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
            $dto = $rn->encapsulaObservacao(MdWsSeiRest::dataToIso88591($request->getParams()));
            return $response->withJSON($rn->criarObservacao($dto));
        });

    })->add( new TokenValidationMiddleware());
})->add( new ModuleVerificationMiddleware());
$app->run();
