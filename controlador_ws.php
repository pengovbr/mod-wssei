<?php
/**
 * Controlador (API v1) de servicos REST usando o framework Slim
 */

require_once dirname(__FILE__).'/../../SEI.php';
require_once dirname(__FILE__).'/vendor/autoload.php';
require_once dirname(__FILE__) . '/versao/v2/MdWsSeiServicosV2.php';

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
                    new InfraException('Token inv�lido!')
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
                    "mensagem" => utf8_encode("M�dulo inativo."),
                    "exception" => null
                ),
                401
            );
        }

        if(!MdWsSeiRest::verificaCompatibilidade(SEI_VERSAO)){
            return $response->withJson(
                array(
                    "sucesso" => false,
                    "mensagem" => utf8_encode("M�dulo incompat�vel com a vers�o ".SEI_VERSAO." do SEI."),
                    "exception" => null
                ),
                401
            );
        }

        $response = $next($request, $response);
        return $response;
    }
}

/**
 * Classe com regra para verificar se existe permiss�o para execu��o dos servi�os
 */
class ServicePermissionsMiddleware {

    /**
     * Aplica regra
     *
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     * @param Closure $next
     * @return \Slim\Http\Response
     */
    public function __invoke($request, $response, $next)
    {

        $servicosHabilitados = ConfiguracaoSEI::getInstance()->getValor('WSSEI', 'ServicosHabilitados', false, false);
        $servicosDesabilitados = ConfiguracaoSEI::getInstance()->getValor('WSSEI', 'ServicosDesabilitados', false, false);

        $servicoHabilitado = true;
        $servicoDesabilitado = false;

        if($servicosHabilitados){
            $servicoHabilitado = $this->isRequestInPatternList($request, $servicosHabilitados);
        }

        if($servicosDesabilitados){
            $servicoDesabilitado = $this->isRequestInPatternList($request, $servicosDesabilitados);            
        }

        if(!$servicoHabilitado || $servicoDesabilitado){
            $response = $response->withJson(
                array(
                    "sucesso" => false,
                    "mensagem" => utf8_encode('Servi�o ' . $request->getMethod() . ':' . str_replace('api/v2/', '', $request->getUri()->getPath()) . ' n�o permitido'),                        
                    "exception" => null
                ),
                405
            );
        }else{
            $response = $next($request, $response);
        }
         
        return $response;
    }

    /**
     * verifica se path da requisi��o passa na espress�o regular da lista
     *
     * @param \Slim\Http\Request $request
     * @param array $lista
     * @return boolean retorna true se $request->getUri()->getPath() combina com com algum $pattern, false se n�o der match em nenhum registro
     */
    private function isRequestInPatternList($request, $lista){
        $result = false;
        foreach ($lista as $pattern) {                                      
            $pattern = '/^api\/v2\/' . $pattern . '$/m';
            if(preg_match($pattern, $request->getUri()->getPath())){                                
                $result = true;
                break;
            }
        }
        return $result;
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
$app->run();
