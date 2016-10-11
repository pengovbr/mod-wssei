<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Service\Exception\BaseException;
use Doctrine\Common\Util\Debug;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class BlocoController extends AbstractActionController
{
    /**
     *
     * GET Route annotation.
     * @Get("/bloco/consultar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="consultar bloco",
     *
     *  parameters={
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Identificador Unidade Organizacional"},
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Identificador do número de registros por página"},
     *      {"name"="offset", "dataType"="integer", "required"=true, "description"="Identificador da página em execução"},
     *  }
     * )
     */
    public function getConsultarBlocoAction() {
        try {
            $request = $this->params();
            $page = ($request->fromQuery('start'));

            $service = $this->getServiceLocator()->get('bloco');
            $return = $service->consultarBloco(
                $request->fromQuery('unidade'),
                $request->fromQuery('limit'),
                (($page < 0)? 0 : $page)
            );

            return $this->sendJson($return);
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    /**
     *
     * GET Route annotation.
     * @Get("/bloco/documentos/consultar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="consultar documentos bloco",
     *
     *  parameters={
     *      {"name"="bloco", "dataType"="integer", "required"=true, "description"="Identificador do bloco"}
     *  }
     * )
     */
    public function getConsultarDocumentosBlocoAction() {
        try {
            $request = $this->params();

            $service = $this->getServiceLocator()->get('bloco');
            $return = $service->listarDocumentosBloco(
                $request->fromQuery('bloco')
            );

            return $this->sendJson($return);
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    /**
     *
     * Post Route annotation.
     * @Post("/bloco/anotacao/cadastrar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="cadastrar anotacao documento do bloco",
     *
     *  parameters={
     *      {"name"="protocolo", "dataType"="integer", "required"=true, "description"="Identificador Protocolo"},
     *      {"name"="bloco", "dataType"="integer", "required"=true, "description"="Identificador do Bloco"},
     *      {"name"="anotacao", "dataType"="string", "required"=true, "description"="Anotação"},
     *  }
     * )
     */
    public function postCadastrarAnotacaoBlocoAction() {
        try {
            $request = $this->params();
            $post = ($request->fromPost());

            $service = $this->getServiceLocator()->get('bloco');
            $service->cadastrarAnotacao($post);

            return $this->sendJson(array(), 'Anotação realizada com sucesso');
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    /**
     *
     * Post Route annotation.
     * @Post("/bloco/retornar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="retorno do bloco",
     *
     *  parameters={
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Id da unidade onde o bloco foi gerado"},
     *      {"name"="bloco", "dataType"="integer", "required"=true, "description"="Número do bloco"}
     *  }
     * )
     */
    public function postRetornarBlocoAction() {
        try {
            $request = $this->params();
            $post = $request->fromPost();

            $service = $this->getServiceLocator()->get('bloco');
            $service->retornarBloco($post);

            return $this->sendJson(array(),'bloco retornado com sucesso');
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    /**
     *
     * Post Route annotation.
     * @Post("/bloco/disponibilizar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="disponibilizar do bloco",
     *
     *  parameters={
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Id da unidade onde o bloco foi gerado"},
     *      {"name"="bloco", "dataType"="integer", "required"=true, "description"="Número do bloco"}
     *  }
     * )
     */
    public function postDisponibilizarBlocoAction() {
        try {
            $request = $this->params();
            $post = $request->fromPost();

            $service = $this->getServiceLocator()->get('bloco');
            $service->disponibilizarBloco($post);

            return $this->sendJson(array(),'bloco disponibilizado com sucesso');
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }
}
