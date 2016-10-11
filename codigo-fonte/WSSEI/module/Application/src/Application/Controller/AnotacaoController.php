<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Service\Anotacao;
use Application\Service\Exception\BaseException;
use Application\Service\Unidade;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class AnotacaoController extends AbstractActionController
{
    /**
     *
     * Post Route annotation.
     * @Post("/anotacao/cadastrar")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Assinatura do documento",
     *
     *  parameters={
     *      {"name"="unidade", "dataType"="integer", "required"=true, "description"="CPF do usuário"},
     *      {"name"="protocolo", "dataType"="integer", "required"=true, "description"="Login do usuário"},
     *      {"name"="usuario", "dataType"="integer", "required"=true, "description"="Login do usuário"},
     *      {"name"="descricao", "dataType"="string", "required"=true, "description"="Login do usuário"},
     *      {"name"="prioridade", "dataType"="string", "required"=true, "description"="Login do usuário"}
     *  }
     * )
     */
    public function postCriarAnotacaoAction()
    {
        try {
            $request = $this->params();
            $post = $request->fromPost();

            /** @var Anotacao $service */
            $service = $this->getServiceLocator()->get('anotacao');
            /** @var Unidade $serviceUnidade */
            $serviceUnidade = $this->getServiceLocator()->get('unidade');
            $serviceProtocolo = $this->getServiceLocator()->get('protocolo');
            $service->criarAnotacao($post, $serviceUnidade, $serviceProtocolo);

            return $this->sendJson(
                array(),
                'anotação cadastrada com sucesso'
            );
        } catch (BaseException $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        } catch (\Exception $ex) {
            return $this->sendJson(array(), $ex->getMessage(), 'error');
        }
    }
}
