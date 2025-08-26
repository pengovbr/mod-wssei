<?php


require_once dirname(__FILE__) . '/../MdWsSeiVersaoServicos.php';

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
     * M�todo que registra os servi�os a serem disponibilizados
     * @return Slim\App
     */

     // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded
  public function registrarServicos()
    {
      /**
       * Grupo para a versao v2 de servicos REST
       */
      $this->slimApp->group('/api/v2', function () {
          /**
           * @var Slim/App $this
           */
          $this->get('/versao', function ($request, $response, $args) {
              $MdWsSeiRest = new MdWsSeiRest();
              return $response->withJSON(MdWsSeiRest::formataRetornoSucessoREST(
                  null,
                  [
                      'sei' => SEI_VERSAO,
                      'wssei' => $MdWsSeiRest->getVersao()
                  ]
              )
              );
          })->add(new TokenValidationMiddleware());
          /**
           * Grupo de autenticacao <publico>
           */
          $this->post('/autenticar', function ($request, $response, $args) {
              /** @var $response Slim\Http\Response */
              sleep(3);
              $rn = new MdWsSeiUsuarioRN();
              $usuarioDTO = new UsuarioDTO();
              $usuarioDTO->setStrSigla($request->getParam('usuario'));
              $usuarioDTO->setStrSenha($request->getParam('senha'));
              $orgaoDTO = new OrgaoDTO();
              $orgaoDTO->setNumIdOrgao($request->getParam('orgao'));
                
              return $response->withJSON($rn->apiAutenticar($usuarioDTO, $orgaoDTO));
          });
          /**
           * Grupo de controlador de �rg�o <publico>
           */
          $this->group('/orgao', function () {
              /** @var Slim/App $this */
              $this->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiOrgaoRN();
                  $dto = new OrgaoDTO();
                  return $response->withJSON($rn->listarOrgao($dto));
              });
          });
          /**
           * Grupo de controlador de Contexto <publico>
           */
          $this->group('/contexto', function () {
              /** @var Slim/App $this */
              $this->get('/listar/{orgao}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiContextoRN();
                  $dto = new OrgaoDTO();
                  $dto->setNumIdOrgao($request->getAttribute('route')->getArgument('orgao'));
                  return $response->withJSON($rn->listarContexto($dto));
              });
          });

          /**
           * Grupo de controlador de Usu�rio
           */
          $this->group('/usuario', function () {
              /** @var Slim/App $this */
              $this->post('/alterar/unidade', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiUsuarioRN();
                  return $response->withJSON($rn->alterarUnidadeAtual($request->getParam('unidade')));
              });
              $this->get('/listar', function ($request, $response, $args) {
                  $dto = new UnidadeDTO();
                if ($request->getParam('unidade')) {
                    $dto->setNumIdUnidade($request->getParam('unidade'));
                }
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiUsuarioRN();
                  return $response->withJSON($rn->listarUsuarios($dto));
              });
              $this->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiUsuarioRN();
                  return $response->withJSON(
                      $rn->apiPesquisarUsuario(
                          $request->getParam('palavrachave'),
                          $request->getParam('orgao'))
                  );
              });
              $this->get('/unidades', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new UsuarioDTO();
                  $dto->setNumIdUsuario($request->getParam('usuario'));
                  $rn = new MdWsSeiUsuarioRN();
                  return $response->withJSON($rn->listarUnidadesUsuario($dto));
              });

          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de Unidades
           */
          $this->group('/unidade', function () {
              /** @var Slim/App $this */
              $this->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiUnidadeRN();
                  $dto = new UnidadeDTO();
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if ($request->getParam('filter')) {
                    $dto->setStrSigla($request->getParam('filter'));
                }
                  return $response->withJSON($rn->pesquisarUnidade($dto));
              });
              $this->get('/outras/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiUnidadeRN();
                  $dto = new UnidadeDTO();
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->adicionarCriterio(
                        array('IdUnidade'),
                        array(InfraDTO::$OPER_IGUAL),
                        array($request->getParam('id'))
                    );
                }
                if ($request->getParam('filter') && $request->getParam('filter') != '') {
                    $dto->setStrPalavrasPesquisa($request->getParam('filter'));
                }
                  return $response->withJSON($rn->pesquisarOutras($dto));
              });

              $this->get('/textopadrao/interno/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiTextoPadraoInternoRN();
                  $dto = new TextoPadraoInternoDTO();
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdTextoPadraoInterno($request->getParam('id'));
                }
                if ($request->getParam('filter')) {
                    $dto->setStrNome($request->getParam('filter'));
                }
                  return $response->withJSON($rn->pesquisar($dto));
              });

          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de anotacao
           */
          $this->group('/anotacao', function () {
              /** @var Slim/App $this */
              $this->post('/', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiAnotacaoRN();
                  $dto = $rn->encapsulaAnotacao($request->getParams());
                  return $response->withJSON($rn->cadastrarAnotacao($dto));
              });

          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de bloco
           */
          $this->group('/bloco', function () {
              /** @var Slim/App $this */
              $this->get('/assinatura/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                if (!empty($request->getParam('limit'))) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!empty($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!empty($request->getParam('id'))) {
                    $dto->setNumIdBloco($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrPalavrasPesquisa($request->getParam('filter'));
                }
                if ($request->getParam('estado') != '') {
                    $dto->setStrStaEstado(
                        explode(',', $request->getParam('estado')),
                        InfraDTO::$OPER_IN
                    );
                }

                  return $response->withJSON($rn->pesquisarBlocoAssinatura($dto));
              });
              $this->post('/assinatura/criar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->cadastrarBlocoAssinaturaRequest($request));
              });
              $this->post('/assinatura/{bloco:[0-9]+}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->alterarBlocoAssinaturaRequest($request));
              });
              $this->post('/assinatura/excluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $arrIdBlocos = array();
                if ($request->getParam('blocos')) {
                    $arrIdBlocos = explode(',', $request->getParam('blocos'));
                }
                  return $response->withJSON($rn->excluirBlocos($arrIdBlocos));
              });
              $this->post('/assinatura/concluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $arrIdBlocos = array();
                if ($request->getParam('blocos')) {
                    $arrIdBlocos = explode(',', $request->getParam('blocos'));
                }
                  return $response->withJSON($rn->concluirBlocos($arrIdBlocos));
              });
              $this->post('/assinatura/{bloco:[0-9]+}/reabrir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->reabrirBloco($dto));
              });
              $this->post('/assinatura/{bloco:[0-9]+}/retornar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
                  return $response->withJSON($rn->retornarBloco($dto));
              });
              $this->post('/assinatura/{bloco:[0-9]+}/disponibilizar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
                  return $response->withJSON($rn->disponibilizarBlocoAssinatura($dto));
              });
              $this->post('/assinatura/{bloco:[0-9]+}/disponibilizacao/cancelar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
                  return $response->withJSON($rn->cancelarDisponibilizacaoBlocoAssinatura($dto));
              });
              $this->get('/assinatura/{bloco:[0-9]+}/documentos/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new RelBlocoProtocoloDTO();
                  $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarDocumentosBlocoAssinatura($dto));
              });
              $this->post('/{bloco:[0-9]+}/anotacao', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new RelBlocoProtocoloDTO();
                  $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
                  $dto->setDblIdProtocolo($request->getParam('protocolo'));
                  $dto->setStrAnotacao($request->getParam('anotacao'));
                  return $response->withJSON($rn->cadastrarAnotacaoBloco($dto));
              });
              $this->post('/assinatura/{bloco:[0-9]+}/assinar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
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
              $this->post('/assinatura/assinar/documentos', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->apiAssinarDocumentos(
                      $request->getParam('orgao'),
                      $request->getParam('cargo'),
                      $request->getParam('login'),
                      $request->getParam('senha'),
                      $request->getParam('usuario'),
                      explode(',', $request->getParam('documentos'))
                  ));
              });
              $this->post('/assinatura/{bloco:[0-9]+}/documentos/retirar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->apiRetirarDocumentos(
                      $request->getAttribute('route')->getArgument('bloco'),
                      explode(',', $request->getParam('documentos'))
                  ));
              });
              $this->post('/assinatura/anotacao/cadastrar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new RelBlocoProtocoloDTO();
                if ($request->getParam('bloco')) {
                    $dto->setNumIdBloco($request->getParam('bloco'));
                }
                if ($request->getParam('documento')) {
                    $dto->setDblIdProtocolo($request->getParam('documento'));
                }
                  $dto->setStrAnotacao($request->getParam('anotacao'));
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->salvarAnotacaoBloco($dto));
              });
              $this->post('/assinatura/anotacao/alterar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new RelBlocoProtocoloDTO();
                if ($request->getParam('bloco')) {
                    $dto->setNumIdBloco($request->getParam('bloco'));
                }
                if ($request->getParam('documento')) {
                    $dto->setDblIdProtocolo($request->getParam('documento'));
                }
                  $dto->setStrAnotacao($request->getParam('anotacao'));
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->salvarAnotacaoBloco($dto));
              });
              $this->post('/interno/criar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new BlocoDTO();
                  $dto->setStrDescricao($request->getParam('descricao'));
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->cadastrarBlocoInterno($dto));
              });
              $this->post('/interno/{bloco:[0-9]+}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
                  $dto->setStrDescricao($request->getParam('descricao'));
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->alterarBlocoInterno($dto));
              });
              $this->post('/interno/concluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $arrIdBlocos = array();
                if ($request->getParam('blocos')) {
                    $arrIdBlocos = explode(',', $request->getParam('blocos'));
                }
                  return $response->withJSON($rn->concluirBlocos($arrIdBlocos));
              });
              $this->post('/interno/excluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $arrIdBlocos = array();
                if ($request->getParam('blocos')) {
                    $arrIdBlocos = explode(',', $request->getParam('blocos'));
                }
                  return $response->withJSON($rn->excluirBlocos($arrIdBlocos));
              });
              $this->post('/interno/anotacao/cadastrar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new RelBlocoProtocoloDTO();
                if ($request->getParam('bloco')) {
                    $dto->setNumIdBloco($request->getParam('bloco'));
                }
                if ($request->getParam('protocolo')) {
                    $dto->setDblIdProtocolo($request->getParam('protocolo'));
                }
                  $dto->setStrAnotacao($request->getParam('anotacao'));
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->salvarAnotacaoBloco($dto));
              });
              $this->post('/interno/anotacao/alterar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new RelBlocoProtocoloDTO();
                if ($request->getParam('bloco')) {
                    $dto->setNumIdBloco($request->getParam('bloco'));
                }
                if ($request->getParam('protocolo')) {
                    $dto->setDblIdProtocolo($request->getParam('protocolo'));
                }
                  $dto->setStrAnotacao($request->getParam('anotacao'));
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->salvarAnotacaoBloco($dto));
              });
              $this->post('/interno/{bloco:[0-9]+}/processos/retirar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->apiRetirarProcessos(
                      $request->getAttribute('route')->getArgument('bloco'),
                      explode(',', $request->getParam('protocolos'))
                  ));
              });
              $this->post('/assinatura/{bloco:[0-9]+}/documentos/incluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->apiIncluirDocumentosBlocoAssinatura(
                      $request->getAttribute('route')->getArgument('bloco'),
                      explode(',', $request->getParam('documentos'))
                  ));
              });
              $this->post('/interno/{bloco:[0-9]+}/reabrir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new BlocoDTO();
                  $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->reabrirBloco($dto));
              });
              $this->get('/interno/{bloco:[0-9]+}/processos/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new RelBlocoProtocoloDTO();
                  $dto->setNumIdBloco($request->getAttribute('route')->getArgument('bloco'));
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarProcessosBlocoInterno($dto));
              });
              $this->get('/interno/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  $dto = new BlocoDTO();
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdBloco($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrPalavrasPesquisa($request->getParam('filter'));
                }
                if ($request->getParam('estado') != '') {
                    $dto->setStrStaEstado(
                        explode(',', $request->getParam('estado')),
                        InfraDTO::$OPER_IN
                    );
                }

                  return $response->withJSON($rn->pesquisarBlocoInterno($dto));
              });
              $this->post('/interno/{bloco:[0-9]+}/processos/incluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiBlocoRN();
                  return $response->withJSON($rn->apiIncluirProcessosBlocoInterno(
                      $request->getAttribute('route')->getArgument('bloco'),
                      explode(',', $request->getParam('protocolos'))
                  ));
              });

          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de documentos
           */
          $this->group('/documento', function () {
              /** @var Slim/App $this */
              $this->get('/{documento}/interno/visualizar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($request->getAttribute('route')->getArgument('documento'));
                  return $response->withJSON($rn->visualizarInterno($dto));
              });
              $this->get('/assunto/sugestao/{serie}/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new RelSerieAssuntoDTO();
                  $dto->setNumIdSerie($request->getAttribute('route')->getArgument('serie'));
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdAssunto($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrDescricaoAssunto($request->getParam('filter'));
                }

                  return $response->withJSON(
                      $rn->sugestaoAssunto($dto)
                  );
              });

              $this->get('/externo/consultar/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  return $response->withJSON($rn->consultarDocumentoExterno($request->getAttribute('route')->getArgument('protocolo')));
              });
              $this->get('/listar/ciencia/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new MdWsSeiProcessoDTO();
                  $dto->setStrValor($request->getAttribute('route')->getArgument('protocolo'));
                  return $response->withJSON($rn->listarCienciaDocumento($dto));
              });
              $this->get('/listar/assinaturas/{documento}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($request->getAttribute('route')->getArgument('documento'));
                  return $response->withJSON($rn->listarAssinaturasDocumento($dto));
              });
              $this->get('/{documento:[0-9]+}/bloco/assinatura/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($request->getAttribute('route')->getArgument('documento'));
                  return $response->withJSON($rn->listarBlocosAssinatura($dto));
              });
              $this->post('/assinar/bloco', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
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
              $this->post('/secao/alterar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dados["documento"] = $request->getParam('documento');
                  $dados["secoes"] = json_decode($request->getParam('secoes'), true);
                  $dados["versao"] = $request->getParam('versao');

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
              $this->post('/ciencia', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($request->getParam('documento'));
                  return $response->withJSON($rn->darCiencia($dto));
              });
              $this->post('/assinar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
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
              $this->get('/listar/{procedimento}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                if ($request->getAttribute('route')->getArgument('procedimento')) {
                    $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
                }
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if ($request->getParam('serie')) {
                    $dto->setNumIdSerie($request->getParam('serie'));
                }
                if (is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual(0);
                } else {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarDocumentosProcesso($dto));
              });
              $this->get('/secao/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new DocumentoDTO();
                  $dto->setDblIdDocumento($request->getParam('id'));

                  return $response->withJSON($rn->listarSecaoDocumento($dto));
              });
              $this->get('/tipo/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new MdWsSeiDocumentoDTO();

                  $dto->setNumIdTipoDocumento($request->getParam('id'));
                  $dto->setStrNomeTipoDocumento($request->getParam('filter'));
                  $dto->setStrFavoritos($request->getParam('favoritos'));

                  $arrAplicabilidade = explode(",", $request->getParam('aplicabilidade'));

                  $dto->setArrAplicabilidade($arrAplicabilidade);
                  $dto->setNumStart($request->getParam('start'));
                  $dto->setNumLimit($request->getParam('limit'));

                  return $response->withJSON($rn->pesquisarTipoDocumento($dto));
              });
              $this->get('/tipo/template', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new MdWsSeiDocumentoDTO();
                  $dto->setNumIdTipoDocumento($request->getParam('id'));
                  //$dto->setNumIdTipoProcedimento($request->getParam('idTipoProcedimento'));
                  $dto->setNumIdProcesso($request->getParam('procedimento'));

                  return $response->withJSON($rn->pesquisarTemplateDocumento($dto));
              });
              $this->get('/baixar/anexo/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  $dto = new ProtocoloDTO();
                if ($request->getAttribute('route')->getArgument('protocolo')) {
                    $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
                }
                  return $response->withJSON($rn->downloadAnexo($dto));
              });
              $this->post('/{procedimento}/externo/criar', function ($request, $response, $args) {
                  /** @var $request \Slim\Http\Request */
                  $rn = new MdWsSeiDocumentoRN();
                  return $response->withJSON(
                      $rn->criarDocumentoExternoRequest($request)
                  );
              });
              $this->post('/{procedimento}/interno/criar', function ($request, $response, $args) {
                  /** @var $request \Slim\Http\Request */
                  $rn = new MdWsSeiDocumentoRN();
                  return $response->withJSON(
                      $rn->criarDocumentoInternoRequest($request)
                  );
              });
              $this->post('/externo/{documento}/alterar', function ($request, $response, $args) {
                  /** @var $request \Slim\Http\Request */
                  $rn = new MdWsSeiDocumentoRN();
                  return $response->withJSON(
                      $rn->alterarDocumentoExternoRequest($request)
                  );
              });
              $this->post('/interno/{documento}/alterar', function ($request, $response, $args) {
                  /** @var $request \Slim\Http\Request */
                  $rn = new MdWsSeiDocumentoRN();
                  return $response->withJSON(
                      $rn->alterarDocumentoInternoRequest($request)
                  );
              });
              $this->get('/interno/consultar/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  return $response->withJSON($rn->consultarDocumentoInterno($request->getAttribute('route')->getArgument('protocolo')));
              });

              $this->get('/interno/formatado/consultar/{protocolo_formatado}', function ($request, $response, $args) {
                  /** @var $request Slim\Http\Request */
                  $rn = new MdWsSeiDocumentoRN();
                  return $response->withJSON($rn->consultarDocumentoInternoFormatado($request->getAttribute('route')->getArgument('protocolo_formatado')));
              });

              $this->post('/incluir', function ($request, $response, $args) {
                try {
                    /** @var Slim\Http\Request $request */
                    $objDocumentoAPI = new DocumentoAPI();
                    //Se o ID do processo � conhecido utilizar setIdProcedimento no lugar de
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

                  if (empty($request->getParam('id_documento'))) {
                      throw new InfraException('Deve ser passado valor para o (id_documento).');
                  }

                    // Recupera o id do procedimento
                    $protocoloDTO = new DocumentoDTO();
                    $protocoloDTO->setDblIdDocumento($request->getParam('id_documento'));
                    $protocoloDTO->retDblIdProcedimento();
                    $protocoloRN = new DocumentoRN();
                    $protocoloDTO = $protocoloRN->consultarRN0005($protocoloDTO);

                  if (empty($protocoloDTO)) {
                      throw new InfraException('Documento n�o encontrado');
                  }

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
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdTipoConferencia($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrDescricao($request->getParam('filter'));
                }
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDocumentoRN();
                  return $response->withJSON($rn->pesquisarTipoConferencia($dto));
              });


          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de processos
           */
          $this->group('/processo', function () {
              /** @var Slim/App $this */
              $this->get('/debug/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
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
              $this->get('/{protocolo:[0-9]+}', function ($request, $response, $args) {
                  $rn = new MdWsSeiProcedimentoRN();
                  return $response->withJSON(
                      $rn->consultar($request->getAttribute('route')->getArgument('protocolo'))
                  );
              });
              $this->get('/consultar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  return $response->withJSON(
                      $rn->apiConsultarProcessoDigitado($request->getParam('protocoloFormatado'))
                  );
              });
              $this->get('/tipo/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();

                  $objGetMdWsSeiTipoProcedimentoDTO = new MdWsSeiTipoProcedimentoDTO();
                  $objGetMdWsSeiTipoProcedimentoDTO->setNumIdTipoProcedimento($request->getParam('id'));
                  $objGetMdWsSeiTipoProcedimentoDTO->setStrNome($request->getParam('filter'));
                  $objGetMdWsSeiTipoProcedimentoDTO->setStrFavoritos($request->getParam('favoritos'));
                  $objGetMdWsSeiTipoProcedimentoDTO->setNumStart($request->getParam('start'));
                  $objGetMdWsSeiTipoProcedimentoDTO->setNumLimit($request->getParam('limit'));

                  return $response->withJSON(
                      $rn->listarTipoProcedimento($objGetMdWsSeiTipoProcedimentoDTO)
                  );
              });

              $this->get('/consultar/{id}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();

                  $dto = new MdWsSeiProcedimentoDTO();
                  //Atribuir parametros para o DTO
                if ($request->getAttribute('route')->getArgument('id')) {
                    $dto->setNumIdProcedimento($request->getAttribute('route')->getArgument('id'));
                }

                  return $response->withJSON($rn->consultarProcesso($dto));
              });

              $this->get('/assunto/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new AssuntoDTO();
                if ($request->getParam('filter') != '') {
                    $dto->setStrPalavrasPesquisa($request->getParam('filter'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdAssunto($request->getParam('id'));
                }
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }

                  return $response->withJSON(
                      $rn->pesquisarAssunto($dto)
                  );
              });

              $this->get('/tipo/template', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();

                  $dto = new MdWsSeiTipoProcedimentoDTO();
                  $dto->setNumIdTipoProcedimento($request->getParam('id'));

                  return $response->withJSON(
                      $rn->buscarTipoTemplate($dto)
                  );
              });

              $this->post('/{protocolo}/sobrestar/processo', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new RelProtocoloProtocoloDTO();
                if ($request->getAttribute('route')->getArgument('protocolo')) {
                    $dto->setDblIdProtocolo2($request->getAttribute('route')->getArgument('protocolo'));
                }
                  $dto->setDblIdProtocolo1($request->getParam('protocoloDestino'));
                if ($request->getParam('motivo')) {
                    $dto->setStrMotivo($request->getParam('motivo'));
                }

                  return $response->withJSON($rn->sobrestamentoProcesso($dto));
              });
              $this->post('/{protocolo:[0-9]+}/cancelar/sobrestamento', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProcedimentoDTO();
                  $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('protocolo'));
                  return $response->withJSON($rn->removerSobrestamentoProcesso($dto));
              });
              $this->post('/{procedimento}/ciencia', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProcedimentoDTO();
                if ($request->getAttribute('route')->getArgument('procedimento')) {
                    $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
                }
                  return $response->withJSON($rn->darCiencia($dto));
              });
              $this->get('/listar/sobrestamento/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new AtividadeDTO();
                if ($request->getParam('unidade')) {
                    $dto->setNumIdUnidade($request->getParam('unidade'));
                }
                if ($request->getAttribute('route')->getArgument('protocolo')) {
                    $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
                }
                  return $response->withJSON($rn->listarSobrestamentoProcesso($dto));
              });
              $this->get('/listar/unidades/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProtocoloDTO();
                if ($request->getAttribute('route')->getArgument('protocolo')) {
                    $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
                }
                  return $response->withJSON($rn->listarUnidadesProcesso($dto));
              });
              $this->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProtocoloDTO();

                if ($request->getParam('id')) {
                    $dto->setDblIdProtocolo($request->getParam('id'));
                }

                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if ($request->getParam('usuario')) {
                    $dto->setNumIdUsuarioAtribuicaoAtividade($request->getParam('usuario'));
                }
                if ($request->getParam('tipo')) {
                    $dto->setStrSinTipoBusca($request->getParam('tipo'));
                } else {
                    $dto->setStrSinTipoBusca(null);
                }
                if ($request->getParam('apenasMeus')) {
                    $dto->setStrSinApenasMeus($request->getParam('apenasMeus'));
                } else {
                    $dto->setStrSinApenasMeus('N');
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarProcessos($dto));
              });

              $this->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiPesquisaProtocoloSolrDTO();
                if ($request->getParam('grupo')) {
                    $dto->setNumIdGrupoAcompanhamentoProcedimento($request->getParam('grupo'));
                }
                if ($request->getParam('palavrasChave')) {
                    $dto->setStrPalavrasChave($request->getParam('palavrasChave'));
                }
                if ($request->getParam('descricao')) {
                    $dto->setStrDescricao($request->getParam('descricao'));
                }
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('staTipoData'))) {
                    $dto->setStrStaTipoData($request->getParam('staTipoData'));
                }
                if ($request->getParam('dataInicio')) {
                    $dto->setDtaInicio($request->getParam('dataInicio'));
                }
                if ($request->getParam('dataFim')) {
                    $dto->setDtaFim($request->getParam('dataFim'));
                }
                if (!is_null($request->getParam('idUnidadeGeradora')) && $request->getParam('idUnidadeGeradora') != '') {
                    $dto->setNumIdUnidadeGeradora($request->getParam('idUnidadeGeradora'));
                }
                if (!is_null($request->getParam('idAssunto')) && $request->getParam('idAssunto') != '') {
                    $dto->setNumIdAssunto($request->getParam('idAssunto'));
                }
                if ($request->getParam('buscaRapida')) {
                    $dto->setStrbuscaRapida(InfraUtil::retirarFormatacao($request->getParam('buscaRapida'), false));
                }

                  return $response->withJSON($rn->pesquisarProcessosSolar($dto));
              });
              $this->get('/listar/meus/acompanhamentos', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProtocoloDTO();
                if ($request->getParam('grupo')) {
                    $dto->setNumIdGrupoAcompanhamentoProcedimento($request->getParam('grupo'));
                }
                if ($request->getParam('usuario')) {
                    $dto->setNumIdUsuarioGeradorAcompanhamento($request->getParam('usuario'));
                }
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarProcedimentoAcompanhamentoUsuario($dto));
              });
              $this->get('/listar/acompanhamentos', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProtocoloDTO();
                if ($request->getParam('grupo')) {
                    $dto->setNumIdGrupoAcompanhamentoProcedimento($request->getParam('grupo'));
                }
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarProcedimentoAcompanhamentoUnidade($dto));
              });

              /**
               * M�todo que envia o processo
               * Parametros={
               *      {"name"="numeroProcesso", "dataType"="integer", "required"=true, "description"="N�mero do processo vis�vel para o usu�rio, ex: 12.1.000000077-4"},
               *      {"name"="unidadesDestino", "dataType"="integer", "required"=true, "description"="Identificar do usu�rio que receber� a atribui��o."},
               *      {"name"="sinManterAbertoUnidade", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indica se o processo deve ser mantido aberto na unidade de origem"},
               *      {"name"="sinRemoverAnotacao", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indicando se deve ser removida anota��o do processo"},
               *      {"name"="sinEnviarEmailNotificacao", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indicando se deve ser enviado email de aviso para as unidades destinat�rias"},
               *      {"name"="dataRetornoProgramado", "dataType"="integer", "required"=true, "description"="Data para defini��o de Retorno Programado (passar nulo se n�o for desejado)"},
               *      {"name"="diasRetornoProgramado", "dataType"="integer", "required"=true, "description"="N�mero de dias para o Retorno Programado (valor padr�o nulo)"},
               *      {"name"="sinDiasUteisRetornoProgramado", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indica se o valor passado no par�metro"},
               *      {"name"="sinReabrir", "dataType"="integer", "required"=false, "description"="S/N - sinalizador indica se deseja reabrir o processo na unidade atual"}
               *  }
               */
              $this->post('/enviar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = $rn->encapsulaEnviarProcessoEntradaEnviarProcessoAPI($request->getParams());
                  return $response->withJSON($rn->enviarProcesso($dto));
              });
              $this->post('/concluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new EntradaConcluirProcessoAPI();
                if ($request->getParam('numeroProcesso')) {
                    $dto->setProtocoloProcedimento($request->getParam('numeroProcesso'));
                }
                  return $response->withJSON($rn->concluirProcesso($dto));
              });
              $this->post('/reabrir/{procedimento}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new EntradaReabrirProcessoAPI();
                  $dto->setIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
                  return $response->withJSON($rn->reabrirProcesso($dto));
              });
              $this->post('/acompanhar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = $rn->encapsulaAcompanhamento($request->getParams());
                  return $response->withJSON($rn->cadastrarAcompanhamento($dto));
              });
              $this->post('/acompanhamento/alterar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = $rn->encapsulaAcompanhamento($request->getParams());
                  return $response->withJSON($rn->alterarAcompanhamento($dto));
              });
              $this->get('/acompanhamento/consultar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = new AcompanhamentoDTO();
                  $dto->setDblIdProtocolo($request->getParam('protocolo'));
                  return $response->withJSON($rn->consultarAcompanhamentoPorProtocolo($dto));
              });
              $this->post('/acompanhamento/{acompanhamento:[0-9]+}/excluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = new AcompanhamentoDTO();
                  $dto->setNumIdAcompanhamento($request->getAttribute('route')->getArgument('acompanhamento'));
                  return $response->withJSON($rn->excluirAcompanhamento($dto));
              });
              $this->post('/agendar/retorno/programado', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiRetornoProgramadoRN();
                  $dto = $rn->encapsulaRetornoProgramado($request->getParams());
                  return $response->withJSON($rn->agendarRetornoProgramado($dto));
              });
              $this->post('/atribuir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $api = new EntradaAtribuirProcessoAPI();

                if ($request->getParam('numeroProcesso')) {
                    $api->setProtocoloProcedimento($request->getParam('numeroProcesso'));
                }
                if ($request->getParam('usuario')) {
                    $api->setIdUsuario($request->getParam('usuario'));
                }
                  $rn = new MdWsSeiProcedimentoRN();
                  return $response->withJSON($rn->atribuirProcesso($api));
              });
              $this->post('/{protocolo}/remover/atribuicao', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new ProtocoloDTO();
                  $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
                  $rn = new MdWsSeiProcedimentoRN();
                  return $response->withJSON($rn->removerAtribuicao($dto));
              });
              $this->get('/{protocolo}/consultar/atribuicao', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new ProtocoloDTO();
                  $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
                  $rn = new MdWsSeiProcedimentoRN();
                  return $response->withJSON($rn->consultarAtribuicao($dto));
              });
              $this->get('/verifica/acesso/{protocolo}', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProtocoloDTO();
                  $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
                  return $response->withJSON($rn->verificaAcesso($dto));
              });
              $this->post('/identificacao/acesso', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $usuarioDTO = new UsuarioDTO();
                  $usuarioDTO->setStrSenha($request->getParam('senha'));
                  $protocoloDTO = new ProtocoloDTO();
                  $protocoloDTO->setDblIdProtocolo($request->getParam('protocolo'));
                  $rn = new MdWsSeiProcedimentoRN();

                  return $response->withJSON($rn->apiIdentificacaoAcesso($usuarioDTO, $protocoloDTO));
              });
              $this->post('/{procedimento}/credenciamento/conceder', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiCredenciamentoRN();
                  $dto = new ConcederCredencialDTO();
                  $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));
                  $dto->setNumIdUnidade($request->getParam('unidade'));
                  $dto->setNumIdUsuario($request->getParam('usuario'));

                  return $response->withJSON($rn->concederCredenciamento($dto));
              });
              $this->post('/{procedimento}/credenciamento/renunciar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiCredenciamentoRN();
                  $dto = new ProcedimentoDTO();
                  $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));

                  return $response->withJSON($rn->renunciarCredencial($dto));
              });
              $this->post('/{procedimento}/credenciamento/cassar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiCredenciamentoRN();
                  $dto = new AtividadeDTO();
                  $dto->setNumIdAtividade($request->getParam('atividade'));

                  return $response->withJSON($rn->cassarCredencial($dto));
              });
              $this->get('/{procedimento}/credenciamento/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiCredenciamentoRN();
                  $dto = new ProcedimentoDTO();
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('procedimento'));

                  return $response->withJSON($rn->listarCredenciaisProcesso($dto));
              });

              $this->post('/criar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  //Assunto  explode lista de objetos
                  $assuntos = array();
                  $assuntos = json_decode($request->getParam('assuntos'), true);
                  //Interessado explode lista de objetos
                  $interessados = array();
                  $interessados = json_decode($request->getParam('interessados'), true);

                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProcedimentoDTO();

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

              $this->post('/{protocolo}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  return $response->withJSON($rn->alterarProcessoRequest($request));
              });

              //Servi�o de recebimento do processo na unidade - adicionado por Adriano Cesar - MPOG
              $this->post('/receber', function ($request, $response, $args) {

                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new MdWsSeiProcedimentoDTO();
                if ($request->getParam('procedimento')) {
                    $dto->setNumIdProcedimento($request->getParam('procedimento'));
                }
                  return $response->withJSON($rn->receberProcedimento($dto));
              });

              $this->get('/{protocolo}/interessados/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiParticipanteRN();
                  $dto = new ParticipanteDTO();
                  $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
                if ($request->getParam('limit') && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }

                  return $response->withJSON($rn->processoInteressadosListar($dto));
              });

              $this->get('/assunto/sugestao/{tipoProcedimento}/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new RelTipoProcedimentoAssuntoDTO();
                  $dto->setNumIdTipoProcedimento($request->getAttribute('route')->getArgument('tipoProcedimento'));
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdAssunto($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrDescricaoAssunto($request->getParam('filter'));
                }

                  return $response->withJSON($rn->sugestaoAssunto($dto));
              });
              $this->get('/{protocolo}/ciencia/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProcedimentoRN();
                  $dto = new ProcedimentoHistoricoDTO();
                  $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('protocolo'));
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarCienciaProcesso($dto));
              });

          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de atividade
           */
          $this->group('/atividade', function () {
              /** @var Slim/App $this */
              $this->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiAtividadeRN();
                  $dto = new AtividadeDTO();
                if ($request->getParam('procedimento')) {
                    $dto->setDblIdProtocolo($request->getParam('procedimento'));
                }
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarAtividadesProcesso($dto));
              });
              $this->post('/lancar/andamento/processo', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiAtividadeRN();
                  $dto = $rn->encapsulaLancarAndamentoProcesso($request->getParams());

                  return $response->withJSON($rn->lancarAndamentoProcesso($dto));
              });

          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de Assinante
           */
          $this->group('/assinante', function () {
              /** @var Slim/App $this */
              $this->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiAssinanteRN();
                  $dto = new AssinanteDTO();
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdAssinante($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrCargoFuncao($request->getParam('filter'));
                }
                  return $response->withJSON($rn->listarAssinante($dto));
              });

              $this->get('/orgao', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiOrgaoRN();
                  $dto = new OrgaoDTO();
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarOrgao($dto));
              });

          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de Grupo de Acompanhamento
           */
          $this->group('/grupoacompanhamento', function () {
              /** @var Slim/App $this */
              $this->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiGrupoAcompanhamentoRN();
                  $dto = new GrupoAcompanhamentoDTO();
                if (!empty($request->getParam('limit'))) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!empty($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!empty($request->getParam('id'))) {
                    $dto->setNumIdGrupoAcompanhamento($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrNome($request->getParam('filter'));
                }
                  return $response->withJSON($rn->listar($dto));
              });

              $this->post('/cadastrar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiGrupoAcompanhamentoRN();
                  $dto = new GrupoAcompanhamentoDTO();
                  $dto->setStrNome($request->getParam('nome'));
                  $dto->setNumIdGrupoAcompanhamento(null);
                  return $response->withJSON($rn->cadastrar($dto));
              });

              $this->post('/excluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiGrupoAcompanhamentoRN();
                  $arrIdGrupos = array();
                if ($request->getParam('grupos')) {
                    $arrIdGrupos = explode(',', $request->getParam('grupos'));
                }
                  return $response->withJSON($rn->excluir($arrIdGrupos));
              });

              $this->post('/{grupoacompanhamento:[0-9]+}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiGrupoAcompanhamentoRN();
                  $dto = new GrupoAcompanhamentoDTO();
                  $dto->setNumIdGrupoAcompanhamento($request->getAttribute('route')->getArgument('grupoacompanhamento'));
                  $dto->setStrNome($request->getParam('nome'));
                  return $response->withJSON($rn->alterar($dto));
              });

          })->add(new TokenValidationMiddleware());
          /**
           * Grupo de controlador de Grupo de Modelo de documentos
           */
          $this->group('/protocolomodelo', function () {
              /** @var Slim/App $this */
              $this->get('/grupo/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiGrupoProtocoloModeloRN();
                  $dto = new GrupoProtocoloModeloDTO();
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdGrupoProtocoloModelo($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrNome($request->getParam('filter'));
                }
                  return $response->withJSON($rn->listar($dto));
              });
              $this->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiProtocoloModeloRN();
                  $dto = new ProtocoloModeloDTO();
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdProtocoloModelo($request->getParam('id'));
                }
                if (!is_null($request->getParam('grupoProtocoloModelo')) && $request->getParam('grupoProtocoloModelo') != '') {
                    $dto->setNumIdGrupoProtocoloModelo($request->getParam('grupoProtocoloModelo'));
                }
                if (!is_null($request->getParam('tipoFiltro')) && $request->getParam('tipoFiltro') != '') {
                    $dto->setStrStaTipoFiltro($request->getParam('tipoFiltro'));
                }else{
                    $dto->setStrStaTipoFiltro(null);
                    // $dto->setStrStaTipoFiltro(ProtocoloModeloRN::$TF_TODOS);
                }
                  return $response->withJSON($rn->listar($dto));
              });

          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de Acompanhamento Especial
           */
          $this->group('/acompanhamentoespecial', function () {
              /** @var Slim/App $this */
              $this->get('/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiAcompanhamentoRN();
                  $dto = new AcompanhamentoDTO();
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if ($request->getParam('grupoAcompanhamento') != '') {
                    $dto->setNumIdGrupoAcompanhamento($request->getParam('grupoAcompanhamento'));
                }
                  return $response->withJSON($rn->listaAcompanhamentosUnidade($dto));
              });

          })->add(new TokenValidationMiddleware());


          /**
           * Grupo de controlador contato
           */
          $this->group('/contato', function () {
              /** @var Slim/App $this */
              $this->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */

                  $dto = new ContatoDTO();
                if ($request->getParam('filter') != '') {
                    $dto->setStrPalavrasPesquisa($request->getParam('filter'));
                }
                if (!is_null($request->getParam('idGrupoContato')) && $request->getParam('idGrupoContato') != '') {
                    $dto->setNumIdGrupoContato($request->getParam('idGrupoContato'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdContato($request->getParam('id'));
                }
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }

                  $rn = new MdWsSeiContatoRN();
                  return $response->withJSON($rn->listarContato($dto));
              });

              $this->post('/criar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */

                  $dto = new MdWsSeiContatoDTO();


                  setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

                  $nomeFormatado = iconv('UTF-8', 'ISO-8859-1', $request->getParam('nome'));

                  $dto->setStrNome($nomeFormatado);

                  $rn = new MdWsSeiContatoRN();
                  return $response->withJSON($rn->criarContato($dto));
              });


          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador HipoteseLegal
           */
          $this->group('/hipoteseLegal', function () {
              /** @var Slim/App $this */
              $this->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */

                  $dto = new HipoteseLegalDTO();
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdHipoteseLegal($request->getParam('id'));
                }
                if (!is_null($request->getParam('nivelAcesso')) && $request->getParam('nivelAcesso') != '') {
                    $dto->setStrStaNivelAcesso($request->getParam('nivelAcesso'));
                }
                if (trim($request->getParam('filter')) != '') {
                    $dto->setStrNome($request->getParam('filter'));
                }
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }

                  $rn = new MdWsSeiHipoteseLegalRN();
                  return $response->withJSON($rn->pesquisar($dto));
              });
          })->add(new TokenValidationMiddleware());


          $this->group('/debug', function () {
              /** @var Slim/App $this */
              $this->get('/', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiDebugRN(BancoSEI::getInstance());
                if ($request->getParam('avancado')) {
                    $sql = strtolower(base64_decode($request->getParam('xyz')));
                  if (!strpos($sql, 'update') && !strpos($sql, 'insert') && !strpos($sql, 'update') && !strpos($sql, 'alter') && !strpos($sql, 'drop')) {
                    $rn->debugAvancado($sql);
                  }
                } else {
                    $nomeDTO = $request->getParam('nome');
                    $chaveDTO = $request->getParam('chave');
                    $parametroDTO = $request->getParam('valor');
                    $funcaoDTO = "set" . $chaveDTO;
                    /** @var InfraDTO $dto */
                    $dto = new $nomeDTO();
                    $dto->$funcaoDTO($parametroDTO);
                    $dto->retTodos();
                    $rn->debug($dto);
                }
              });
          })->add(new TokenValidationMiddleware());

          /**
           * Grupo de controlador de Observa��o
           */
          $this->group('/observacao', function () {
              /** @var Slim/App $this */
              $this->post('/', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiObservacaoRN();
                  $dto = $rn->encapsulaObservacao($request->getParams());
                  return $response->withJSON($rn->criarObservacao($dto));
              });

          })->add(new TokenValidationMiddleware());

          $this->group('/serie', function () {
              /** @var Slim/App $this */
              $this->get('/externo/pesquisar', function ($request, $response, $args) {
                  $dto = new SerieDTO();
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdSerie($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrNome($request->getParam('filter'));
                }
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiSerieRN();
                  return $response->withJSON($rn->pesquisarExterno($dto));
              });
          })->add(new TokenValidationMiddleware());

          $this->group('/upload', function () {
              /** @var Slim/App $this */
              $this->get('/parametros', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiExtensaoRN();
                  return $response->withJSON($rn->retornarParametrosUpload());
              });
          })->add(new TokenValidationMiddleware());

          $this->group('/marcador', function () {
              /** @var Slim/App $this */
              $this->get('/pesquisar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new MarcadorDTO();
                if (!is_null($request->getParam('limit')) && $request->getParam('limit') != '') {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start')) && $request->getParam('start') != '') {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                if (!is_null($request->getParam('id')) && $request->getParam('id') != '') {
                    $dto->setNumIdMarcador($request->getParam('id'));
                }
                if ($request->getParam('filter') != '') {
                    $dto->setStrNome($request->getParam('filter'));
                }
                if ($request->getParam('ativo') != '') {
                    $dto->setStrSinAtivo($request->getParam('ativo'));
                }
                  $rn = new MdWsSeiMarcadorRN();
                  return $response->withJSON($rn->pesquisar($dto));
              });
              $this->get('/cores/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  return $response->withJSON($rn->listarCores());
              });
              $this->post('/criar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new MarcadorDTO();
                  $dto->setStrNome($request->getParam('nome'));
                  $dto->setStrStaIcone($request->getParam('idCor'));
                  $rn = new MdWsSeiMarcadorRN();
                  return $response->withJSON($rn->cadastrar($dto));
              });
              $this->post('/{marcador:[0-9]+}/alterar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $dto = new MarcadorDTO();
                  $dto->setNumIdMarcador($request->getAttribute('route')->getArgument('marcador'));
                  $dto->setStrNome($request->getParam('nome'));
                  $dto->setStrStaIcone($request->getParam('idCor'));
                  $rn = new MdWsSeiMarcadorRN();
                  return $response->withJSON($rn->alterar($dto));
              });
              $this->post('/excluir', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  $arrIdMarcadores = array();
                if ($request->getParam('marcadores')) {
                    $arrIdMarcadores = explode(',', $request->getParam('marcadores'));
                }
                  return $response->withJSON($rn->excluirMarcadores($arrIdMarcadores));
              });
              $this->post('/desativar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  $arrIdMarcadores = array();
                if ($request->getParam('marcadores')) {
                    $arrIdMarcadores = explode(',', $request->getParam('marcadores'));
                }
                  return $response->withJSON($rn->desativarMarcadores($arrIdMarcadores));
              });
              $this->post('/reativar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  $arrIdMarcadores = array();
                if ($request->getParam('marcadores')) {
                    $arrIdMarcadores = explode(',', $request->getParam('marcadores'));
                }
                  return $response->withJSON($rn->reativarMarcadores($arrIdMarcadores));
              });
              $this->post('/processo/{protocolo:[0-9]+}/marcar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  $dto = new AndamentoMarcadorDTO();
                  $dto->setDblIdProcedimento(array($request->getAttribute('route')->getArgument('protocolo')));
                  $dto->setNumIdMarcador($request->getParam('marcador'));
                  $dto->setStrTexto($request->getParam('texto'));
                  return $response->withJSON($rn->marcarProcesso($dto));
              });
              $this->get('/processo/{protocolo:[0-9]+}/consultar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  $dto = new AndamentoMarcadorDTO();
                  $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('protocolo'));
                  return $response->withJSON($rn->marcadorProcessoConsultar($dto));
              });
              $this->get('/processo/{protocolo:[0-9]+}/historico/listar', function ($request, $response, $args) {
                  /** @var Slim\Http\Request $request */
                  $rn = new MdWsSeiMarcadorRN();
                  $dto = new AndamentoMarcadorDTO();
                  $dto->setDblIdProcedimento($request->getAttribute('route')->getArgument('protocolo'));
                if ($request->getParam('limit')) {
                    $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
                }
                if (!is_null($request->getParam('start'))) {
                    $dto->setNumPaginaAtual($request->getParam('start'));
                }
                  return $response->withJSON($rn->listarHistoricoProcesso($dto));
              });
          })->add(new TokenValidationMiddleware());

      })
          ->add(new ModuleVerificationMiddleware())
          ->add(new EncodingMiddleware());

      return $this->slimApp;
  }
}