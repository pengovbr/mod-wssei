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

class DocumentoController extends AbstractActionController
{
    /**
     *
     * Post Route annotation.
     * @Post("/documento/{documento}/assinar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Assinatura do documento",
     *
     *  parameters={
     *      {"name"="cpf", "dataType"="string", "required"=true, "description"="CPF do usuário"},
     *      {"name"="login", "dataType"="string", "required"=true, "description"="Login do usuário"}
     *  }
     * )
     */
    public function postAssinarAction() {
        try {
            $request = $this->params();

            $documento = $request->fromPost('documento');
            $orgao = $request->fromPost('orgao');
            $cargo = $request->fromPost('cargo');
            $login = $request->fromPost('login');
            $senha = $request->fromPost('senha');
            $usuario = $request->fromPost('usuario');

            $service = $this->getServiceLocator()->get('documento');
            $service->assinar($documento, $orgao, $cargo, $login, $senha, $usuario);

            return $this->sendJson(array(), 'Documento assinado com sucesso');

        } catch (\SoapFault $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    /**
     *
     * Post Route annotation.
     * @Post("/documento/assinar/bloco")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Assinatura do documento",
     *
     *  parameters={
     *      {"name"="cpf", "dataType"="string", "required"=true, "description"="CPF do usuário"},
     *      {"name"="login", "dataType"="string", "required"=true, "description"="Login do usuário"}
     *  }
     * )
     */
    public function postAssinarBlocoAction() {
        try {
            $request = $this->params();
            $documento = explode(',', $request->fromPost('arrDocumento'));
            $orgao = $request->fromPost('orgao');
            $cargo = $request->fromPost('cargo');
            $login = $request->fromPost('login');
            $senha = $request->fromPost('senha');
            $usuario = $request->fromPost('usuario');

            if (!$request->fromPost('arrDocumento')) {
                $documento = array( $request->fromPost('documento') );
            }

            $service = $this->getServiceLocator()->get('documento');
            $service->assinarBloco($documento, $orgao, $cargo, $login, $senha, $usuario);

            return $this->sendJson(array(), 'Documento em bloco assinado com sucesso');

        } catch (\SoapFault $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    /**
     *
     * Get Route annotation.
     * @Get("/documento/ciencia/listar")
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
    public function getListarCienciaDocumentoAction () {
        $request = $this->params();
        $protocolo = $request->fromQuery('protocolo');

        $service = $this->getServiceLocator()->get('documento');
        return $this->sendJson($service->listarCienciaDocumento($protocolo));
    }

    /**
     *
     * Get Route annotation.
     * @Get("/documento/assinatura/listar")
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
    public function getListarAssinaturasDocumentoAction () {
        $request = $this->params();
        $documento = $request->fromQuery('documento');

        $service = $this->getServiceLocator()->get('documento');
        return $this->sendJson($service->listarAssinaturasDocumento($documento));
    }

    /**
     *
     * Get Route annotation.
     * @Post("/documento/ciencia/protocolo/{protocolo}")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     * )
     */
    public function postCienciaDocumentoAction () {
        try {
            $request = $this->params();

            $protocolo = $request->fromPost('protocolo');
            $unidade = $request->fromPost('unidade');
            $usuario = $request->fromPost('usuario');

            $serviceUnidade = $this->getServiceLocator()->get('unidade');
            $serviceProtocolo = $this->getServiceLocator()->get('protocolo');
            $serviceAtividade = $this->getServiceLocator()->get('atividade');

            $service = $this->getServiceLocator()->get('documento');

            $service->cienciaDocumento($protocolo, $unidade, $usuario, $serviceUnidade, $serviceProtocolo, $serviceAtividade);

            return $this->sendJson(array(), 'Ciência documento realizado com sucesso');
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }

    }
}
