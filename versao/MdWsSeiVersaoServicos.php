<?php

abstract class MdWsSeiVersaoServicos
{
    protected $slimApp;

    protected function __construct(Slim\App $slimApp)
    {
        $this->slimApp = $slimApp;
    }

    public static function getInstance(Slim\App $slimApp){
    }

    /**
     * Método que monta os serviços a serem disponibilizados
     * @param Slim\App $slimApp
     * @return Slim\App
     */
    public function registrarServicos(){
        return $this->container;
    }

}