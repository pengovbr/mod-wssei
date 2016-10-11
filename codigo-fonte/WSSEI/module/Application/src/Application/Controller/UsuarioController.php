<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Service\Exception\ServiceException;
use Zend\View\Model\ViewModel;
use Doctrine\Common\Util\Debug;
use Zend\Json\Server\Exception\HttpException;

class UsuarioController extends AbstractActionController
{
    /**
     *
     * POST Route annotation.
     * @Post("/usuario/auth")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Login do usuário",
     *
     *  parameters={
     *      {"name"="usuario", "dataType"="string", "required"=true, "description"="username do sistema SEI Web"},
     *      {"name"="senha", "dataType"="string", "required"=true, "description"="password do sistema SEI Web"}
     *  }
     * )
     */
    public function authAction()
    {
        try {
            $request = $this->params();
            $service = $this->getServiceLocator()->get('usuario');

            $return = $service->autenticar(
                $request->fromPost('usuario'),
                $request->fromPost('senha')
            );

            if (!$return) {
                return $this->sendJson(array(), 'Usuario não autorizado', 'error');
            }

            return $this->sendJson(array(
                "data" => $return
            ));

        } catch (\SoapFault $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    /**
     *
     * Get Route annotation.
     * @Get("/usuario/listar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="idUnidade", "dataType"="integer", "required"=true, "description"="Identificador da Unidade"},
     *      {"name"="usuario", "dataType"="integer", "required"=false, "description"="Opcional. Filtra determinado usuário."}
     *  }
     * )
     */
    public function getUsuariosAction () {
        try {
            $request = $this->params();

            $post = $request->fromQuery();
            $service = $this->getServiceLocator()->get('usuario');

            return $this->sendJson($service->getUsuarios($post));
        } catch (\SoapFault $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }

    /**
     *
     * Get Route annotation.
     * @Get("/usuario/alterar/unidade")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Versão do aplicativo",
     *
     *  parameters={
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="Identificador da Unidade"},
     *      {"name"="usuario", "dataType"="integer", "required"=false, "description"="Opcional. Filtra determinado usuário."}
     *  }
     * )
     */
    public function postAlterarUsuarioUnidadeAction () {
        try {
            $request = $this->params();

            $service = $this->getServiceLocator()->get('usuario');
            $usuario = $request->fromPost('usuario');
            $unidade = $request->fromPost('unidade');

            $service->alterarUltimaUnidadeUsuario($usuario, $unidade);

            return $this->sendJson(array(), 'Unidade alterada com sucesso');
        } catch (\SoapFault $ex) {
            return $this->sendJson(array(),  $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }
}
