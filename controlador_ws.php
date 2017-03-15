<?
/**
 * Controlador (API v1) de servicos REST usando o framework Slim
 */

require_once dirname(__FILE__).'/../../SEI.php';
require_once dirname(__FILE__).'/vendor/autoload.php';

ini_set('xdebug.var_display_max_depth', 100);
ini_set('xdebug.var_display_max_children', 100);
ini_set('xdebug.var_display_max_data', 2048);
//echo '<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>';


function response_to_utf8($item){
    if(is_array($item)){
        $itemArr = $item;
    }else if(is_object($item)) {
        $itemArr = get_object_vars($item);
    }else if(is_bool($item)){
        return $item;
    }else{
        //return mb_convert_encoding($item, "ISO-8859-1", mb_detect_encoding($item, "UTF-8, ISO-8859-1, ISO-8859-15, ASCII", true));
        return utf8_encode(htmlspecialchars($item));
    }
    $response = array();
    foreach($itemArr as $key => $val){
        $response[$key] = response_to_utf8($val);
    }
    return $response;
}


class TokenValidationMiddleware{
    public function __invoke($request, $response, $next)
    {
        /** @var $request Slim\Http\Request */
        /** @var $response Slim\Http\Response */
        $token = $request->getHeader('token');

        if(!$token){
            return $response->withJson(response_to_utf8(array('sucesso' => false, 'mensagem' => 'Acesso negado!')), 401);
        }
        $rn = new MdWsSeiUsuarioRN();
        $result = $rn->autenticarToken($token[0]);
        if(!$result['sucesso']){
            return $response->withJson(response_to_utf8($result), 403);
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

        return $response->withJSON(response_to_utf8($rn->autenticar($usuarioDTO)));
    });

    /**
     * Grupo de controlador de anotacao
     */
    $this->group('/anotacao', function(){
        $this->post('/', function($request, $response, $args){
            $rn = new MdWsSeiAnotacaoRN();
            $dto = $rn->encapsulaAnotacao($request->getParams());
            return $response->withJSON(response_to_utf8($rn->cadastrarAnotacao($dto)));
        })->setName('v1_anotacao_cadastrar');

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
            return $response->withJSON(response_to_utf8($rn->listarBlocoUnidade($dto)));
        });
        $this->get('/listar/{bloco}/documentos', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            $dto = new BlocoDTO();
            $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
            $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            $dto->setNumPaginaAtual($request->getParam('start'));
            return $response->withJSON(response_to_utf8($rn->listarDocumentosBloco($dto)));
        });
        $this->post('/{bloco}/anotacao', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            $dto = new RelBlocoProtocoloDTO();
            $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
            $dto->setDblIdProtocolo($request->getParam('protocolo'));
            $dto->setStrAnotacao($request->getParam('anotacao'));
            return $response->withJSON(response_to_utf8($rn->cadastrarAnotacaoBloco($dto)));
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
            return $response->withJSON(response_to_utf8($rn->listarCienciaDocumento($dto)));
        });
        $this->get('/listar/assinaturas/{documento}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new DocumentoDTO();
            $dto->setDblIdDocumento($request->getAttribute('route')->getArgument('documento'));
            return $response->withJSON(response_to_utf8($rn->listarAssinaturasDocumento($dto)));
        });
        $this->post('/ciencia', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new DocumentoDTO();
            $dto->setDblIdDocumento($request->getParam('documento'));
            return $response->withJSON(response_to_utf8($rn->darCiencia($dto)));
        });
        $this->post('/assinar/bloco', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON(response_to_utf8($rn->apiAssinarDocumentos(
                $request->getParam('arrDocumento'),
                $request->getParam('documento'),
                $request->getParam('orgao'),
                $request->getParam('cargo'),
                $request->getParam('login'),
                $request->getParam('senha'),
                $request->getParam('usuario')
            )));
        });
        $this->post('/assinar', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            return $response->withJSON(response_to_utf8($rn->apiAssinarDocumento(
                $request->getParam('documento'),
                $request->getParam('documento'),
                $request->getParam('orgao'),
                $request->getParam('cargo'),
                $request->getParam('login'),
                $request->getParam('senha'),
                $request->getParam('usuario')
            )));
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
            return $response->withJSON(response_to_utf8($rn->removerSobrestamentoProcesso($dto)));
        });
        $this->get('/listar/ciencia/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiProcedimentoRN();
            $dto = new ProtocoloDTO();
            $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
            return $response->withJSON(response_to_utf8($rn->listarCienciaProcesso($dto)));
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
            return $response->withJSON(response_to_utf8($rn->sobrestamentoProcesso($dto)));
        });

    })->add( new TokenValidationMiddleware());
});
$app->run();
