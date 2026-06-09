<?php
use Slim\Factory\AppFactory;


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
    $container = new \DI\Container();

    AppFactory::setContainer($container);
    $app = AppFactory::create();
    $app->setBasePath('/sei');

    $container = $app->getContainer();
    return $container;
  }

}