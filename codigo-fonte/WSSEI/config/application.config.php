<?php
namespace config;

use DoctrineModule\Service\DriverFactory;
use DoctrineModule\Service\EventManagerFactory;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use DoctrineORMModule\Service\DBALConnectionFactory;
use DoctrineORMModule\Service\EntityManagerFactory;
use DoctrineORMModule\Service\EntityResolverFactory;
use DoctrineORMModule\Service\SQLLoggerCollectorFactory;
use Symfony\Component\Yaml\Parser;
use Doctrine\DBAL\Configuration;
use DoctrineORMModule\Service\ConfigurationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

return array(
    'modules' => array(
        'DoctrineModule',
        'DoctrineORMModule',
        'Application',
        'Base',
        'MecCdn',
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            './module',
            './vendor'
        ),

        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php',
            getenv('APP_CONFIG_FILE') ?  : 'config/autoload/desenvolvedor.php'
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
        'translator' => array(
            'locale' => 'pt_BR',
            'translation_file_patterns' => array(
                array(
                    'type' => 'gettext',
                    'base_dir' => __DIR__ . '/../language',
                    'pattern' => '%s.mo',
                ),
                array(
                    'type' => 'phparray',
                    'base_dir' => __DIR__ . '/../language',
                    'pattern' => '%s.php',
                ),
            ),
        ),
        'factories' => array(
            'doctrine.configuration.orm_default' => function($sm) {
                    $factory = new ConfigurationFactory('orm_default');
                    /**
                     * @var Configuration $config
                     */
                    $config = $factory->createService($sm);
                    return $config;
                },
            'doctrine.configuration.orm_sip' => function($sm) {
                    $factory = new ConfigurationFactory('orm_sip');

                    /**
                     * @var Configuration $config
                     */
                    $config = $factory->createService($sm);
                    return $config;
                },
            'doctrine.connection.orm_sip'           => new DBALConnectionFactory('orm_sip'),
            'doctrine.entitymanager.orm_sip'        => new EntityManagerFactory('orm_sip'),
            'doctrine.driver.orm_sip'               => new DriverFactory('orm_sip'),
            'doctrine.eventmanager.orm_sip'         => new EventManagerFactory('orm_sip'),
            'doctrine.entity_resolver.orm_sip'      => new EntityResolverFactory('orm_sip'),
            'doctrine.sql_logger_collector.orm_sip' => new SQLLoggerCollectorFactory('orm_sip'),

            'DoctrineORMModule\Form\Annotation\AnnotationBuilder' => function(ServiceLocatorInterface $srvManager) {
                return new AnnotationBuilder($srvManager->get('doctrine.entitymanager.orm_sip'));
            },
    )

    ),
);
