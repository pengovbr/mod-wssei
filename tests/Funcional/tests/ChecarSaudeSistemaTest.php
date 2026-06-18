<?php

/**
 * Teste basico de checagem de saude (health check) do ambiente.
 *
 * Verifica que o SEI/SUPER esta no ar e que a autenticacao funciona,
 * confirmando que a infraestrutura de testes funcionais esta operacional.
 */
class ChecarSaudeSistemaTest extends CenarioBaseTestCase
{
    public static $contextoTeste;

    public function test_login_e_checar_saude_sistema()
    {
        self::$contextoTeste = $this->definirContextoTeste(CONTEXTO_SEI);

        // Acessar a página de login
        $this->acessarSistema(self::$contextoTeste['URL'], self::$contextoTeste['SIGLA_UNIDADE'], self::$contextoTeste['LOGIN'], self::$contextoTeste['SENHA']);



        sleep(60);
        // Esperar que o login tenha sucesso, por exemplo, verificando um elemento visível da tela principal
        $boolLoginSucesso = $this->waitUntil(function () {
            try {
                $element = $this->byXPath("//span[contains(text(),'Controle de Processos')]");
                return $element !== null;
            } catch (\Exception $e) {
                return null;
            }
        }, 30000);

        // Teste mínimo para checar o 'health' do sistema
        $this->assertTrue($boolLoginSucesso);
    }
}
