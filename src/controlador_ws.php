<?php
/**
 * Controlador (API v1) de servicos REST usando o framework Slim
 */

require_once dirname(__FILE__).'/../../SEI.php';
require_once dirname(__FILE__).'/vendor/autoload.php';
require_once dirname(__FILE__) . '/versao/v2/MdWsSeiServicosV2.php';
require_once dirname(__FILE__) . '/versao/v3/MdWsSeiServicosV3.php';

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
        $request = $request->withQueryParams(MdWsSeiRest::dataToIso88591($request->getQueryParams()) ?: array());
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
MdWsSeiServicosV2::getInstance($app)->registrarServicos();
MdWsSeiServicosV3::getInstance($app)->registrarServicos();
$app->run();
