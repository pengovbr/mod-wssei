<?php

return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Lsw\\DoctrinePdoDblib\\Doctrine\\DBAL\\Driver\\PDODblib\\Driver',
                'params' => array(
                    'port'     => '1433',
                    'service'  => true,
                    'charset'  => 'utf8',
                )
            ),

            'orm_sip' => array(
                'driverClass' => 'Lsw\\DoctrinePdoDblib\\Doctrine\\DBAL\\Driver\\PDODblib\\Driver',
                'params' => array(
                    'port'     => '1433',
                    'service'  => true,
                    'charset'  => 'utf8',
                )
            )
        ),
        'configuration' => array(
            'orm_default' => array(
                'proxy_dir' => sys_get_temp_dir() . '/data/sei/orm/DoctrineORMModule/Proxy'
            ),

            'orm_sip' => array(
                'proxy_dir' => sys_get_temp_dir() . '/data/sei/orm/DoctrineORMModule/Proxy'
            )
        ),
        'entitymanager' => array(
            'orm_default' => array(
                'connection'    => 'orm_default',
                'configuration' => 'orm_default'
            ),
            'orm_sip' => array(
                'connection'    => 'orm_sip',
                'configuration' => 'orm_sip'
            ),
        ),
    ),
    'dados-sistema' => array(
        'nome' => 'Archetype Zf',
        'descricao' => 'Descricao da Sigla do Sistema - Arquitetura Base'
    ),
);
