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


$config = array(
    'settings' => array(
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => true
    )
);

$app = new \Slim\App($config);

//Enable CORS
// $app->options('/{routes:.+}', function ($request, $response, $args) {
//     return $response;
// });

// $app->add(function ($req, $res, $next) {
//     $response = $next($req, $res);

//     //cabeçalhos encontrados na implementação do Mobile
//     $strAllowHeaders = 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Access-Control-Max-Age, If-Modified-Since' .
//         'token, User-Agent, Cookie, Content-Disposition, Content-Length, Transfer-Encoding, Accept-Encoding';

//     return $response->withHeader('Access-Control-Allow-Origin', 'http://localhost:8100') //Especifico para o IONIC
//                     ->withHeader('Access-Control-Allow-Headers', $strAllowHeaders)
//                     ->withHeader('Access-Control-Allow-Credentials', 'true')
//                     ->withHeader('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS, HEAD');
// });

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
        $contextoDTO->setNumIdOrgao($request->getParam('orgao'));

        return $response->withJSON($rn->apiAutenticar($usuarioDTO, $contextoDTO));
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
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiUsuarioRN();
            return $response->withJSON($rn->listarUsuarios($dto));
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
        $this->get('/baixar/anexo/{protocolo}', function($request, $response, $args){
            /** @var $request Slim\Http\Request */
            $rn = new MdWsSeiDocumentoRN();
            $dto = new ProtocoloDTO();
            if($request->getAttribute('route')->getArgument('protocolo')){
                $dto->setDblIdProtocolo($request->getAttribute('route')->getArgument('protocolo'));
            }
            return $response->withJSON($rn->downloadAnexo($dto));
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
            $dto = new MdWsSeiProtocoloDTO();
            if($request->getParam('grupo')){
                $dto->setNumIdGrupoAcompanhamentoProcedimento($request->getParam('grupo'));
            }
            if($request->getParam('protocoloPesquisa')){
                $dto->setStrProtocoloFormatadoPesquisa($request->getParam('protocoloPesquisa'));
            }
            if($request->getParam('limit')){
                $dto->setNumMaxRegistrosRetorno($request->getParam('limit'));
            }
            if(!is_null($request->getParam('start'))){
                $dto->setNumPaginaAtual($request->getParam('start'));
            }
            return $response->withJSON($rn->pesquisarProcedimento($dto));
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
            return $response->withJSON($rn->listarProcedimentoAcompanhamento($dto));
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
            return $response->withJSON($rn->identificacaoAcesso($usuarioDTO, $protocoloDTO));
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
});
$app->run();
