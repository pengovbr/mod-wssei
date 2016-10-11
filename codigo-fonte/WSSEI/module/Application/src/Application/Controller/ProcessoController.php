<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Entity\Acompanhamento;
use Application\Service\Exception\BaseException;
use Application\Service\Processo;
use Zend\View\Model\ViewModel;
use Doctrine\Common\Util\Debug;

class ProcessoController extends AbstractActionController
{
    /**
     *
     * Get Route annotation.
     * @Post("/processo/acompanhar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="protocolo", "dataType"="integer", "required"=true, "description"="Identificador do Protocolo"},
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Unidade Organizacaional"},
     *      {"name"="grupo", "dataType"="string", "required"=true, "description"="Identificador do grupo"},
     *      {"name"="usuario", "dataType"="integer", "required"=true, "description"="Identificador do usuário"},
     *      {"name"="observacao", "dataType"="integer", "required"=true, "description"="Observação/Descrição do Acompanhamento"}
     *  }
     * )
     */
    public function postAcompanharAction () {
        $request = $this->getRequest();
        $post = $request->getPost();

        try {
            $service = $this->getServiceLocator()->get('acompanhamento');
            $serviceProtocolo = $this->getServiceLocator()->get('protocolo');
            $serviceUnidade = $this->getServiceLocator()->get('unidade');
            $serviceGrupo = $this->getServiceLocator()->get('grupoAcompanhamento');

            $acompanhamento = new Acompanhamento();

            if (isset($post['protocolo']))
                $acompanhamento->setIdProtocolo(
                    $serviceProtocolo->pesquisarProtocoloPorID($post['protocolo'])
                );

            if (isset($post['unidade']))
                $acompanhamento->setIdUnidade(
                    $serviceUnidade->pesquisarUnidadePorID($post['unidade'])
                );

            if (isset($post['grupo']))
                $acompanhamento->setIdGrupoAcompanhamento(
                    $serviceGrupo->pesquisarGrupoAcompanhamentoPorID($post['grupo'])
                );

            if (isset($post['usuario']))
                $acompanhamento->setIdUsuarioGerador($post['usuario']);

            if (isset($post['observacao']))
                $acompanhamento->setObservacao($post['observacao']);

            $service->criarAcompanhamentoProcesso($acompanhamento);

            return $this->sendJson(array(), 'acompanhamento realizado com sucesso');
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/listar/acompanhamento/usuario/{usuario}")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="grupo", "dataType"="integer", "required"=true, "description"="Identificador do grupo de acompanhamento"},
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="page", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"}
     *  }
     * )
     */
    public function getProcessoAcompanhamentoAction () {
        $request = $this->params();

        $page = ($request->fromQuery('start'));
        $service = $this->getServiceLocator()->get('processo');

        return $this->sendJson($service->pesquisarProcessoAcompanhamento(
            $request->fromQuery('usuario'),
            $request->fromQuery('grupo'),
            $request->fromQuery('limit'),
            (($page < 0)? 0 : $page)
        ));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/listar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Unidade Organizacaional"},
     *      {"name"="tipo", "dataType"="string", "required"=true, "description"="Identificador do tipo de consulta (G - Gerados, M - Meus, R - Recebidos)"},
     *      {"name"="usuario", "dataType"="integer", "required"=true, "description"="Identificador do usuário"},
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="page", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"},
     *  }
     * )
     */
    public function getListaProcessoAction () {
        $request = $this->params();

        $page = ($request->fromQuery('start'));
        /** @var Processo $service */
        $service = $this->getServiceLocator()->get('processo');

        return $this->sendJson($service->listarProcessos(
            $request->fromQuery('unidade'),
            $request->fromQuery('tipo'),
            $request->fromQuery('usuario'),
            $request->fromQuery('limit'),
            (($page < 0)? 0 : $page)
        ));
    }

    /**
     *
     * Post Route annotation.
     * @Get("/processo/pesquisar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="processo", "dataType"="string", "required"=true, "description"="Número do Processo"},
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="page", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"},
     *  }
     * )
     */
    public function getPesquisarProcessoAction () {
        $request = $this->params();

        $page = ($request->fromQuery('start'));
        $service = $this->getServiceLocator()->get('processo');

        return $this->sendJson($service->pesquisarProcesso(
            $request->fromQuery('processo'),
            $request->fromQuery('idProcesso'),
            $request->fromQuery('limit'),
            (($page < 0)? 0 : $page)
        ));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/{procedimento}/listar/documentos")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="offset", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"},
     *  }
     * )
     */
    public function getListarDocumentosAction () {
        $request = $this->params();

        $page = ($request->fromQuery('start'));
        $service = $this->getServiceLocator()->get('documento');

        return $this->sendJson($service->listarDocumentos(
            $request->fromQuery('procedimento'),
            $request->fromQuery('limit'),
            (($page < 0)? 0 : $page)
        ));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/anexo/{extensao}")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     * )
     */
    public function getDownloadAnexoAction () {
        $request = $this->params();
        $service = $this->getServiceLocator()->get('documento');

        return $this->sendJson($service->downloadAnexo($request->fromQuery('protocolo')));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/{procedimento}/unidade/{unidade}/listar/atividades")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="offset", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"},
     *  }
     * )
     */
    public function getListarAtividadesAction () {
        $request = $this->params();

        $page = ($request->fromQuery('start'));
        $service = $this->getServiceLocator()->get('processo');

        return $this->sendJson($service->listarAtividadeProcesso(
            $request->fromQuery('procedimento'),
            $request->fromQuery('unidade'),
            $request->fromQuery('limit'),
            (($page < 0)? 0 : $page)
        ));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/unidade/listar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     * )
     */
    public function getListarUnidadesAction ()
    {
        $request = $this->params();
        $page = ($request->fromQuery('start'));

        $service = $this->getServiceLocator()->get('unidade');

        return $this->sendJson(array (
            "data" => $service->listarUnidades(
                    $request->fromQuery('filter'),
                    $request->fromQuery('limit'),
                    (($page < 0)? 0 : $page)
                )
        ));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/cargo/funcao/listar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Identificador unidade organizacional"},
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="offset", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"},
     *  }
     * )
     */
    public function getListarCargoFuncaoAction () {
        $request = $this->params();
        $page = ($request->fromQuery('start'));
        $service = $this->getServiceLocator()->get('cargoFuncao');

        return $this->sendJson($service->listarCargoFuncao(
            $request->fromQuery('unidade'),
            $request->fromQuery('limit'),
            (($page < 0)? 0 : $page)
        ));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/orgao/listar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="offset", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"},
     *  }
     * )
     */
    public function getListarOrgaoAction () {
        $request = $this->params();

        $page = ($request->fromQuery('start'));
        $service = $this->getServiceLocator()->get('orgao');

        return $this->sendJson($service->listarOrgao(
            $request->fromQuery('limit'),
            (($page < 0)? 0 : $page)
        ));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/ciencia/listar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="offset", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"},
     *  }
     * )
     */
    public function getListarCienciaAction () {
        $request = $this->params();
        $protocolo = $request->fromQuery('protocolo');

        $service = $this->getServiceLocator()->get('processo');

        return $this->sendJson($service->listarCienciaProcesso($protocolo));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/processo/grupo/listar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Identificador Unidade Organizacional"},
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="offset", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"},
     *  }
     * )
     */
    public function getListarGrupoAcompanhamentoAction () {
        $request = $this->params();

        $page = ($request->fromQuery('start'));
        $service = $this->getServiceLocator()->get('grupoAcompanhamento');

        return $this->sendJson($service->listarGrupoAcompanhamento(
            $request->fromQuery('unidade'),
            $request->fromQuery('limit'),
            (($page < 0)? 0 : $page)
        ));
    }

    /**
     *
     * Get Route annotation.
     * @Post("/processo/ciencia/procedimento/{procedimento}")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     * )
     */
    public function postCienciaProcessoAction () {
        try {
            $request = $this->params();

            $service = $this->getServiceLocator()->get('processo');

            $unidade = $request->fromPost('unidade');
            $usuario = $request->fromPost('usuario');
            $procedimento = $request->fromPost('procedimento');

            $serviceUnidade = $this->getServiceLocator()->get('unidade');
            $serviceProtocolo = $this->getServiceLocator()->get('protocolo');
            $serviceAtividade = $this->getServiceLocator()->get('atividade');

            $service->cienciaProcesso($procedimento, $unidade, $usuario, $serviceUnidade, $serviceProtocolo, $serviceAtividade);

            return $this->sendJson(array(), 'Ciência processo realizado com sucesso');
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    /**
     *
     * Get Route annotation.
     * @Post("/processo/retornoProgramado/agendar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *  parameters={
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Identificador Unidade Organizacional"},
     *      {"name"="usuario", "dataType"="integer", "required"=true, "description"="Identificador do usuário logado"},
     *      {"name"="atividadeEnvio", "dataType"="integer", "required"=true, "description"="Identificador da atividade"},
     *      {"name"="dtProgramada", "dataType"="dateTime", "required"=true, "description"="Data Programada"},
     *  }
     * )
     */
    public function postAgendarRetornoProgramadoAction () {
        $request = $this->getRequest();

        $post = $request->getPost();

        try {
            $service = $this->getServiceLocator()->get('retornoProgramado');
            $serviceUsuario = $this->getServiceLocator()->get('usuario');
            $serviceAtividade = $this->getServiceLocator()->get('atividade');
            $serviceUnidade = $this->getServiceLocator()->get('unidade');

            $service->agendarRetornoProgramado($post, $serviceUsuario, $serviceAtividade, $serviceUnidade);

            return $this->sendJson(array(), 'Retorno Programado agendado com sucesso');
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    /**
     *
     * Get Route annotation.
     * @Post("/processo/andamento/cadastrar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *  parameters={
     *      {"name"="protocolo", "dataType"="integer", "required"=true, "description"="Identificador do Processo"},
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Identificador Unidade Organizacional"},
     *      {"name"="descricao", "dataType"="text", "required"=true, "description"="Descrição da observacao"},
     *  }
     * )
     */
    public function postCadastrarObservacaoAction () {
        $request = $this->getRequest();

        $post = $request->getPost();

        try {
            $service = $this->getServiceLocator()->get('observacao');
            $serviceUnidade = $this->getServiceLocator()->get('unidade');
            $serviceProtocolo = $this->getServiceLocator()->get('protocolo');
            $serviceAtividade = $this->getServiceLocator()->get('atividade');

            $service->criarObservacao($post, $serviceUnidade, $serviceProtocolo, $serviceAtividade);

            return $this->sendJson(array(), 'Observação cadastrada com sucesso');
        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    /**
     *
     * Get Route annotation.
     * @Post("/processo/anotacao/cadastrar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *  parameters={
     *      {"name"="protocolo", "dataType"="integer", "required"=true, "description"="Identificador do Processo"},
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Identificador Unidade Organizacional"},
     *      {"name"="descricao", "dataType"="text", "required"=true, "description"="Descrição da observacao"},
     *  }
     * )
     */
    public function postCadastrarAnotacaoAction () {
        $request = $this->getRequest();

        $post = $request->getPost();

        try {
            $service = $this->getServiceLocator()->get('observacao');
            $serviceUnidade = $this->getServiceLocator()->get('unidade');
            $serviceProtocolo = $this->getServiceLocator()->get('protocolo');

            $service->criarObservacao($post, $serviceUnidade, $serviceProtocolo);

            return $this->sendJson(array(), 'Anotação cadastrada com sucesso');
        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    /**
     *
     * Get Route annotation.
     * @Post("/processo/concluir")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *  parameters={
     *      {"name"="numero", "dataType"="integer", "required"=true, "description"="Número do processo"},
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Identificador Unidade Organizacional"}
     *  }
     * )
     */
    public function postConcluirProcessoAction () {
        $request = $this->getRequest();

        $post = $request->getPost();

        try {
            $service = $this->getServiceLocator()->get('processo');

            $result = $service->concluirProcesso($post);

            if ($result) {
                return $this->sendJson(array(), 'Processo concluído com sucesso');
            }

            return $this->sendJson(array(), 'Não foi possível concluir o processo');

        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    /**
     *
     * Get Route annotation.
     * @Post("/processo/atribuir")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *  parameters={
     *      {"name"="numero", "dataType"="integer", "required"=true, "description"="Número do processo"},
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Identificador Unidade Organizacional"},
     *      {"name"="usuario", "dataType"="integer", "required"=true, "description"="Identificar do usuário que receberá a atribuição."},
     *      {"name"="reabrir", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indicando se o processo deve ser reaberto automaticamente caso esteja concluído na unidade."}
     *  }
     * )
     */
    public function postAtribuirProcessoAction () {
        $request = $this->getRequest();

        $post = $request->getPost();

        try {
            $service = $this->getServiceLocator()->get('processo');

            $result = $service->atribuirProcesso($post);

            if ($result) {
                return $this->sendJson(array(), 'Processo atribuído com sucesso');
            }

            return $this->sendJson(array(), 'Não foi possível atribuir o processo');

        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    /**
     *
     * Get Route annotation.
     * @Post("/processo/enviar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *  parameters={
     *      {"name"="numero", "dataType"="integer", "required"=true, "description"="Número do processo visível para o usuário, ex: 12.1.000000077-4"},
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Valor informado no cadastro do serviço realizado no SEI"},
     *      {"name"="UnidadesDestino", "dataType"="integer", "required"=true, "description"="Identificar do usuário que receberá a atribuição."},
     *      {"name"="SinManterAbertoUnidade", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indica se o processo deve ser mantido aberto na unidade de origem"},
     *      {"name"="SinRemoverAnotacao", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indicando se deve ser removida anotação do processo"},
     *      {"name"="SinEnviarEmailNotificacao", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indicando se deve ser enviado email de aviso para as unidades destinatárias"},
     *      {"name"="DataRetornoProgramado", "dataType"="integer", "required"=true, "description"="Data para definição de Retorno Programado (passar nulo se não for desejado)"},
     *      {"name"="DiasRetornoProgramado", "dataType"="integer", "required"=true, "description"="Número de dias para o Retorno Programado (valor padrão nulo)"},
     *      {"name"="SinDiasUteisRetornoProgramado", "dataType"="integer", "required"=true, "description"="S/N - sinalizador indica se o valor passado no parâmetro"},
     *  }
     * )
     */
    public function postEnviarProcessoAction () {
        $request = $this->getRequest();

        $post = $request->getPost();

        try {
            $service = $this->getServiceLocator()->get('processo');

            $result = $service->enviarProcesso($post);

            if ($result) {
                return $this->sendJson(array(), 'Processo enviado com sucesso');
            }

            return $this->sendJson(array(), 'Não foi possível enviar o processo');

        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    /**
     *
     * Get Route annotation.
     * @Post("/processo/sobrestar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo"
     * )
     */
    public function postSobrestarProcessoAction () {
        $request = $this->params();

        $unidade = $request->fromPost('unidade');
        $usuario = $request->fromPost('usuario');
        $protocolo = $request->fromPost('protocolo');
        $motivo = $request->fromPost('motivo');

        try {
            $service = $this->getServiceLocator()->get('processo');
            $service->sobrestarProcesso($protocolo, $motivo, $unidade, $usuario);

            return $this->sendJson(array(), 'Processo sobrestado com sucesso');

        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    /**
     *
     * Get Route annotation.
     * @Post("/processo/sobrestar/cancelar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo"
     * )
     */
    public function postCancelarSobrestarProcessoAction () {
        $request = $this->params();

        $unidade = $request->fromPost('unidade');
        $usuario = $request->fromPost('usuario');
        $protocolo = $request->fromPost('protocolo');

        try {
            $service = $this->getServiceLocator()->get('processo');
            $service->cancelarSobrestarProcesso($protocolo, $unidade, $usuario);

            return $this->sendJson(array(), 'Sobrestar cancelado com sucesso');

        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }

    public function getChecarUnidadesProcessoAction () {
        $request = $this->params();

        try {
            $service = $this->getServiceLocator()->get('processo');
            $protocolo = ($request->fromQuery('protocolo'));

            return $this->sendJson($service->checarProcessoUnidades($protocolo));
        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    public function getChecarSobrestamentoAction () {
        $request = $this->params();

        try {
            $service = $this->getServiceLocator()->get('processo');
            $protocolo = ($request->fromQuery('protocolo'));
            $unidade = ($request->fromQuery('unidade'));

            return $this->sendJson($service->checarSobrestamento($protocolo, $unidade));
        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    public function getProcessoUnidadesAction () {
        $request = $this->params();

        try {
            $service = $this->getServiceLocator()->get('processo');
            $protocolo = ($request->fromQuery('protocolo'));

            return $this->sendJson($service->pesquisarProcessoUnidades($protocolo));
        } catch (BaseException $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }
}
