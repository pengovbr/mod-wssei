<?php

use PHPUnit\Extensions\Selenium2TestCase\WebDriverException;

/**
 * Page Object da tela de login do SEI/SUPER.
 */
class PaginaLogin extends PaginaTeste
{
    protected $usuarioInput;
    protected $passwordInput;
    protected $loginButton;

    public function __construct($test)
    {
        parent::__construct($test);
        $this->usuarioInput = $test->byId('txtUsuario');
        $this->passwordInput = $test->byId('pwdSenha');
        try {
            $this->loginButton = $test->byId('Acessar');
        } catch (WebDriverException $wde) {
            // SEI 4.0.12 alterou o id do botao para sbmAcessar
            $this->loginButton = $test->byId('sbmAcessar');
        }
    }

    public function usuario($value)
    {
        if (isset($value)) {
            $this->usuarioInput->value($value);
        }

        return $this->usuarioInput->value();
    }

    public function senha($value)
    {
        if (isset($value)) {
            $this->passwordInput->value($value);
        }

        return $this->passwordInput->value();
    }

    public function submit()
    {
        $this->loginButton->click();
        return $this->test;
    }

    public static function executarAutenticacao($test, $usuario = "teste", $senha = "teste")
    {
        $paginaLogin = new PaginaLogin($test);
        $paginaLogin->usuario($usuario);
        $paginaLogin->senha($senha);
        $paginaLogin->submit();
    }
}
