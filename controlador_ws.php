<?
/**
 * Controlador (API v1) de servicos REST usando o framework Slim
 */

require_once dirname(__FILE__).'/../../SEI.php';
require_once dirname(__FILE__).'/vendor/autoload.php';

//ini_set('xdebug.var_display_max_depth', 100);
//ini_set('xdebug.var_display_max_children', 100);
//ini_set('xdebug.var_display_max_data', 2048);
//echo '<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>';


class TokenValidationMiddleware{
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
        $usuarioDTO->setStrSigla($request->getParam('usuario'));
        $usuarioDTO->setStrSenha($request->getParam('senha'));

        return $response->withJSON($rn->autenticar($usuarioDTO));
    });

    /**
     * Grupo de controlador de anotacao
     */
    $this->group('/anotacao', function(){
        $this->post('/', function($request, $response, $args){
            $rn = new MdWsSeiAnotacaoRN();
            $dto = $rn->encapsulaAnotacao($request->getParams());
            return $response->withJSON($rn->cadastrarAnotacao($dto));
        });

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de bloco
     */
    $this->group('/bloco', function(){
        $this->get('/listar/bloco/{unidade}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            $dto = new UnidadeDTO();
            $dto->setNumIdUnidade($request->getAttribute('route')->getArgument('unidade'));
            $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            $dto->setNumPaginaAtual($request->getParam('start'));
            return $response->withJSON($rn->listarBlocoUnidade($dto));
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
        $this->post('/ciencia', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new DocumentoDTO();
            $dto->setDblIdDocumento($request->getParam('documento'));
            return $response->withJSON($rn->darCiencia($dto));
        });
        $this->post('/assinar/bloco', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON($rn->apiAssinarDocumentos(
                $request->getParam('arrDocumento'),
                $request->getParam('documento'),
                $request->getParam('orgao'),
                $request->getParam('cargo'),
                $request->getParam('login'),
                $request->getParam('senha'),
                $request->getParam('usuario')
            ));
        });
        $this->post('/assinar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON($rn->apiAssinarDocumento(
                $request->getParam('documento'),
                $request->getParam('documento'),
                $request->getParam('orgao'),
                $request->getParam('cargo'),
                $request->getParam('login'),
                $request->getParam('senha'),
                $request->getParam('usuario')
            ));
        });

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de processos
     */
    $this->group('/processo', function(){
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
        $this->post('/sobrestar/processo', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new EntradaSobrestarProcessoAPI();
            if($request->getParam('protocoloFormatado')){
                $dto->setProtocoloProcedimento($request->getParam('protocoloFormatado'));
            }
            if($request->getParam('protocolo')){
                $dto->setIdProcedimento($request->getParam('protocolo'));
            }
            if($request->getParam('protocoloVinculado')){
                $dto->setIdProcedimentoVinculado($request->getParam('protocoloVinculado'));
            }
            if($request->getParam('protocoloFormatadoVinculado')){
                $dto->setProtocoloProcedimentoVinculado($request->getParam('protocoloFormatadoVinculado'));
            }
            $dto->setMotivo($request->getParam('motivo'));
            return $response->withJSON($rn->sobrestamentoProcesso($dto));
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
            if($request->getParam('unidade')){
                $dto->setNumIdUnidadeAtividade($request->getParam('unidade'));
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
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->listarProcessos($dto));
        });

    })->add( new TokenValidationMiddleware());
});
$app->run();
