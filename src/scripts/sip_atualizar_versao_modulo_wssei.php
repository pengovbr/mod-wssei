<?php
// Identificação da versão do módulo mod-wssei. Este deve estar sempre sincronizado com a versão definida em MdWsSeiRest.php
define("VERSAO_MODULO_WSSEI", "2.1.2");

require_once dirname(__FILE__) . '/../../web/Sip.php';

class VersaoSipRN extends InfraScriptVersao
{
    const PARAMETRO_VERSAO_MODULO = 'VERSAO_MODULO_WSSEI';
    const NOME_MODULO = 'Módulo de WSSEI - SIP';

    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSip::getInstance();
    }

    protected function verificarVersaoInstaladaControlado()
    {
        $objInfraParametroDTO = new InfraParametroDTO();
        $objInfraParametroDTO->setStrNome(VersaoSipRN::PARAMETRO_VERSAO_MODULO);
        $objInfraParametroDB = new InfraParametroBD(BancoSip::getInstance());
        if ($objInfraParametroDB->contar($objInfraParametroDTO) == 0) {
            $objInfraParametroDTO->setStrValor('0.0.0');
            $objInfraParametroDB->cadastrar($objInfraParametroDTO);
        }
    }

    public function versao_0_0_0($strVersaoAtual)
    {
        $this->logar("VERSÃO 0.0.0 atualizada.");
    }

    public function versao_0_8_12($strVersaoAtual)
    {
        $this->logar("VERSÃO 0.8.12 atualizada.");
    }

    public function versao_1_0_0($strVersaoAtual)
    {
        $this->logar("VERSÃO 1.0.0 atualizada.");
    }

    public function versao_1_0_1($strVersaoAtual)
    {
        $this->logar("VERSÃO 1.0.1 atualizada.");
    }

    public function versao_1_0_2($strVersaoAtual)
    {
        $this->logar("VERSÃO 1.0.2 atualizada.");
    }

    public function versao_1_0_3($strVersaoAtual)
    {
        $this->logar("VERSÃO 1.0.3 atualizada.");
    }

    public function versao_1_0_4($strVersaoAtual)
    {
        $this->logar("VERSÃO 1.0.4 atualizada.");
    }

    public function versao_2_0_0($strVersaoAtual)
    {
        $this->logar("VERSÃO 2.0.0 atualizada.");
    }

    public function versao_2_1_0($strVersaoAtual)
    {
        $this->logar("VERSÃO $strVersaoAtual atualizada.");
    }

    public function versao_2_1_1($strVersaoAtual)
    {
        $this->logar("VERSÃO $strVersaoAtual atualizada.");
    }  

    public function versao_2_1_2($strVersaoAtual)
    {
        $this->logar("VERSÃO $strVersaoAtual atualizada.");
    }
}

try {
    session_start();

    SessaoSip::getInstance(false);
    BancoSip::getInstance()->setBolScript(true);

    $objVersaoSipRN = new VersaoSipRN();
    $objVersaoSipRN->verificarVersaoInstalada();
    $objVersaoSipRN->setStrNome(VersaoSipRN::NOME_MODULO);
    $objVersaoSipRN->setStrVersaoAtual(VERSAO_MODULO_WSSEI);
    $objVersaoSipRN->setStrParametroVersao(VersaoSipRN::PARAMETRO_VERSAO_MODULO);
    $objVersaoSipRN->setArrVersoes(
        array(
            '0.0.0' => 'versao_0_0_0',
            '0.8.12' => 'versao_0_8_12',
            '1.0.0' => 'versao_1_0_0',
            '1.0.1' => 'versao_1_0_1',
            '1.0.2' => 'versao_1_0_2',
            '1.0.3' => 'versao_1_0_3',
            '1.0.4' => 'versao_1_0_4',
            '2.0.0' => 'versao_2_0_0',
            '2.1.0' => 'versao_2_1_0',
            '2.1.1' => 'versao_2_1_1',
            '2.1.2' => 'versao_2_1_2',
        )
    );
    
    $objVersaoSipRN->setStrVersaoInfra('1.595.1');
    $objVersaoSipRN->setBolMySql(true);
    $objVersaoSipRN->setBolOracle(true);
    $objVersaoSipRN->setBolSqlServer(true);
    $objVersaoSipRN->setBolPostgreSql(true);
    $objVersaoSipRN->setBolErroVersaoInexistente(true);

    $objVersaoSipRN->atualizarVersao();
} catch (Exception $e) {
    echo (InfraException::inspecionar($e));
    try {
        LogSip::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {
    }
    exit(1);
}
