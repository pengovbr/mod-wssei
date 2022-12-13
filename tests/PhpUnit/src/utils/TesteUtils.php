<?php

//namespace Wssei\Tests;

class TesteUtils 
{
    
    public static function obterToken($http){

        $t = $GLOBALS['token'];
        
        if(!$t){

            echo 'autenticar';
            $t = self::autenticar($http);
            $GLOBALS['token'] = $t;

        }else{

            echo 'autenticado';

        }

        return $GLOBALS['token'];
    }

    private static function autenticar($http, $user='teste', $pass='teste'){

        $p = ['form_params' => ['usuario' => $user, 'senha' => $pass]];
        $body = $http->request('POST', 'autenticar', $p);
        $r='';

        try{
            
            $r = json_decode($body->getBody())->{"data"}->{"token"};

        } catch(Exception $e){
            $r = '';
        }

        return $r;
    }
    
    
}

