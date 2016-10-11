<?php

// your configuration file, e.g., config/autoload/global.php
return array(
    'navigation' => array(
        'escapeLabels' => false,
        'default' => array(
            array(
                'label' => 'Home',
                'route' => 'home',
            ),
            array(
                'label' => 'Menu Exemplo <i class="fa fa-chevron-down pull-right"></i>',
                'route' => 'home',
                'pages' => array(
                    array(
                        'label' => 'Submenu #1',
                        'route' => 'home',
                    ),
                    array(
                        'label' => 'Submenu #2',
                        'route' => 'home',
                    ),
                    array(
                        'label' => 'Submenu #3',
                        'route' => 'home',
                    ),
                ),
            ),
            array(
                'label' => 'Menu Exemplo #2',
                'route' => 'home',
            ),
            array(
                'label' => 'Menu Exemplo #3 <i class="fa fa-chevron-down pull-right"></i>',
                'route' => 'home',
                'pages' => array(
                    array(
                        'label' => 'Submenu #1',
                        'route' => 'home',
                    ),
                    array(
                        'label' => 'Submenu #2',
                        'route' => 'home',
                    ),
                    array(
                        'label' => 'Submenu #3',
                        'route' => 'home',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
    ),
);
