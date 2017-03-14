<?
/**
 * Controlador (API v1) de servicos REST usando o framework Slim
 */

require_once dirname(__FILE__).'/../../SEI.php';
require_once dirname(__FILE__).'/vendor/autoload.php';

class TokenValidationMiddleware{
    public function __invoke($request, $response, $next)
    {
        /** @var $request Slim\Http\Request */
        /** @var $response Slim\Http\Response */
        $token = $request->getHeader('token');

        if(!$token){
            return $response->withJson(array('sucesso' => false, 'mensagem' => 'Acesso negado!'), 401);
        }
        $rn = new MdWsSeiUsuarioRN();
        $result = $rn->autenticarToken($token[0]);
        if(!$result['sucesso']){
            var_dump($result);
            return $response->withJson($result, 403);
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
$app->group('/v1',function(){
    /**
     * Grupo de autenticacao <publico>
     */
    $this->post('/autenticar', function($request, $response, $args){
        var_dump(111);exit;
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
        })->setName('v1_anotacao_cadastrar');

    })->add( new TokenValidationMiddleware());

    /**
     * Grupo de controlador de bloco
     */
    $this->group('/bloco', function(){
        $this->get('/listar/bloco/unidade/{unidade}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            $dto = new UnidadeDTO();
            $dto->setNumIdUnidade($request->getAttribute('route')->getArgument('unidade'));
            $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            $dto->setNumPaginaAtual($request->getParam('start'));
            return $response->withJSON($rn->listarBlocoUnidade($dto));
        });
        $this->get('/listar/bloco/documentos/{bloco}', function($request, $response, $args){
            var_dump(111);exit;
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiBlocoRN();
            $dto = new BlocoDTO();
            $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
            $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            $dto->setNumPaginaAtual($request->getParam('start'));
            return $response->withJSON($rn->listarDocumentosBloco($dto));
        });

    })->add( new TokenValidationMiddleware());
});


$app->run();
$c = $app->getContainer();
$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        var_dump($exception);exit;
        return $c['response']->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};
