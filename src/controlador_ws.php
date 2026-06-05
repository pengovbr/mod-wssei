<?php
/**
 * Controlador (API v1) de servicos REST usando o framework Slim
 */

require_once dirname(__FILE__).'/../../SEI.php';
require_once dirname(__FILE__).'/vendor/autoload.php';
require_once dirname(__FILE__) . '/versao/v2/MdWsSeiServicosV2.php';

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

final class JsonResponseHelper
{
  public static function json(
        ResponseFactoryInterface $responseFactory,
        mixed $data,
        int $status = 200
    ): ResponseInterface {
      $response = $responseFactory->createResponse($status);

      $response->getBody()->write(
          json_encode(
              $data,
              JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
          )
      );

      return $response->withHeader(
          'Content-Type',
          'application/json'
      );
  }
}

final readonly class TokenValidationMiddleware implements MiddlewareInterface
{
  public function __construct(
        private ResponseFactoryInterface $responseFactory
    ) {
  }

  public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

      $token = $request->getHeaderLine('token');

    if ($token === '') {
        return JsonResponseHelper::json(
            $this->responseFactory,
            MdWsSeiRest::formataRetornoErroREST(
                new InfraException('Acesso negado!')
            ),
            401
        );
    }

      $rn = new MdWsSeiUsuarioRN();

      $result = $rn->autenticarToken($token);

    if (!$result['sucesso']) {
        return JsonResponseHelper::json(
            $this->responseFactory,
            MdWsSeiRest::formataRetornoErroREST(
                new InfraException('Token inválido!')
            ),
            403
        );
    }

      $unidade = $request->getHeaderLine('unidade');

    if ($unidade !== '') {
        $rn->alterarUnidadeAtual($unidade);
    }

      return $handler->handle($request);
  }
}

final readonly class ModuleVerificationMiddleware implements MiddlewareInterface
{
  public function __construct(
        private ResponseFactoryInterface $responseFactory
    ) {
  }

  public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

    if (!class_exists('MdWsSeiRest', false) || !MdWsSeiRest::moduloAtivo()) {

        return JsonResponseHelper::json(
            $this->responseFactory,
            [
                'sucesso' => false,
                'mensagem' => 'Módulo inativo.',
                'exception' => null,
            ],
            401
        );
    }

      $rest = new MdWsSeiRest();

    if (!$rest->verificaCompatibilidade(SEI_VERSAO)) {

        return JsonResponseHelper::json(
            $this->responseFactory,
            [
                'sucesso' => false,
                'mensagem' =>
                    'Módulo incompatível com a versão ' .
                    SEI_VERSAO .
                    ' do SEI.',
                'exception' => null,
            ],
            401
        );
    }

      return $handler->handle($request);
  }
}

final class EncodingMiddleware implements MiddlewareInterface
{
  public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

      $request = $request->withParsedBody(
          MdWsSeiRest::dataToIso88591(
              $request->getParsedBody()
          ) ?: []
      );

      $request = $request->withQueryParams(
          MdWsSeiRest::dataToIso88591(
              $request->getQueryParams()
          ) ?: []
      );

      return $handler->handle($request);
  }
}


$app = AppFactory::create();

$responseFactory = $app->getResponseFactory();
$app->setBasePath('/sei/modulos/wssei/controlador_ws.php');

$app->add(new EncodingMiddleware());
//$app->add(new TokenValidationMiddleware($responseFactory));
$app->add(new ModuleVerificationMiddleware($responseFactory));

MdWsSeiServicosV2::getInstance($app)->registrarServicos();

$app->run();
