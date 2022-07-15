<?php

//namespace Wssei\Tests;

class TesteUtils 
{
    
    public static function obterToken($http){

        $t = $GLOBALS['token'];
        
        if(!$t){
            
            $t = self::autenticar($http);
            $GLOBALS['token'] = $t;

        }

        return $GLOBALS['token'];
    }

    public function montar_cabecalho_geral($token, $arrForm){
        
        $c = ['token' => $token]; 
        $c = ['form_params' => $arrForm,'headers' => $c];

        return $c;
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

