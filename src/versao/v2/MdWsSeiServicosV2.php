<?php


require_once dirname(__FILE__) . '/../MdWsSeiVersaoServicos.php';
use Slim\Routing\RouteContext;

/**
 * Undocumented class
 * @property SLim\App $slimApp
 */
class MdWsSeiServicosV2 extends MdWsSeiVersaoServicos
{

  public static function getInstance(Slim\App $slimApp)
    {
      return new MdWsSeiServicosV2($slimApp);
  }

    /**
     * Método que registra os serviços a serem disponibilizados
     * @return Slim\App
     */

     // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded
  public function registrarServicos()
    {
      /**
       * Grupo para a versao v2 de servicos REST
       */
      $this->slimApp->group('/api/v2', function (\Slim\Routing\RouteCollectorProxy $app) {
          /**
           * @var Slim/App $this
           */
          $app->get('/versao', function ($request, $response, $args) {
              $MdWsSeiRest = new MdWsSeiRest();
              return JsonResponse::create($response, MdWsSeiRest::formataRetornoSucessoREST(
                  null,
                  [
                      'sei' => SEI_VERSAO,
                      'wssei' => $MdWsSeiRest->getVersao()
                  ]
              )
              );
          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));
          /**
           * Grupo de autenticacao <publico>
           */
          $app->post('/autenticar', function ($request, $response, $args) {
              /** @var $response Slim\Http\Response */
              $rn = new MdWsSeiUsuarioRN();
              $usuarioDTO = new UsuarioDTO();
              $usuarioDTO->setStrSigla($this->getParam($request,'usuario'));
              $usuarioDTO->setStrSenha($this->getParam($request,'senha'));
              $orgaoDTO = new OrgaoDTO();
              $orgaoDTO->setNumIdOrgao($this->getParam($request,'orgao'));
                
              return JsonResponse::create($response, $rn->apiAutenticar($usuarioDTO, $orgaoDTO));
          });
          /**
           * Grupo de controlador de Órgão <publico>
           */
          $app->group('/orgao', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiOrgaoRN();
                  $dto = new OrgaoDTO();
                  return JsonResponse::create($response, $rn->listarOrgao($dto));
              });
          });
          /**
           * Grupo de controlador de Contexto <publico>
           */
          $app->group('/contexto', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/listar/{orgao}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiContextoRN();
                  $dto = new OrgaoDTO();
                  $dto->setNumIdOrgao($orgao);
                  return JsonResponse::create($response, $rn->listarContexto($dto));
              });
          });

          /**
           * Grupo de controlador de Usuário
           */
          $app->group('/usuario', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->post('/alterar/unidade', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiUsuarioRN();
                  return JsonResponse::create($response, $rn->alterarUnidadeAtual($this->getParam($request, 'unidade')));
              });
              $app->get('/listar', function ($request, $response, $args) {
                  $dto = new UnidadeDTO();
                if ($this->getParam($request, 'unidade')) {
                    $dto->setNumIdUnidade($this->getParam($request, 'unidade'));
                }
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiUsuarioRN();
                  return JsonResponse::create($response, $rn->listarUsuarios($dto));
              });
              $app->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiUsuarioRN();
                  return JsonResponse::create($response, 
                      $rn->apiPesquisarUsuario(
                          $this->getParam($request, 'palavrachave'),
                          $this->getParam($request, 'orgao'))
                  );
              });
              $app->get('/unidades', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $dto = new UsuarioDTO();
                  $dto->setNumIdUsuario($this->getParam($request, 'usuario'));
                  $rn = new MdWsSeiUsuarioRN();
                  return JsonResponse::create($response, $rn->listarUnidadesUsuario($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de Unidades
           */
          $app->group('/unidade', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiUnidadeRN();
                  $dto = new UnidadeDTO();
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if ($this->getParam($request, 'filter')) {
                    $dto->setStrSigla($this->getParam($request, 'filter'));
                }
                  return JsonResponse::create($response, $rn->pesquisarUnidade($dto));
              });
              $app->get('/outras/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiUnidadeRN();
                  $dto = new UnidadeDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->adicionarCriterio(
                        array('IdUnidade'),
                        array(InfraDTO::$OPER_IGUAL),
                        array($this->getParam($request, 'id'))
                    );
                }
                if ($this->getParam($request, 'filter') && $this->getParam($request, 'filter') != '') {
                    $dto->setStrPalavrasPesquisa($this->getParam($request, 'filter'));
                }
                  return JsonResponse::create($response, $rn->pesquisarOutras($dto));
              });

              $app->get('/textopadrao/interno/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiTextoPadraoInternoRN();
                  $dto = new TextoPadraoInternoDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdTextoPadraoInterno($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter')) {
                    $dto->setStrNome($this->getParam($request, 'filter'));
                }
                  return JsonResponse::create($response, $rn->pesquisar($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de anotacao
           */
          $app->group('/anotacao', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->post('/', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiAnotacaoRN();
                  $dto = $rn->encapsulaAnotacao($request->getParsedBody());
                  return JsonResponse::create($response, $rn->cadastrarAnotacao($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de bloco
           */
          $app->group('/bloco', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/assinatura/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                if (!empty($this->getParam($request, 'limit'))) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!empty($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!empty($this->getParam($request, 'id'))) {
                    $dto->setNumIdBloco($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrPalavrasPesquisa($this->getParam($request, 'filter'));
                }
                if ($this->getParam($request, 'estado') != '') {
                    $dto->setStrStaEstado(
                        explode(',', $this->getParam($request, 'estado')),
                        InfraDTO::$OPER_IN
                    );
                }

                  return JsonResponse::create($response, $rn->pesquisarBlocoAssinatura($dto));
              });
              $app->post('/assinatura/criar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->cadastrarBlocoAssinaturaRequest($request));
              });
              $app->post('/assinatura/{bloco:[0-9]+}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->alterarBlocoAssinaturaRequest($request));
              });
              $app->post('/assinatura/excluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $arrIdBlocos = array();
                if ($this->getParam($request, 'blocos')) {
                    $arrIdBlocos = explode(',', $this->getParam($request, 'blocos'));
                }
                  return JsonResponse::create($response, $rn->excluirBlocos($arrIdBlocos));
              });
              $app->post('/assinatura/concluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $arrIdBlocos = array();
                if ($this->getParam($request, 'blocos')) {
                    $arrIdBlocos = explode(',', $this->getParam($request, 'blocos'));
                }
                  return JsonResponse::create($response, $rn->concluirBlocos($arrIdBlocos));
              });
              $app->post('/assinatura/{bloco:[0-9]+}/reabrir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($bloco);
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->reabrirBloco($dto));
              });
              $app->post('/assinatura/{bloco:[0-9]+}/retornar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($bloco);
                  return JsonResponse::create($response, $rn->retornarBloco($dto));
              });
              $app->post('/assinatura/{bloco:[0-9]+}/disponibilizar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($bloco);
                  return JsonResponse::create($response, $rn->disponibilizarBlocoAssinatura($dto));
              });
              $app->post('/assinatura/{bloco:[0-9]+}/disponibilizacao/cancelar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($bloco);
                  return JsonResponse::create($response, $rn->cancelarDisponibilizacaoBlocoAssinatura($dto));
              });
              $app->get('/assinatura/{bloco:[0-9]+}/documentos/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new RelBlocoProtocoloDTO();
                  $dto->setNumIdBloco($bloco);
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarDocumentosBlocoAssinatura($dto));
              });
              $app->post('/{bloco:[0-9]+}/anotacao', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new RelBlocoProtocoloDTO();
                  $dto->setNumIdBloco($bloco);
                  $dto->setDblIdProtocolo($this->getParam($request, 'protocolo'));
                  $dto->setStrAnotacao($this->getParam($request, 'anotacao'));
                  return JsonResponse::create($response, $rn->cadastrarAnotacaoBloco($dto));
              });
              $app->post('/assinatura/{bloco:[0-9]+}/assinar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->apiAssinarBloco(
                      $bloco,
                      $this->getParam($request, 'orgao'),
                      mb_convert_encoding($this->getParam($request, 'cargo'), "ISO-8859-1", "UTF-8"),
                      $this->getParam($request, 'login'),
                      $this->getParam($request, 'senha'),
                      $this->getParam($request, 'usuario')
                  ));
              });
              $app->post('/assinatura/assinar/documentos', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->apiAssinarDocumentos(
                      $this->getParam($request, 'orgao'),
                      mb_convert_encoding($this->getParam($request, 'cargo'), "ISO-8859-1", "UTF-8"),
                      $this->getParam($request, 'login'),
                      $this->getParam($request, 'senha'),
                      $this->getParam($request, 'usuario'),
                      explode(',', $this->getParam($request, 'documentos'))
                  ));
              });
              $app->post('/assinatura/{bloco:[0-9]+}/documentos/retirar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->apiRetirarDocumentos(
                      $bloco,
                      explode(',', $this->getParam($request, 'documentos'))
                  ));
              });
              $app->post('/assinatura/anotacao/cadastrar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $dto = new RelBlocoProtocoloDTO();
                if ($this->getParam($request, 'bloco')) {
                    $dto->setNumIdBloco($this->getParam($request, 'bloco'));
                }
                if ($this->getParam($request, 'documento')) {
                    $dto->setDblIdProtocolo($this->getParam($request, 'documento'));
                }
                  $dto->setStrAnotacao($this->getParam($request, 'anotacao'));
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->salvarAnotacaoBloco($dto));
              });
              $app->post('/assinatura/anotacao/alterar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $dto = new RelBlocoProtocoloDTO();
                if ($this->getParam($request, 'bloco')) {
                    $dto->setNumIdBloco($this->getParam($request, 'bloco'));
                }
                if ($this->getParam($request, 'documento')) {
                    $dto->setDblIdProtocolo($this->getParam($request, 'documento'));
                }
                  $dto->setStrAnotacao($this->getParam($request, 'anotacao'));
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->salvarAnotacaoBloco($dto));
              });
              $app->post('/interno/criar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $dto = new BlocoDTO();
                  $dto->setStrDescricao($this->getParam($request, 'descricao'));
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->cadastrarBlocoInterno($dto));
              });
              $app->post('/interno/{bloco:[0-9]+}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($bloco);
                  $dto->setStrDescricao($this->getParam($request, 'descricao'));
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->alterarBlocoInterno($dto));
              });
              $app->post('/interno/concluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $arrIdBlocos = array();
                if ($this->getParam($request, 'blocos')) {
                    $arrIdBlocos = explode(',', $this->getParam($request, 'blocos'));
                }
                  return JsonResponse::create($response, $rn->concluirBlocos($arrIdBlocos));
              });
              $app->post('/interno/excluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $arrIdBlocos = array();
                if ($this->getParam($request, 'blocos')) {
                    $arrIdBlocos = explode(',', $this->getParam($request, 'blocos'));
                }
                  return JsonResponse::create($response, $rn->excluirBlocos($arrIdBlocos));
              });
              $app->post('/interno/anotacao/cadastrar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $dto = new RelBlocoProtocoloDTO();
                if ($this->getParam($request, 'bloco')) {
                    $dto->setNumIdBloco($this->getParam($request, 'bloco'));
                }
                if ($this->getParam($request, 'protocolo')) {
                    $dto->setDblIdProtocolo($this->getParam($request, 'protocolo'));
                }
                  $dto->setStrAnotacao($this->getParam($request, 'anotacao'));
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->salvarAnotacaoBloco($dto));
              });
              $app->post('/interno/anotacao/alterar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $dto = new RelBlocoProtocoloDTO();
                if ($this->getParam($request, 'bloco')) {
                    $dto->setNumIdBloco($this->getParam($request, 'bloco'));
                }
                if ($this->getParam($request, 'protocolo')) {
                    $dto->setDblIdProtocolo($this->getParam($request, 'protocolo'));
                }
                  $dto->setStrAnotacao($this->getParam($request, 'anotacao'));
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->salvarAnotacaoBloco($dto));
              });
              $app->post('/interno/{bloco:[0-9]+}/processos/retirar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->apiRetirarProcessos(
                      $bloco,
                      explode(',', $this->getParam($request, 'protocolos'))
                  ));
              });
              $app->post('/assinatura/{bloco:[0-9]+}/documentos/incluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->apiIncluirDocumentosBlocoAssinatura(
                      $bloco,
                      explode(',', $this->getParam($request, 'documentos'))
                  ));
              });
              $app->post('/interno/{bloco:[0-9]+}/reabrir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($bloco);
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->reabrirBloco($dto));
              });
              $app->get('/interno/{bloco:[0-9]+}/processos/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new RelBlocoProtocoloDTO();
                  $dto->setNumIdBloco($bloco);
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarProcessosBlocoInterno($dto));
              });
              $app->get('/interno/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdBloco($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrPalavrasPesquisa($this->getParam($request, 'filter'));
                }
                if ($this->getParam($request, 'estado') != '') {
                    $dto->setStrStaEstado(
                        explode(',', $this->getParam($request, 'estado')),
                        InfraDTO::$OPER_IN
                    );
                }

                  return JsonResponse::create($response, $rn->pesquisarBlocoInterno($dto));
              });
              $app->post('/interno/{bloco:[0-9]+}/processos/incluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $bloco = $route->getArgument('bloco');
                  $rn = new MdWsSeiBlocoRN();
                  return JsonResponse::create($response, $rn->apiIncluirProcessosBlocoInterno(
                      $bloco,
                      explode(',', $this->getParam($request, 'protocolos'))
                  ));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de documentos
           */
          $app->group('/documento', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/{documento}/interno/visualizar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $documento = $route->getArgument('documento');
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($documento);
                  return JsonResponse::create($response, $rn->visualizarInterno($dto));
              });
              $app->get('/assunto/sugestao/{serie}/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $serie = $route->getArgument('serie');
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new RelSerieAssuntoDTO();
                  $dto->setNumIdSerie($serie);
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdAssunto($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrDescricaoAssunto($this->getParam($request, 'filter'));
                }

                  return JsonResponse::create($response, 
                      $rn->sugestaoAssunto($dto)
                  );
              });

              $app->get('/externo/consultar/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, $rn->consultarDocumentoExterno($protocolo));
              });
              $app->get('/listar/ciencia/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new MdWsSeiProcessoDTO();
                  $dto->setStrValor($protocolo);
                  return JsonResponse::create($response, $rn->listarCienciaDocumento($dto));
              });
              $app->get('/listar/assinaturas/{documento}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $documento = $route->getArgument('documento');
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($documento);
                  return JsonResponse::create($response, $rn->listarAssinaturasDocumento($dto));
              });
              $app->get('/{documento:[0-9]+}/bloco/assinatura/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $documento = $route->getArgument('documento');
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($documento);
                  return JsonResponse::create($response, $rn->listarBlocosAssinatura($dto));
              });
              $app->post('/assinar/bloco', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, $rn->apiAssinarDocumentos(
                      $this->getParam($request, 'arrDocumento'),
                      $this->getParam($request, 'orgao'),
                      $this->getParam($request, 'cargo'),
                      $this->getParam($request, 'login'),
                      $this->getParam($request, 'senha'),
                      $this->getParam($request, 'usuario')
                  ));
              });
              $app->post('/secao/alterar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $dados["documento"] = $this->getParam($request, 'documento');
                  $dados["secoes"] = json_decode($this->getParam($request, 'secoes'), true);
                  $dados["versao"] = $this->getParam($request, 'versao');

                  // Ajuste de encoding das secoes
                  setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br
                for ($i = 0; $i < count($dados["secoes"]); $i++) {
                    // $dados["secoes"][$i]['conteudo'] = iconv('UTF-8', 'ISO-8859-1', $dados["secoes"][$i]['conteudo']);
                    $dados["secoes"][$i]['conteudo'] = mb_convert_encoding($dados["secoes"][$i]['conteudo'], 'ISO-8859-1', 'UTF-8');
                }

                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, 
                      $rn->alterarSecaoDocumento($dados)
                  );
              });
              $app->post('/ciencia', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($this->getParam($request, 'documento'));
                  return JsonResponse::create($response, $rn->darCiencia($dto));
              });
              $app->post('/assinar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, $rn->apiAssinarDocumento(
                      $this->getParam($request, 'documento'),
                      $this->getParam($request, 'orgao'),
                      $this->getParam($request, 'cargo'),
                      $this->getParam($request, 'login'),
                      $this->getParam($request, 'senha'),
                      $this->getParam($request, 'usuario')
                  ));
              });
              $app->get('/listar/{procedimento}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $procedimento = $route->getArgument('procedimento');
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                if ($procedimento) {
                    $dto->setDblIdProcedimento($procedimento);
                }
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual(0);
                } else {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarDocumentosProcesso($dto));
              });
              $app->get('/secao/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($this->getParam($request, 'id'));

                  return JsonResponse::create($response, $rn->listarSecaoDocumento($dto));
              });
              $app->get('/tipo/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new MdWsSeiDocumentoDTO();

                  $dto->setNumIdTipoDocumento($this->getParam($request, 'id'));
                  $dto->setStrNomeTipoDocumento($this->getParam($request, 'filter'));
                  $dto->setStrFavoritos($this->getParam($request, 'favoritos'));

                  $arrAplicabilidade = explode(",", $this->getParam($request, 'aplicabilidade'));

                  $dto->setArrAplicabilidade($arrAplicabilidade);
                  $dto->setNumStart($this->getParam($request, 'start'));
                  $dto->setNumLimit($this->getParam($request, 'limit'));

                  return JsonResponse::create($response, $rn->pesquisarTipoDocumento($dto));
              });
              $app->get('/tipo/template', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new MdWsSeiDocumentoDTO();
                  $dto->setNumIdTipoDocumento($this->getParam($request, 'id'));
                  //$dto->setNumIdTipoProcedimento($this->getParam($request, 'idTipoProcedimento'));
                  $dto->setNumIdProcesso($this->getParam($request, 'procedimento'));

                  return JsonResponse::create($response, $rn->pesquisarTemplateDocumento($dto));
              });
              $app->get('/baixar/anexo/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new ProtocoloDTO();
                if ($protocolo) {
                    $dto->setDblIdProtocolo($protocolo);
                }
                  return JsonResponse::create($response, $rn->downloadAnexo($dto));
              });
              $app->post('/{procedimento}/externo/criar', function ($request, $response, $args) {
                  /** @var $request \Slim\Psr7\Request */
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, 
                      $rn->criarDocumentoExternoRequest($request)
                  );
              });
              $app->post('/{procedimento}/interno/criar', function ($request, $response, $args) {
                  /** @var $request \Slim\Psr7\Request */
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, 
                      $rn->criarDocumentoInternoRequest($request)
                  );
              });
              $app->post('/externo/{documento}/alterar', function ($request, $response, $args) {
                  /** @var $request \Slim\Psr7\Request */
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, 
                      $rn->alterarDocumentoExternoRequest($request)
                  );
              });
              $app->post('/interno/{documento}/alterar', function ($request, $response, $args) {
                  /** @var $request \Slim\Psr7\Request */
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, 
                      $rn->alterarDocumentoInternoRequest($request)
                  );
              });
              $app->get('/interno/consultar/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, $rn->consultarDocumentoInterno($protocolo));
              });

              $app->get('/interno/formatado/consultar/{protocolo_formatado}', function ($request, $response, $args) {
                  /** @var $request Slim\Psr7\Request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo_formatado = $route->getArgument('protocolo_formatado');
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, $rn->consultarDocumentoInternoFormatado($protocolo_formatado));
              });

              $app->post('/incluir', function ($request, $response, $args) {
                try {
                    /** @var Slim\Psr7\Request $request */
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
                  //return JsonResponse::create($response, );
              });

              $app->post('/linkedicao', function ($request, $response, $args) {
                try {
                    session_start();

                  if (empty($this->getParam($request, 'id_documento'))) {
                      throw new InfraException('Deve ser passado valor para o (id_documento).');
                  }

                    // Recupera o id do procedimento
                    $protocoloDTO = new DocumentoDTO();
                    $protocoloDTO->setDblIdDocumento($this->getParam($request, 'id_documento'));
                    $protocoloDTO->retDblIdProcedimento();
                    $protocoloRN = new DocumentoRN();
                    $protocoloDTO = $protocoloRN->consultarRN0005($protocoloDTO);

                  if (empty($protocoloDTO)) {
                      throw new InfraException('Documento não encontrado');
                  }

                    $linkassinado = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=editor_montar&acao_origem=arvore_visualizar&id_procedimento=' . $protocoloDTO->getDblIdProcedimento() . '&id_documento=' . $this->getParam($request, 'id_documento'));

                    return JsonResponse::create($response, 
                        array("link" => $linkassinado, "phpsessid" => session_id())
                    );

                } catch (InfraException $e) {
                    die($e->getStrDescricao());
                }
              });

              $app->get('/tipoconferencia/pesquisar', function ($request, $response, $args) {
                  $dto = new TipoConferenciaDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdTipoConferencia($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrDescricao($this->getParam($request, 'filter'));
                }
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  return JsonResponse::create($response, $rn->pesquisarTipoConferencia($dto));
              });


          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de processos
           */
          $app->group('/processo', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/{protocolo:[0-9]+}', function ($request, $response, $args) {
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiProcedimentoRN();
                  return JsonResponse::create($response, 
                      $rn->consultar($protocolo)
                  );
              });
              $app->get('/consultar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  return JsonResponse::create($response, 
                      $rn->apiConsultarProcessoDigitado($this->getParam($request, 'protocoloFormatado'))
                  );
              });
              $app->get('/tipo/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();

                  $objGetMdWsSeiTipoProcedimentoDTO = new MdWsSeiTipoProcedimentoDTO();
                  $objGetMdWsSeiTipoProcedimentoDTO->setNumIdTipoProcedimento($this->getParam($request, 'id'));
                  $objGetMdWsSeiTipoProcedimentoDTO->setStrNome($this->getParam($request, 'filter'));
                  $objGetMdWsSeiTipoProcedimentoDTO->setStrFavoritos($this->getParam($request, 'favoritos'));
                  $objGetMdWsSeiTipoProcedimentoDTO->setNumStart($this->getParam($request, 'start'));
                  $objGetMdWsSeiTipoProcedimentoDTO->setNumLimit($this->getParam($request, 'limit'));

                  return JsonResponse::create($response, 
                      $rn->listarTipoProcedimento($objGetMdWsSeiTipoProcedimentoDTO)
                  );
              });

              $app->get('/consultar/{id}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $id = $route->getArgument('id');
                  $rn = new MdWsSeiProcedimentoRN();

                  $dto = new MdWsSeiProcedimentoDTO();
                  //Atribuir parametros para o DTO
                if ($id) {
                    $dto->setNumIdProcedimento($id);
                }

                  return JsonResponse::create($response, $rn->consultarProcesso($dto));
              });

              $app->get('/assunto/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new AssuntoDTO();
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrPalavrasPesquisa($this->getParam($request, 'filter'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdAssunto($this->getParam($request, 'id'));
                }
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }

                  return JsonResponse::create($response, 
                      $rn->pesquisarAssunto($dto)
                  );
              });

              $app->get('/tipo/template', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();

                  $dto = new MdWsSeiTipoProcedimentoDTO();
                  $dto->setNumIdTipoProcedimento($this->getParam($request, 'id'));

                  return JsonResponse::create($response, 
                      $rn->buscarTipoTemplate($dto)
                  );
              });

              $app->post('/{protocolo}/sobrestar/processo', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new RelProtocoloProtocoloDTO();
                if ($protocolo) {
                    $dto->setDblIdProtocolo2($protocolo);
                }
                  $dto->setDblIdProtocolo1($this->getParam($request, 'protocoloDestino'));
                if ($this->getParam($request, 'motivo')) {
                    $dto->setStrMotivo($this->getParam($request, 'motivo'));
                }

                  return JsonResponse::create($response, $rn->sobrestamentoProcesso($dto));
              });
              $app->post('/{protocolo:[0-9]+}/cancelar/sobrestamento', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProcedimentoDTO();
                  $dto->setDblIdProcedimento($protocolo);
                  return JsonResponse::create($response, $rn->removerSobrestamentoProcesso($dto));
              });
              $app->post('/{procedimento}/ciencia', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $procedimento = $route->getArgument('procedimento');
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProcedimentoDTO();
                if ($procedimento) {
                    $dto->setDblIdProcedimento($procedimento);
                }
                  return JsonResponse::create($response, $rn->darCiencia($dto));
              });
              $app->get('/listar/sobrestamento/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new AtividadeDTO();
                if ($this->getParam($request, 'unidade')) {
                    $dto->setNumIdUnidade($this->getParam($request, 'unidade'));
                }
                if ($protocolo) {
                    $dto->setDblIdProtocolo($protocolo);
                }
                  return JsonResponse::create($response, $rn->listarSobrestamentoProcesso($dto));
              });
              $app->get('/listar/unidades/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProtocoloDTO();
                if ($protocolo) {
                    $dto->setDblIdProtocolo($protocolo);
                }
                  return JsonResponse::create($response, $rn->listarUnidadesProcesso($dto));
              });
              $app->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProtocoloDTO();

                if ($this->getParam($request, 'id')) {
                    $dto->setDblIdProtocolo($this->getParam($request, 'id'));
                }

                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if ($this->getParam($request, 'usuario')) {
                    $dto->setNumIdUsuarioAtribuicaoAtividade($this->getParam($request, 'usuario'));
                }
                if ($this->getParam($request, 'tipo')) {
                    $dto->setStrSinTipoBusca($this->getParam($request, 'tipo'));
                } else {
                    $dto->setStrSinTipoBusca(null);
                }
                if ($this->getParam($request, 'apenasMeus')) {
                    $dto->setStrSinApenasMeus($this->getParam($request, 'apenasMeus'));
                } else {
                    $dto->setStrSinApenasMeus('N');
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarProcessos($dto));
              });

              $app->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiPesquisaProtocoloSolrDTO();
                if ($this->getParam($request, 'grupo')) {
                    $dto->setNumIdGrupoAcompanhamentoProcedimento($this->getParam($request, 'grupo'));
                }
                if ($this->getParam($request, 'palavrasChave')) {
                    $dto->setStrPalavrasChave($this->getParam($request, 'palavrasChave'));
                }
                if ($this->getParam($request, 'descricao')) {
                    $dto->setStrDescricao($this->getParam($request, 'descricao'));
                }
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'staTipoData'))) {
                    $dto->setStrStaTipoData($this->getParam($request, 'staTipoData'));
                }
                if ($this->getParam($request, 'dataInicio')) {
                    $dto->setDtaInicio($this->getParam($request, 'dataInicio'));
                }
                if ($this->getParam($request, 'dataFim')) {
                    $dto->setDtaFim($this->getParam($request, 'dataFim'));
                }
                if (!is_null($this->getParam($request, 'idUnidadeGeradora')) && $this->getParam($request, 'idUnidadeGeradora') != '') {
                    $dto->setNumIdUnidadeGeradora($this->getParam($request, 'idUnidadeGeradora'));
                }
                if (!is_null($this->getParam($request, 'idAssunto')) && $this->getParam($request, 'idAssunto') != '') {
                    $dto->setNumIdAssunto($this->getParam($request, 'idAssunto'));
                }
                if ($this->getParam($request, 'buscaRapida')) {
                    $dto->setStrbuscaRapida(InfraUtil::retirarFormatacao($this->getParam($request, 'buscaRapida'), false));
                }

                  return JsonResponse::create($response, $rn->pesquisarProcessosSolar($dto));
              });
              $app->get('/listar/meus/acompanhamentos', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProtocoloDTO();
                if ($this->getParam($request, 'grupo')) {
                    $dto->setNumIdGrupoAcompanhamentoProcedimento($this->getParam($request, 'grupo'));
                }
                if ($this->getParam($request, 'usuario')) {
                    $dto->setNumIdUsuarioGeradorAcompanhamento($this->getParam($request, 'usuario'));
                }
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarProcedimentoAcompanhamentoUsuario($dto));
              });
              $app->get('/listar/acompanhamentos', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProtocoloDTO();
                if ($this->getParam($request, 'grupo')) {
                    $dto->setNumIdGrupoAcompanhamentoProcedimento($this->getParam($request, 'grupo'));
                }
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarProcedimentoAcompanhamentoUnidade($dto));
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
              $app->post('/enviar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = $rn->encapsulaEnviarProcessoEntradaEnviarProcessoAPI($request->getParsedBody());
                  return JsonResponse::create($response, $rn->enviarProcesso($dto));
              });
              $app->post('/concluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new EntradaConcluirProcessoAPI();
                if ($this->getParam($request, 'numeroProcesso')) {
                    $dto->setProtocoloProcedimento($this->getParam($request, 'numeroProcesso'));
                }
                  return JsonResponse::create($response, $rn->concluirProcesso($dto));
              });
              $app->post('/reabrir/{procedimento}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $procedimento = $route->getArgument('procedimento');
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new EntradaReabrirProcessoAPI();
                  $dto->setIdProcedimento($procedimento);
                  return JsonResponse::create($response, $rn->reabrirProcesso($dto));
              });
              $app->post('/acompanhar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = $rn->encapsulaAcompanhamento($request->getParsedBody());
                  return JsonResponse::create($response, $rn->cadastrarAcompanhamento($dto));
              });
              $app->post('/acompanhamento/alterar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = $rn->encapsulaAcompanhamento($request->getParsedBody());
                  return JsonResponse::create($response, $rn->alterarAcompanhamento($dto));
              });
              $app->get('/acompanhamento/consultar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = new AcompanhamentoDTO();
                  $dto->setDblIdProtocolo($this->getParam($request, 'protocolo'));
                  return JsonResponse::create($response, $rn->consultarAcompanhamentoPorProtocolo($dto));
              });
              $app->post('/acompanhamento/{acompanhamento:[0-9]+}/excluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $acompanhamento = $route->getArgument('acompanhamento');
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = new AcompanhamentoDTO();
                  $dto->setNumIdAcompanhamento($acompanhamento);
                  return JsonResponse::create($response, $rn->excluirAcompanhamento($dto));
              });
              $app->post('/agendar/retorno/programado', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiRetornoProgramadoRN();
                  $dto = $rn->encapsulaRetornoProgramado($request->getParsedBody());
                  return JsonResponse::create($response, $rn->agendarRetornoProgramado($dto));
              });
              $app->post('/atribuir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $api = new EntradaAtribuirProcessoAPI();

                if ($this->getParam($request, 'numeroProcesso')) {
                    $api->setProtocoloProcedimento($this->getParam($request, 'numeroProcesso'));
                }
                if ($this->getParam($request, 'usuario')) {
                    $api->setIdUsuario($this->getParam($request, 'usuario'));
                }
                  $rn = new MdWsSeiProcedimentoRN();
                  return JsonResponse::create($response, $rn->atribuirProcesso($api));
              });
              $app->post('/{protocolo}/remover/atribuicao', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $dto = new ProtocoloDTO();
                  $dto->setDblIdProtocolo($protocolo);
                  $rn = new MdWsSeiProcedimentoRN();
                  return JsonResponse::create($response, $rn->removerAtribuicao($dto));
              });
              $app->get('/{protocolo}/consultar/atribuicao', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $dto = new ProtocoloDTO();
                  $dto->setDblIdProtocolo($protocolo);
                  $rn = new MdWsSeiProcedimentoRN();
                  return JsonResponse::create($response, $rn->consultarAtribuicao($dto));
              });
              $app->get('/verifica/acesso/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProtocoloDTO();
                  $dto->setDblIdProtocolo($protocolo);
                  return JsonResponse::create($response, $rn->verificaAcesso($dto));
              });
              $app->post('/identificacao/acesso', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $usuarioDTO = new UsuarioDTO();
                  $usuarioDTO->setStrSenha($this->getParam($request, 'senha'));
                  $protocoloDTO = new ProtocoloDTO();
                  $protocoloDTO->setDblIdProtocolo($this->getParam($request, 'protocolo'));
                  $rn = new MdWsSeiProcedimentoRN();

                  return JsonResponse::create($response, $rn->apiIdentificacaoAcesso($usuarioDTO, $protocoloDTO));
              });
              $app->post('/{procedimento}/credenciamento/conceder', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $procedimento = $route->getArgument('procedimento');
                  $rn = new MdWsSeiCredenciamentoRN();
                  $dto = new ConcederCredencialDTO();
                  $dto->setDblIdProcedimento($procedimento);
                  $dto->setNumIdUnidade($this->getParam($request, 'unidade'));
                  $dto->setNumIdUsuario($this->getParam($request, 'usuario'));

                  return JsonResponse::create($response, $rn->concederCredenciamento($dto));
              });
              $app->post('/{procedimento}/credenciamento/renunciar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $procedimento = $route->getArgument('procedimento');
                  $rn = new MdWsSeiCredenciamentoRN();
                  $dto = new ProcedimentoDTO();
                  $dto->setDblIdProcedimento($procedimento);

                  return JsonResponse::create($response, $rn->renunciarCredencial($dto));
              });
              $app->post('/{procedimento}/credenciamento/cassar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $procedimento = $route->getArgument('procedimento');
                  $rn = new MdWsSeiCredenciamentoRN();
                  $dto = new AtividadeDTO();
                  $dto->setNumIdAtividade($this->getParam($request, 'atividade'));

                  return JsonResponse::create($response, $rn->cassarCredencial($dto));
              });
              $app->get('/{procedimento}/credenciamento/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $procedimento = $route->getArgument('procedimento');
                  $rn = new MdWsSeiCredenciamentoRN();
                  $dto = new ProcedimentoDTO();
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  $dto->setDblIdProcedimento($procedimento);

                  return JsonResponse::create($response, $rn->listarCredenciaisProcesso($dto));
              });

              $app->post('/criar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  //Assunto  explode lista de objetos
                  $assuntos = array();
                  $assuntos = json_decode($this->getParam($request, 'assuntos'), true);
                  //Interessado explode lista de objetos
                  $interessados = array();
                  $interessados = json_decode($this->getParam($request, 'interessados'), true);

                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProcedimentoDTO();

                  setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

                //   $especificacaoFormatado = iconv('UTF-8', 'ISO-8859-1', $this->getParam($request, 'especificacao'));
                //   $observacoesFormatado = iconv('UTF-8', 'ISO-8859-1', $this->getParam($request, 'observacoes'));
                  $especificacaoFormatado = mb_convert_encoding($this->getParam($request, 'especificacao'), 'ISO-8859-1', 'UTF-8');
                  $observacoesFormatado = mb_convert_encoding($this->getParam($request, 'observacoes'), 'ISO-8859-1', 'UTF-8');

                  //Atribuir parametros para o DTO
                  $dto->setArrObjInteressado($interessados);
                  $dto->setArrObjAssunto($assuntos);
                  $dto->setNumIdTipoProcedimento($this->getParam($request, 'tipoProcesso'));
                  $dto->setStrEspecificacao($especificacaoFormatado);
                  $dto->setStrObservacao($observacoesFormatado);
                  $dto->setNumNivelAcesso($this->getParam($request, 'nivelAcesso'));
                  $dto->setNumIdHipoteseLegal($this->getParam($request, 'hipoteseLegal'));
                  $dto->setStrStaGrauSigilo($this->getParam($request, 'grauSigilo'));

                  return JsonResponse::create($response, $rn->gerarProcedimento($dto));
              });

              $app->post('/{protocolo}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  return JsonResponse::create($response, $rn->alterarProcessoRequest($request));
              });

              //Serviço de recebimento do processo na unidade - adicionado por Adriano Cesar - MPOG
              $app->post('/receber', function ($request, $response, $args) {

                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProcedimentoDTO();
                if ($this->getParam($request, 'procedimento')) {
                    $dto->setNumIdProcedimento($this->getParam($request, 'procedimento'));
                }
                  return JsonResponse::create($response, $rn->receberProcedimento($dto));
              });

              $app->get('/{protocolo}/interessados/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiParticipanteRN();
                  $dto = new ParticipanteDTO();
                  $dto->setDblIdProtocolo($protocolo);
                if ($this->getParam($request, 'limit') && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }

                  return JsonResponse::create($response, $rn->processoInteressadosListar($dto));
              });

              $app->get('/assunto/sugestao/{tipoProcedimento}/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $tipoProcedimento = $route->getArgument('tipoProcedimento');
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new RelTipoProcedimentoAssuntoDTO();
                  $dto->setNumIdTipoProcedimento($tipoProcedimento);
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdAssunto($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrDescricaoAssunto($this->getParam($request, 'filter'));
                }

                  return JsonResponse::create($response, $rn->sugestaoAssunto($dto));
              });
              $app->get('/{protocolo}/ciencia/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProcedimentoHistoricoDTO();
                  $dto->setDblIdProcedimento($protocolo);
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarCienciaProcesso($dto));
              });
              $app->get('/{protocolo}/relacionamentos', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProcedimentoDTO();
                  $dto->setDblIdProcedimento($protocolo);

                  return JsonResponse::create($response, $rn->processosRelacionados($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de atividade
           */
          $app->group('/atividade', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiAtividadeRN();
                  $dto = new AtividadeDTO();
                if ($this->getParam($request, 'procedimento')) {
                    $dto->setDblIdProtocolo($this->getParam($request, 'procedimento'));
                }
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarAtividadesProcesso($dto));
              });
              $app->post('/lancar/andamento/processo', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiAtividadeRN();
                  $dto = $rn->encapsulaLancarAndamentoProcesso($request->getParsedBody());

                  return JsonResponse::create($response, $rn->lancarAndamentoProcesso($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de Assinante
           */
          $app->group('/assinante', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiAssinanteRN();
                  $dto = new AssinanteDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdAssinante($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrCargoFuncao($this->getParam($request, 'filter'));
                }
                  return JsonResponse::create($response, $rn->listarAssinante($dto));
              });

              $app->get('/orgao', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiOrgaoRN();
                  $dto = new OrgaoDTO();
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarOrgao($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de Grupo de Acompanhamento
           */
          $app->group('/grupoacompanhamento', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiGrupoAcompanhamentoRN();
                  $dto = new GrupoAcompanhamentoDTO();

                if (!empty($this->getParam($request, 'limit'))) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!empty($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!empty($this->getParam($request, 'id'))) {
                    $dto->setNumIdGrupoAcompanhamento($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrNome($this->getParam($request, 'filter'));
                }
                  return JsonResponse::create($response, $rn->listar($dto));
              });

              $app->post('/cadastrar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiGrupoAcompanhamentoRN();
                  $dto = new GrupoAcompanhamentoDTO();
                  $dto->setStrNome($this->getParam($request, 'nome'));
                  $dto->setNumIdGrupoAcompanhamento(null);
                  return JsonResponse::create($response, $rn->cadastrar($dto));
              });

              $app->post('/excluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiGrupoAcompanhamentoRN();
                  $arrIdGrupos = array();
                if ($this->getParam($request, 'grupos')) {
                    $arrIdGrupos = explode(',', $this->getParam($request, 'grupos'));
                }
                  return JsonResponse::create($response, $rn->excluir($arrIdGrupos));
              });

              $app->post('/{grupoacompanhamento:[0-9]+}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $grupoacompanhamento = $route->getArgument('grupoacompanhamento');
                  $rn = new MdWsSeiGrupoAcompanhamentoRN();
                  $dto = new GrupoAcompanhamentoDTO();
                  $dto->setNumIdGrupoAcompanhamento($grupoacompanhamento);
                  $dto->setStrNome($this->getParam($request, 'nome'));
                  return JsonResponse::create($response, $rn->alterar($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));
          /**
           * Grupo de controlador de Grupo de Modelo de documentos
           */
          $app->group('/protocolomodelo', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/grupo/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiGrupoProtocoloModeloRN();
                  $dto = new GrupoProtocoloModeloDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdGrupoProtocoloModelo($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrNome($this->getParam($request, 'filter'));
                }
                  return JsonResponse::create($response, $rn->listar($dto));
              });
              $app->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiProtocoloModeloRN();
                  $dto = new ProtocoloModeloDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdProtocoloModelo($this->getParam($request, 'id'));
                }
                if (!is_null($this->getParam($request, 'grupoProtocoloModelo')) && $this->getParam($request, 'grupoProtocoloModelo') != '') {
                    $dto->setNumIdGrupoProtocoloModelo($this->getParam($request, 'grupoProtocoloModelo'));
                }
                if (!is_null($this->getParam($request, 'tipoFiltro')) && $this->getParam($request, 'tipoFiltro') != '') {
                    $dto->setStrStaTipoFiltro($this->getParam($request, 'tipoFiltro'));
                }else{
                    $dto->setStrStaTipoFiltro(null);
                    // $dto->setStrStaTipoFiltro(ProtocoloModeloRN::$TF_TODOS);
                }
                  return JsonResponse::create($response, $rn->listar($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de Acompanhamento Especial
           */
          $app->group('/acompanhamentoespecial', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = new AcompanhamentoDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if ($this->getParam($request, 'grupoAcompanhamento') != '') {
                    $dto->setNumIdGrupoAcompanhamento($this->getParam($request, 'grupoAcompanhamento'));
                }
                  return JsonResponse::create($response, $rn->listaAcompanhamentosUnidade($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));


          /**
           * Grupo de controlador contato
           */
          $app->group('/contato', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */

                  $dto = new ContatoDTO();
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrPalavrasPesquisa($this->getParam($request, 'filter'));
                }
                if (!is_null($this->getParam($request, 'idGrupoContato')) && $this->getParam($request, 'idGrupoContato') != '') {
                    $dto->setNumIdGrupoContato($this->getParam($request, 'idGrupoContato'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdContato($this->getParam($request, 'id'));
                }
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }

                  $rn = new MdWsSeiContatoRN();
                  return JsonResponse::create($response, $rn->listarContato($dto));
              });

              $app->post('/criar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */

                  $dto = new MdWsSeiContatoDTO();


                  setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

                //   $nomeFormatado = iconv('UTF-8', 'ISO-8859-1', $this->getParam($request, 'nome'));
                  $nomeFormatado = mb_convert_encoding($this->getParam($request, 'nome'), 'ISO-8859-1', 'UTF-8');

                  $dto->setStrNome($nomeFormatado);

                  $rn = new MdWsSeiContatoRN();
                  return JsonResponse::create($response, $rn->criarContato($dto));
              });


          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador HipoteseLegal
           */
          $app->group('/hipoteseLegal', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */

                  $dto = new HipoteseLegalDTO();
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdHipoteseLegal($this->getParam($request, 'id'));
                }
                if (!is_null($this->getParam($request, 'nivelAcesso')) && $this->getParam($request, 'nivelAcesso') != '') {
                    $dto->setStrStaNivelAcesso($this->getParam($request, 'nivelAcesso'));
                }
                if (trim($this->getParam($request, 'filter')) != '') {
                    $dto->setStrNome($this->getParam($request, 'filter'));
                }
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }

                  $rn = new MdWsSeiHipoteseLegalRN();
                  return JsonResponse::create($response, $rn->pesquisar($dto));
              });
          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          /**
           * Grupo de controlador de Observação
           */
          $app->group('/observacao', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->post('/', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiObservacaoRN();
                  $dto = $rn->encapsulaObservacao($request->getParsedBody());
                  return JsonResponse::create($response, $rn->criarObservacao($dto));
              });

          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          $app->group('/serie', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/externo/pesquisar', function ($request, $response, $args) {
                  $dto = new SerieDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdSerie($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrNome($this->getParam($request, 'filter'));
                }
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiSerieRN();
                  return JsonResponse::create($response, $rn->pesquisarExterno($dto));
              });
          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          $app->group('/upload', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/parametros', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiExtensaoRN();
                  return JsonResponse::create($response, $rn->retornarParametrosUpload());
              });
          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

          $app->group('/marcador', function (\Slim\Routing\RouteCollectorProxy $app) {
              /** @var Slim/App $this */
              $app->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $dto = new MarcadorDTO();
                if (!is_null($this->getParam($request, 'limit')) && $this->getParam($request, 'limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start')) && $this->getParam($request, 'start') != '') {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                if (!is_null($this->getParam($request, 'id')) && $this->getParam($request, 'id') != '') {
                    $dto->setNumIdMarcador($this->getParam($request, 'id'));
                }
                if ($this->getParam($request, 'filter') != '') {
                    $dto->setStrNome($this->getParam($request, 'filter'));
                }
                if ($this->getParam($request, 'ativo') != '') {
                    $dto->setStrSinAtivo($this->getParam($request, 'ativo'));
                }
                  $rn = new MdWsSeiMarcadorRN();
                  return JsonResponse::create($response, $rn->pesquisar($dto));
              });
              $app->get('/cores/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  return JsonResponse::create($response, $rn->listarCores());
              });
              $app->post('/criar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $dto = new MarcadorDTO();
                  $dto->setStrNome($this->getParam($request, 'nome'));
                  $dto->setStrStaIcone($this->getParam($request, 'idCor'));
                  $rn = new MdWsSeiMarcadorRN();
                  return JsonResponse::create($response, $rn->cadastrar($dto));
              });
              $app->post('/{marcador:[0-9]+}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $marcador = $route->getArgument('marcador');
                  $dto = new MarcadorDTO();
                  $dto->setNumIdMarcador($marcador);
                  $dto->setStrNome($this->getParam($request, 'nome'));
                  $dto->setStrStaIcone($this->getParam($request, 'idCor'));
                  $rn = new MdWsSeiMarcadorRN();
                  return JsonResponse::create($response, $rn->alterar($dto));
              });
              $app->post('/excluir', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  $arrIdMarcadores = array();
                if ($this->getParam($request, 'marcadores')) {
                    $arrIdMarcadores = explode(',', $this->getParam($request, 'marcadores'));
                }
                  return JsonResponse::create($response, $rn->excluirMarcadores($arrIdMarcadores));
              });
              $app->post('/desativar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  $arrIdMarcadores = array();
                if ($this->getParam($request, 'marcadores')) {
                    $arrIdMarcadores = explode(',', $this->getParam($request, 'marcadores'));
                }
                  return JsonResponse::create($response, $rn->desativarMarcadores($arrIdMarcadores));
              });
              $app->post('/reativar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  $arrIdMarcadores = array();
                if ($this->getParam($request, 'marcadores')) {
                    $arrIdMarcadores = explode(',', $this->getParam($request, 'marcadores'));
                }
                  return JsonResponse::create($response, $rn->reativarMarcadores($arrIdMarcadores));
              });
              $app->post('/processo/{protocolo:[0-9]+}/marcar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiMarcadorRN();
                  $dto = new AndamentoMarcadorDTO();
                  $dto->setDblIdProcedimento(array($protocolo));
                  $dto->setNumIdMarcador($this->getParam($request, 'marcador'));
                  $dto->setStrTexto($this->getParam($request, 'texto'));
                  return JsonResponse::create($response, $rn->marcarProcesso($dto));
              });
              $app->get('/processo/{protocolo:[0-9]+}/consultar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiMarcadorRN();
                  $dto = new AndamentoMarcadorDTO();
                  $dto->setDblIdProcedimento($protocolo);
                  return JsonResponse::create($response, $rn->marcadorProcessoConsultar($dto));
              });
              $app->get('/processo/{protocolo:[0-9]+}/historico/listar', function ($request, $response, $args) {
                  /** @var Slim\Psr7\Request $request */
                  $routeContext = RouteContext::fromRequest($request);
                  $route = $routeContext->getRoute();
                  $protocolo = $route->getArgument('protocolo');
                  $rn = new MdWsSeiMarcadorRN();
                  $dto = new AndamentoMarcadorDTO();
                  $dto->setDblIdProcedimento($protocolo);
                if ($this->getParam($request, 'limit')) {
                    $dto->setNumMaxRegistrosRetorno($this->getParam($request, 'limit'));
                }
                if (!is_null($this->getParam($request, 'start'))) {
                    $dto->setNumPaginaAtual($this->getParam($request, 'start'));
                }
                  return JsonResponse::create($response, $rn->listarHistoricoProcesso($dto));
              });
          })->add(new TokenValidationMiddleware($this->slimApp->getResponseFactory()));

      })
          ->add(new ModuleVerificationMiddleware($this->slimApp->getResponseFactory()))
          ->add(new EncodingMiddleware($this->slimApp->getResponseFactory()));

      return $this->slimApp;
  }

  private function getParam(Slim\Psr7\Request $request, string $name)
{
    $query = $request->getQueryParams();

    if (isset($query[$name])) {
        return $query[$name];
    }

    $body = $request->getParsedBody();

    if (is_array($body) && isset($body[$name])) {
        return $body[$name];
    }
    // PARA GET não funciona o getParsedBody
    $body = $request->getBody()->getContents();
    $array = [];
    parse_str($body, $array);

    if (array_key_exists($name, $array)){
        return $array[$name];
    }
    
    return null;
}
  
}
final class JsonResponse
{
    public static function create(
        Slim\Psr7\Response $response,
        mixed $data,
        int $status = 200
    ): Slim\Psr7\Response {

        $response->getBody()->write(
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            )
        );

        return $response
            ->withStatus($status)
            ->withHeader(
                'Content-Type',
                'application/json'
            );
    }
}