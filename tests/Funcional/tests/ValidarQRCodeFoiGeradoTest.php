<?php

class ValidarQRCodeFoiGeradoTest extends CenarioBaseTestCase
{
    public static $contextoTeste;

    public function test_loga_e_valida_qrcode_foi_gerado()
    {
        self::$contextoTeste = $this->definirContextoTeste(CONTEXTO_SEI);

        //die(self::$contextoTeste['URL']);

        // Acessar a página de login
        $this->acessarSistema(self::$contextoTeste['URL'], self::$contextoTeste['SIGLA_UNIDADE'], self::$contextoTeste['LOGIN'], self::$contextoTeste['SENHA']);

        // Esperar que o login tenha sucesso, por exemplo, verificando um elemento visível da tela principal
        $boolQrCodeGerado = $this->waitUntil(function () {
            try {
                $element = $this->byId("textoQRCode");
                return $element !== null;
            } catch (\Exception $e) {
                return null;
            }
        }, 30000);

        // Teste mínimo para checar o 'health' do sistema
        $this->assertTrue($boolQrCodeGerado);

    }
}
