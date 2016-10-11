<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application;

//require_once 'vendor/FirePHPCore/FirePHP.class.php';

use Application\Service\AbstractService;
use Application\Service\Atividade;
use Application\Service\Protocolo;
use Auditoria\Service\Utility as ServiceUtility;
use Auditoria\Writer\Audit as AuditWriter;

use Doctrine\Common\Util\Debug;
use Zend\Authentication\AuthenticationService;
use Zend\Log\Logger;

use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module {

    /** @var  ServiceManager */
    private $_serviceManager;

	public function init(ModuleManager $moduleManager) {

	}

	public function onBootstrap(MvcEvent $event) {
        $app = $event->getApplication();
        $sm  = $app->getServiceManager();

        $entityManager = $sm->get('Doctrine\ORM\EntityManager');

        /**
         * Registrando o FetchPairsHydrator
         */
        $entityManager->getConfiguration()->addCustomHydrationMode('FetchPairsHydrator', 'Base\Hydrator\FetchPairsHydrator');

        $em  = $app->getEventManager();
        $this->_serviceManager = $sm;
        AbstractService::setServiceManager($this->_serviceManager);

        $sm = $event->getApplication()->getServiceManager();

	}

	public function getConfig() {
		return include __DIR__ .'/config/module.config.php';
	}

	public function getAutoloaderConfig() {
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces'                    => array(
					__NAMESPACE__                  => __DIR__ .'/src/'.__NAMESPACE__,
				)
			)
		);
	}

	public function getViewHelperConfig()
	{
		return array(
			'invokables' => array(
				'version' => 'Application\View\Helper\Version',
			)
		);
	}

	public function getServiceConfig() {
		return array(
			'factories' => array(
				'acompanhamento' => function () {
                    $service = new Service\Acompanhamento();
					return $service;
				},
				'anotacao' => function () {
					return new Service\Anotacao();
				},
				'atividade' => function () {
					return new Service\Atividade();
				},
                'bloco' => function (ServiceLocatorInterface $srvManager) {
                    $config = $srvManager->get('Config');
                    $service = new Service\Bloco();
                    $service->setConfig($config);

                    return $service;
                },
				'cargoFuncao' => function () {
					return new Service\CargoFuncao();
				},
				'documento' => function (ServiceLocatorInterface $srvManager) {
                    $config = $srvManager->get('Config');
                    $serviceUsuario = new Service\Usuario();
                    $serviceUsuario->setConfig($config);

                    $service = new Service\Documento();
                    $serviceProcesso = new Service\Processo();
                    $service->setServiceUsuario($serviceUsuario);
                    $service->setServiceProcesso($serviceProcesso);
                    $service->setConfig($config);

                    return $service;
				},
				'grupoAcompanhamento' => function () {
					return new Service\GrupoAcompanhamento();
				},
				'observacao' => function () {
                    $atividade = new Service\Atividade();
                    $service = new Service\Observacao();

                    $service->setServiceAtividade($atividade);

					return new Service\Observacao();
				},
				'orgao' => function () {
					return new Service\Orgao();
				},
				'processo' => function (ServiceLocatorInterface $srvManager) {
                    $config = $srvManager->get('Config');

                    $service = new Service\Processo();
                    $service->setServiceAtividade(new Atividade());
                    $service->setServiceProtocolo(new Protocolo());

                    $service->setConfig($config);

                    return $service;
				},
				'protocolo' => function () {
					return new Service\Protocolo();
				},
				'retornoProgramado' => function () {
					return new Service\RetornoProgramado();
				},
				'unidade' => function () {
					return new Service\Unidade();
				},
				'usuario' => function (ServiceLocatorInterface $srvManager) {
                    $config = $srvManager->get('Config');

                    $service = new Service\Usuario();
                    $service->setConfig($config);

					return $service;
				},
			)
		);
	}

	public function loadConfiguration(MvcEvent $e) {

	}

	public function onMergeConfig(ModuleEvent $e) {

	}

	public function loadVariablesView(MvcEvent $e) {

	}
}
