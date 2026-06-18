<?php

use PHPUnit\Extensions\Selenium2TestCase;

/**
 * Classe base contendo rotinas comuns utilizadas nos casos de teste funcionais do modulo WSSEI.
 */
class CenarioBaseTestCase extends Selenium2TestCase
{
    protected $paginaBase = null;
    protected $paginaAgendamentos = null;

    public function setUpPage(): void
    {
        $this->paginaBase = new PaginaTeste($this);
        $this->paginaAgendamentos = new PaginaAgendamentos($this);
        $this->currentWindow()->maximize();
    }

    public function setUp(): void
    {
        $this->setHost(PHPUNIT_HOST);
        $this->setPort(intval(PHPUNIT_PORT));
        $this->setBrowser(PHPUNIT_BROWSER);
        $this->setBrowserUrl(PHPUNIT_TESTS_URL);
        $this->setDesiredCapabilities(
            array(
                'platform' => 'LINUX',
                'chromeOptions' => array(
                    'w3c' => false,
                    'args' => array(
                        '--profile-directory=' . uniqid(),
                        '--disable-features=TranslateUI',
                        '--disable-translate',
                    ),
                )
            )
        );
    }

    protected function definirContextoTeste($nomeContexto)
    {
        return array(
            'URL' => constant($nomeContexto . '_URL'),
            'ORGAO' => constant($nomeContexto . '_SIGLA_ORGAO'),
            'SIGLA_UNIDADE' => constant($nomeContexto . '_SIGLA_UNIDADE'),
            'LOGIN' => constant($nomeContexto . '_USUARIO_LOGIN'),
            'SENHA' => constant($nomeContexto . '_USUARIO_SENHA'),
        );
    }

    /**
     * Acessa o sistema realizando a autenticacao com as credenciais informadas.
     */
    protected function acessarSistema($url, $login, $senha)
    {
        $this->url($url);
        PaginaLogin::executarAutenticacao($this, $login, $senha);
    }

    protected function sairSistema()
    {
        $this->paginaBase->sairSistema();
    }

    public static function generateRandomString($length = 10)
    {
        return substr(
            str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / 62))),
            1,
            $length
        );
    }
}
