<?

    try {

        require_once dirname(__FILE__) . '/../../web/SEI.php';
        session_start();
    
        SessaoSEI::getInstance(false);
        BancoSEI::getInstance()->setBolScript(true);
    
        $objVersaoSeiRN = new MdWsSeiVersaoRN();
        $objVersaoSeiRN->verificarVersaoInstalada();
        $objVersaoSeiRN->setStrNome(MdWsSeiVersaoRN::NOME_MODULO);
        $objVersaoSeiRN->setStrVersaoAtual(MdWsSeiRest::VERSAO_MODULO);
        $objVersaoSeiRN->setStrParametroVersao(MdWsSeiVersaoRN::PARAMETRO_VERSAO_MODULO);
        $objVersaoSeiRN->setArrVersoes(
            array(
                '0.0.0' => 'versao_0_0_0',
                '0.8.12' => 'versao_0_8_12',
                '1.0.0' => 'versao_1_0_0',
                '1.0.1' => 'versao_1_0_1',
                '1.0.2' => 'versao_1_0_2',
                '1.0.3' => 'versao_1_0_3',
                '1.0.4' => 'versao_1_0_4',
                '2.0.0' => 'versao_2_0_0',
            )
        );
    
        $objVersaoSeiRN->setStrVersaoInfra('1.595.1');
        $objVersaoSeiRN->setBolMySql(true);
        $objVersaoSeiRN->setBolOracle(true);
        $objVersaoSeiRN->setBolSqlServer(true);
        $objVersaoSeiRN->setBolPostgreSql(true);
        $objVersaoSeiRN->setBolErroVersaoInexistente(true);
        $objVersaoSeiRN->atualizarVersao();
    } catch (Exception $e) {
        echo (InfraException::inspecionar($e));
        try {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        } catch (Exception $e) {
        }
        exit(1);
    }
