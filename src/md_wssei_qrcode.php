<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();


    //////////////////////////////////////////////////////////////////////////////
    // InfraDebug::getInstance()->setBolLigado(false);
    // InfraDebug::getInstance()->setBolDebugInfra(true);
    // InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    SessaoSEI::getInstance()->validarLink();

    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    switch ($_GET['acao']) {
        case 'md_wssei_qrcode':
            $strTitulo = 'Leitura do QR Code';
            $arrComandos[] = '<button type="button" accesskey="V" name="btnVoltar" id="btnVoltar" value="Voltar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao']) . PaginaSEI::getInstance()->montarAncora($strAncora) . '\';" class="infraButton"><span class="infraTeclaAtalho">V</span>oltar</button>';
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }



} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
?>
<div style="font-size: 12px; text-align: center;">
    <div style="height: 12px; margin-bottom: 22px; background-color: var(--color-primary-default);"></div>
    <!-- <p style="text-align: left; margin: 5px;">
        <strong style="font-weight: bolder">
            Acesse as lojas App Store ou Google Play e instale o aplicativo do SEI! no seu celular.
        </strong>
    </p> -->
    <p style="text-align: left; margin: 15px 5px 5px 5px;">
        <strong style="font-weight: bolder">
            Abra o aplicativo do SEI! e faça a leitura do código abaixo para sincronizá-lo com sua conta.
        </strong>
    </p>
    <img style="margin: 40px auto 9px;" text-align="center" src="data:image/png;base64, <?= MdWsSeiRest::getQRCodeBase64Img() ?>" />
</div>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();

?>