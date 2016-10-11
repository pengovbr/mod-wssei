<?php

return array(
    'acl' => array(
        'permissoes' => array(
            // application
            'application\controller\index\index'    => array('anonimo'),
            'application\controller\upload\index'   => array('anonimo'),
            'application\controller\paginate\index' => array('anonimo'),
            'application\controller\index\faq'      => array('anonimo'),

            'application\controller\componentes\index' => array('anonimo'),
            'application\controller\index\restrito'    => array('autenticado'),

            //login
            'application\controller\login\index'              => array('anonimo'),
            'application\controller\login\primeiro-acesso'    => array('anonimo'),
            'application\controller\login\redirect-login-ssd' => array('anonimo'),
            'application\controller\login\logout'             => array('autenticado'),
        ),
        'perfis' => array(
            'anonimo' => array(),
            'autenticado' => array('anonimo')
        )
    )
);
