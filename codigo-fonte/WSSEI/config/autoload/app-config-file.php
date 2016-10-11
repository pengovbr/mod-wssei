<?php
/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */

ini_set('error_reporting', E_ALL);
ini_set('display_startup_errors', true);
ini_set('display_errors', true);
ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 0);

return array(
    'url_sei'   => '/storage/administrativo/sei/dsv/upload',
    'soap' => array (
		'wsdl_mobile'   => 'http://dsv-sei.mec.gov.br/sei/controlador_ws.php?wsdl&servico=mobile',
        'wsdl_sei' => 'http://dsv-sei.mec.gov.br/sei/controlador_ws.php?wsdl&servico=sei',
        'wsdl_sip' => 'http://dsv-sei.mec.gov.br/sip/controlador_ws.php?servico=wsdl',
        'wsdl_sei_edoc' => 'http://dsv-sei.mec.gov.br/sei/controlador_ws.php?servico=edoc',
    ),

    'id_sistema' => '100000100',
    'secret'     => 'ThisTokenIsNotSoSecretChangeIt',

    'cdn' => array(
        'enabled' => false,
        'urls' => array(
            'http://dsv-static00.mec.gov.br',
            'http://dsv-static01.mec.gov.br',
        ),
        'template' => '/padraosistemas/bibliotecas/stable',
        //    'sistema' => '/agrupador/aplicacao',
    ),
    'log' => array(
        'path' => '/storage/administrativo/wssei/dsv/logs'
    ),
    'path' => array(
        'upload' => '/storage/administrativo/wssei/dsv/upload',
    ),
    //'environment' => 'development',
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'params' => array(
                    'host'     => '10.37.0.27',
                    'user'     => 'sysdbsei',
                    'password' => 'f88dda0eef3311b5',
                    'dbname'   => 'dbsei',
                )
            ),

            'orm_sip' => array(
                'params' => array(
                    'host'     => '10.37.0.27',
                    'user'     => 'sysdbsip',
                    'password' => '947c68c3436dfda0',
                    'dbname'   => 'dbsip',
                )
            )
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
            => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    )
);
