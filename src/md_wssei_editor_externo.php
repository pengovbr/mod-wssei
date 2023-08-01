<?
/**
 * User: Eduardo Romão
 * E-mail: eduardo.romao@outlook.com
 */

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    //////////////////////////////////////////////////////////////////////////////
    InfraDebug::getInstance()->setBolLigado(false);
    InfraDebug::getInstance()->setBolDebugInfra(true);
    InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

  if ($_GET['acao'] == 'editor_salvar' && (isset($_POST['hdnSiglaUnidade']) && $_POST['hdnSiglaUnidade']!=SessaoSEI::getInstance()->getStrSiglaUnidadeAtual())){
      die("INFRA_VALIDACAO\nDetectada troca para a unidade ".SessaoSEI::getInstance()->getStrSiglaUnidadeAtual().".\\nPara salvar este documento é necessário retornar para a unidade ".$_POST['hdnSiglaUnidade'].".");
  }

    PaginaSEI::getInstance()->setTipoPagina(PaginaSEI::$TIPO_PAGINA_SIMPLES);

    SessaoSEI::getInstance()->validarLink();

    SessaoSEI::getInstance()->validarAuditarPermissao(str_replace('md_wssei_', '', str_replace('externo_', '', $_GET['acao'])));

    $strParametros = '';
    $modoAssinatura = 'Default';

  if (isset($_GET['id_procedimento'])){
      $strParametros .= '&id_procedimento='.$_GET['id_procedimento'];
  }

  if (isset($_GET['id_documento'])){
      $strParametros .= '&id_documento='.$_GET['id_documento'];
  }

  if (SessaoSEI::getInstance()->verificarPermissao('documento_assinar')){
      $strLinkAssinatura=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_assinar&acao_origem=editor_montar&acao_retorno=editor_montar&id_documento='.$_GET['id_documento']);
  }

  if (isset($_GET['modo_assinatura'])){
      $modoAssinatura = $_GET['modo_assinatura'];
  }

  if (isset($_GET['id_base_conhecimento'])){
      $strParametros .= '&id_base_conhecimento='.$_GET['id_base_conhecimento'];
  }
    $strLinkAjaxProtocoloLinkEditor = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=protocolo_link_editor');
  switch($_GET['acao']){
    case 'md_wssei_editor_externo_imagem_upload':
      if (isset($_FILES['filArquivo'])){
        PaginaSEI::getInstance()->processarUpload('filArquivo', DIR_SEI_TEMP, false);
      }
        die;
    case 'md_wssei_editor_externo_montar':
      if (isset($_GET['id_documento'])){

          $objDocumentoDTO = new DocumentoDTO();
          $objDocumentoDTO->retDblIdDocumento();
          $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
          $objDocumentoDTO->retStrNomeSerie();
          $objDocumentoDTO->retNumIdConjuntoEstilos();
          $objDocumentoDTO->retStrStaProtocoloProtocolo();
          $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);

          $objDocumentoRN = new DocumentoRN();
          $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

        if ($objDocumentoDTO==null){
          throw new InfraException('Documento não encontrado.', null, null, false);
        }
        if($objDocumentoDTO->getStrStaProtocoloProtocolo()!=ProtocoloRN::$TP_DOCUMENTO_GERADO){
            throw new InfraException('Tipo de Documento inválido.');
        }

          $strTitulo = DocumentoINT::montarTitulo($objDocumentoDTO);

          $objEditorDTO = new EditorDTO();
          $objEditorDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
          $objEditorDTO->setNumIdConjuntoEstilos($objDocumentoDTO->getNumIdConjuntoEstilos());
          $objEditorDTO->setNumIdBaseConhecimento(null);
          $objEditorDTO->setStrSinMontandoEditor('S');

      }else if (isset($_GET['id_base_conhecimento'])){

          $objBaseConhecimentoDTO = new BaseConhecimentoDTO();
          $objBaseConhecimentoDTO->retNumIdBaseConhecimento();
          $objBaseConhecimentoDTO->retStrDescricao();
          $objBaseConhecimentoDTO->retStrSiglaUnidade();
          $objBaseConhecimentoDTO->retNumIdConjuntoEstilos();
          $objBaseConhecimentoDTO->setNumIdBaseConhecimento($_GET['id_base_conhecimento']);

          $objBaseConhecimentoRN = new BaseConhecimentoRN();
          $objBaseConhecimentoDTO = $objBaseConhecimentoRN->consultar($objBaseConhecimentoDTO);

          $strTitulo = BaseConhecimentoINT::montarTitulo($objBaseConhecimentoDTO);

          $objEditorDTO = new EditorDTO();
          $objEditorDTO->setDblIdDocumento(null);
          $objEditorDTO->setNumIdConjuntoEstilos($objBaseConhecimentoDTO->getNumIdConjuntoEstilos());
          $objEditorDTO->setNumIdBaseConhecimento($objBaseConhecimentoDTO->getNumIdBaseConhecimento());
      }else{
          throw new InfraException('Montagem do editor não recebeu documento ou base de conhecimento.');
      }

        $objEditorRN = new EditorRN();
        $objEditorDTORetorno = $objEditorRN->montar($objEditorDTO);

        break;

    case 'editor_salvar':
      if (count($_POST) == 0){
          die("INFRA_VALIDACAO\nNão foi possível salvar o documento.");
      }

        $objEditorDTO = new EditorDTO();

      if (!InfraString::isBolVazia($_GET['id_documento'])){
          $objEditorDTO->setDblIdDocumento($_GET['id_documento']);
          $objEditorDTO->setNumIdBaseConhecimento(null);
      }else if (!InfraString::isBolVazia($_GET['id_base_conhecimento'])){
          $objEditorDTO->setDblIdDocumento(null);
          $objEditorDTO->setNumIdBaseConhecimento($_GET['id_base_conhecimento']);
      }
        $objEditorDTO->setNumVersao($_POST['hdnVersao']);
        $objEditorDTO->setStrSinIgnorarNovaVersao($_POST['hdnIgnorarNovaVersao']);

        $arrObjSecaoDocumentoDTO = array();
        $numTamPrefixo = strlen('txaEditor_');
      foreach($_POST as $chave => $valor){
        if (substr($chave, 0, $numTamPrefixo)=='txaEditor_'){
            $objSecaoDocumentoDTO = new SecaoDocumentoDTO();
            $objSecaoDocumentoDTO->setNumIdSecaoModelo(substr($chave, $numTamPrefixo));
            $objSecaoDocumentoDTO->setStrConteudo($valor);
            $arrObjSecaoDocumentoDTO[] = $objSecaoDocumentoDTO;
        }
      }

        $objEditorDTO->setArrObjSecaoDocumentoDTO($arrObjSecaoDocumentoDTO);

      try{

          $objEditorRN = new EditorRN();
          $numVersao = $objEditorRN->adicionarVersao($objEditorDTO);

          die('OK '.$numVersao);

      }catch(Exception $e){

        if ($e instanceof InfraException && $e->contemValidacoes()){
            die("INFRA_VALIDACAO\n".$e->__toString()); //retorna para o iframe exibir o alert
        }

          PaginaSEI::getInstance()->processarExcecao($e); //vai para a página de erro padrão
      }

        break;


    default:
        throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }
    $objTextoPadraoInternoRN=new TextoPadraoInternoRN();
    $objTextoPadraoInternoDTO= new TextoPadraoInternoDTO();
    $objTextoPadraoInternoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
    $objTextoPadraoInternoDTO->retStrNome();
    $objTextoPadraoInternoDTO->retStrConteudo();
    $objTextoPadraoInternoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $arrObjTextoPadraoInternoDTO=$objTextoPadraoInternoRN->listar($objTextoPadraoInternoDTO);
    $strItens='[  ';
    $strTextos='[  ';
  if (count($arrObjTextoPadraoInternoDTO)>0){

    foreach($arrObjTextoPadraoInternoDTO as $objTextoPadraoInternoDTO){
        $strItens  .= '"'.str_replace('"', '\"', $objTextoPadraoInternoDTO->getStrNome()).'", ';
        $strTexto=str_replace('"', '\"', $objTextoPadraoInternoDTO->getStrConteudo());
        $strTexto=str_replace("\n", '', $strTexto);
        $strTexto=str_replace("\r", '', $strTexto);
        $strTextos .= '"'.$strTexto.'", ';
    }

  }
    $strItens=substr($strItens, 0, strlen($strItens)-2)."]";
    $strTextos=substr($strTextos, 0, strlen($strTextos)-2)."]";

    $objImagemFormatoDTO = new ImagemFormatoDTO();
    $objImagemFormatoDTO->retStrFormato();
    $objImagemFormatoDTO->setBolExclusaoLogica(false);

    $objImagemFormatoRN = new ImagemFormatoRN();

    $arrImagemPermitida = InfraArray::converterArrInfraDTO($objImagemFormatoRN->listar($objImagemFormatoDTO), 'Formato');
  if (in_array('jpg', $arrImagemPermitida) && !in_array('jpeg', $arrImagemPermitida)) { $arrImagemPermitida[]='jpeg';
  }
    $strArrImgPermitida = "'".implode('\',\'', $arrImagemPermitida)."'";
    $strArrImgPermitida = 'var arrImgPermitida = Array('.InfraString::transformarCaixaBaixa($strArrImgPermitida).');'."\n";

}catch(Exception $e){
    PaginaSEI::getInstance()->processarExcecao($e);
}
?>
<!DOCTYPE html>
<html lang="pt-br" >
<head>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <meta http-equiv="X-UA-Compatible" content="IE=8,9,10,11">
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Content-Type" content="text/html;" />
    <meta name="format-detection" content="telephone=no" />
    <title>::<?=$strTitulo?>::</title>
    <?
    PaginaSEI::getInstance()->montarJavaScript();
    ?>
    <style type="text/css" >
        <!--/*--><![CDATA[/*><!--*/
        .cke_combo__styles .cke_combo_text {width:230px !important;}
        .cke_button__save_label {display:inline !important;}
        .cke_button__autotexto_label {display:inline !important;}
        .cke_button__assinatura_label {display:inline !important;}
        .cke_combopanel__styles {width:500px !important;}
        /** desabilitando botoes do SEI para versao externa */
        .cke_button__source, .cke_button__base64image,
        .cke_button__subscript, .cke_button__superscript, .cke_button__maiuscula,
        .cke_button__minuscula, .cke_button__find, .cke_button__replace, .cke_toolbar_separator,
        .cke_button__removeformat, .cke_button__autotexto, .cke_combo__styles,
        .cke_toolbar:nth-child(5), .cke_toolbar:nth-child(7) > .cke_toolgroup:nth-child(2)
        {display: none !important;}
        div.infraAreaDebug{
            overflow:auto;
            display:table;
            white-space:pre-wrap;
            font-size: 1em;
            width:100%;
        }
        div.infraAviso {
            position:absolute;
            padding:.4em;
            border:.1em solid black;
            background-color:#dfdfdf;
            z-index:999;
        }

        div.infraAviso span{
            /* font-family:Verdana, Arial, Helvetica, sans-serif;  */
            font-weight:bold;
            font-size:1.2em;
        }

        div.infraFundoTransparente{
            z-index:997;
            visibility:hidden;
            position:absolute;
            overflow:hidden;
            width:1px;
            height:1px;
            left:0;
            top:0;
            background:transparent url("/infra_css/imagens/fndtransp.gif");
            background-repeat:repeat;
        }
        div.editorSomenteLeitura{background:#e5e5e5;border-color:#696969;border-style: dotted solid;border-width:1px;}
        #myDiv p {white-space:pre-wrap}

        <?=$objEditorDTORetorno->getStrCss();?>
        /*]]>*/-->
    </style>
    <?=$objEditorDTORetorno->getStrInicializacao()?>
    <?
    //if (PaginaSEI::getInstance()->getNumVersaoInternetExplorer()>0) {
    //  echo '<script type="text/javascript" src="'.ConfiguracaoSEI::getInstance()->getValor('SEI','URL').'/editor/ck/jquery-1.10.2.js"></script>';
    //}
    ?>
    <script type="text/javascript">//<![CDATA[

        if (document.documentMode==7){
            alert("Não é possível inicializar corretamente o editor.\nCerifique-se que o modo de compatibilidade esteja desativado.");
            window.CKEDITOR=undefined;
        }
        <?
            //se houver validações, só exibe as validações e descarta o resto do javascript
        if ($objEditorDTORetorno->getStrValidacao()==true){
          ?>
        var verificarSalvamento=function(){};
        function  inicializar() {
            document.getElementById('frmEditor').hidden = false;
            document.getElementById('divCarregando').style.display = "none";
            if (INFRA_IOS) {
                document.getElementById('divEditores').style.overflow = 'scroll';
            }
            alert('<?=$objEditorDTORetorno->getStrMensagens();?>');
        }
          <?
        } else {
          ?>
          <?=$strArrImgPermitida;?>

        function infraUploadCK(form,url,debug){
            var me = this;
            this.frm=form;
            this.url = url;
            this.debug = debug;
            this.ifr = document.getElementById('ifr'+this.frm.id);
            if (this.ifr!=null) {
                this.ifr.removeNode();
                this.ifr=null;
            }
            this.inputFile = null;
            this.bolExecutando = false;
            this.ifr = document.createElement("iframe");
            this.ifr.setAttribute("id","ifr"+this.frm.id);
            this.ifr.setAttribute("name","ifr"+this.frm.id);
            this.ifr.setAttribute("width","0");
            this.ifr.setAttribute("height","0");
            this.ifr.setAttribute("border","0");
            this.ifr.setAttribute("style","width:0;height:0;border:none;");
            document.body.appendChild(this.ifr);
            infraAdicionarEvento(this.ifr,'load',
                function(){
                    if (typeof(me.finalizou)=='function'){
                        if (INFRA_IE == 0){
                            ret = this.contentWindow.document.body.innerHTML;
                        }else{
                            ret = window.frames['ifr'+me.frm.id].document.body.innerHTML;
                        }
                        var arr = null;
                        if (me.bolExecutando){
                            arr = ret.split("#");
                            me.btnUploadCancelar.style.display="none";
                            if (arr.length!=2 && arr.length!=5){
                                if (me.debug==true){
                                    alert('Erro desconhecido realizando upload de arquivo:\n' + ret);
                                } else {
                                    alert('Erro desconhecido realizando upload de arquivo.');
                                }
                            } else{
                                if (arr[0]=='ERRO'){
                                    alert(arr[1]);
                                } else{
                                    var ret = Array();
                                    ret['nome_upload'] = arr[0].infraReplaceAll('\\\'','');
                                    ret['nome'] = arr[1].infraReplaceAll('\\\'','');
                                    ret['tipo'] = arr[2];
                                    ret['tamanho'] = arr[3];
                                    ret['data_hora'] = arr[4];
                                    me.finalizou(ret);
                                }
                            }
                            if (INFRA_IE > 0){
                                window.status='Finalizado.';
                            }
                            me.bolExecutando = false;
                        }
                    }
                });
            if (INFRA_IE==7) {
                window.frames['ifr'+this.frm.id].name="ifr"+this.frm.id;
            }
            this.frm.setAttribute("target","ifr"+this.frm.id);
            this.frm.setAttribute("method","post");
            this.frm.setAttribute("enctype","multipart/form-data");
            this.frm.setAttribute("encoding","multipart/form-data");
            this.frm.setAttribute("action", me.url);
            var nlist = this.frm.getElementsByTagName('input');
            for (var i = 0; i < nlist.length; i++) {
                var node = nlist[i];
                if (node.getAttribute('type') == 'file') {
                    this.inputFile=node;
                    break;
                }
            }
            this.btnUploadCancelar = document.createElement("button");
            this.btnUploadCancelar.setAttribute("id","btnUploadCancelar"+this.frm.id);
            this.btnUploadCancelar.setAttribute("name","btnUploadCancelar"+this.frm.id);
            this.btnUploadCancelar.setAttribute("class","infraButton");
            this.btnUploadCancelar.setAttribute("value","Cancelar");
            this.btnUploadCancelar.setAttribute("style","position:absolute;font-size:0.8em;");
            this.btnUploadCancelar.style.display='none';
            this.btnUploadCancelar.textContent="Cancelar";
            if (INFRA_IE > 0){
                this.btnUploadCancelar.innerText="Cancelar";
            }
            this.btnUploadCancelar.onclick=function(){
                me.btnUploadCancelar.style.display="none";
            }
            this.btnUploadCancelar.style.top = this.inputFile.offsetTop + "px";
            this.btnUploadCancelar.style.left = this.inputFile.offsetLeft + this.inputFile.offsetWidth - 80 + "px";
            this.inputFile.parentNode.appendChild(this.btnUploadCancelar);

            this.executar = function(){
                if (typeof(me.validar)=='function'){
                    if (!me.validar()){
                        return;
                    }
                }
                me.btnUploadCancelar.style.display="";
                me.bolExecutando = true;
                me.frm.submit();
            }
            if (window.attachEvent) { //Limpar as referências do IE
                window.attachEvent("onunload", function(){
                    me.ifr = null;
                    me.frm = null;
                    me = null;
                });
            }
        }

        objAjax = new infraAjaxComplementar(null,'<?=$strLinkAjaxProtocoloLinkEditor?>');
        objAjax.limparCampo = false;
        objAjax.mostrarAviso = false;
        objAjax.tempoAviso = 1000;
        objAjax.async=false;

        objAjax.prepararExecucao = function(){
            window._idProtocolo = '';
            window._protocoloFormatado = '';
            return 'idProtocoloDigitado='+window._procedimento+"&idProcedimento=<?=$_GET["id_procedimento"];?>&idDocumento=<?=$_GET["id_documento"];?>";
        };

        objAjax.processarResultado = function (arr){
            if (arr!=null){
                window._idProtocolo = arr['IdProtocolo'];
                window._protocoloFormatado = arr['ProtocoloFormatado'];
            }
        };
        var _procedimento='';
        var _idProtocolo='';
        var _protocoloFormatado='';
        CKEDITOR.config.contentsCss="<?=str_replace('"', '\"', $objEditorDTORetorno->getStrCss());?>";
        var toolbar=<?=$objEditorDTORetorno->getStrToolbar();?>;
        var timeoutExibirBotao = null;

        function plugin_save(evt){

            CKEDITOR.plugins.registered['save']={
                init:function(editor){
                    var command=editor.addCommand( 'save',{
                        modes:{wysiwyg:1,source:1},
                        readOnly:1,
                        exec:function(editor){
                            if ( editor.fire( 'save' ) ) {
                                var $form=editor.element.$.form;
                                if (validarTags()){
                                    exibirAvisoEditor();
                                    timeoutExibirBotao = self.setTimeout('exibirBotaoCancelarAviso()',30000);
                                    if (INFRA_IE>0) {
                                        window.tempoInicio=(new Date()).getTime();
                                    } else {
                                        console.time('s'); }
                                    if($form){
                                        try{
                                            $form.submit();
                                        }catch(e){
                                            if($form.submit.click){
                                                $form.submit.click();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                    editor.ui.addButton('Save',{label:'Salvar',command:'save'});
                }
            }
        }
        if(CKEDITOR.status!='loaded'){
            CKEDITOR.on('loaded',plugin_save);
        } else {
            plugin_save(null);
        }

        function exibirBotaoCancelarAviso(){

            var div = document.getElementById('divInfraAvisoFundo');

            if (div!=null && div.style.visibility == 'visible'){

                var botaoCancelar = document.getElementById('btnInfraAvisoCancelar');

                if (botaoCancelar != null){
                    botaoCancelar.style.display = 'block';
                }
            }
        }

        function exibirAvisoEditor(){

            var divFundo = document.getElementById('divInfraAvisoFundo');

            if (divFundo==null){
                divFundo = infraAviso(false, 'Salvando...');
            }else{
                document.getElementById('btnInfraAvisoCancelar').style.display = 'none';
                document.getElementById('imgInfraAviso').src='/infra_css/imagens/aguarde.gif';
            }

            if (INFRA_IE==0 || INFRA_IE>=7){
                divFundo.style.position = 'fixed';
            }

            var divAviso = document.getElementById('divInfraAviso');

            divAviso.style.top = Math.floor(infraClientHeight()/3) + 'px';
            divAviso.style.left = Math.floor((infraClientWidth()-200)/2) + 'px';
            divAviso.style.width = '200px';
            divAviso.style.border = '1px solid black';

            divFundo.style.width = screen.width*2 + 'px';
            divFundo.style.height = screen.height*2 + 'px';
            divFundo.style.visibility = 'visible';

        }

        var timer=0;
        var realceTimer=null;
        var timerAlertaSalvar=null;
        var arrAutotextoItens=<?if($strTextos!=null) { echo $strTextos;
                              } else { echo '[]';
                              }?>;
        var selAutotextoItens=<?if($strItens!=null) { echo $strItens;
                              } else { echo '[]';
                              }?>;
        var strLinkAssinatura='<?=$strLinkAssinatura;?>';
        var readOnlyColor='#e5e5e5';
        var modificado=false;
        var botoes=[];
        var habilitaSalvar = function (evt) {

            if (!modificado && timer==0) {
                timer=setTimeout( function() {
                    timer=0;
                    if (evt.name=='drop' || evt.editor.checkDirty())  modificado=true;
                    if (modificado) {
                        if (document.getElementsByClassName) {
                            botoes=document.getElementsByClassName('cke_button__save');
                        } else {
                            //função que substitui a getElementsByClassName
                            var rx = new RegExp("(?:^|\\s)" + 'cke_button__save' + "(?:$|\\s)");
                            var allT = document.getElementsByTagName("*"), allCN = [], ac="", i = 0, a;
                            while (a = allT[i=i+1]) {
                                ac=a.className;
                                if ( ac && ac.indexOf('cke_button__save') !==-1) {
                                    if(ac==='cke_button__save'){ allCN[allCN.length] = a; continue;   }
                                    rx.test(ac) ? (allCN[allCN.length] = a) : 0;
                                }
                            }
                            botoes=allCN;
                        }

                        if (timerAlertaSalvar==null) {
                            timerAlertaSalvar=setTimeout(realcarBotao,600000);//tempo para iniciar o realce do botão default 10min=600.000
                        }

                        for (inst in CKEDITOR.instances) {
                            CKEDITOR.instances[inst].getCommand('save').setState(CKEDITOR.TRISTATE_ENABLED);
                        }

                    }
                },100);
            }
        }
        var realcarBotao=function() {
            if (modificado)
                if(!realceTimer) {
                    realceTimer=setInterval(function(){flashIt();},1000);//intervalo que alterna a cor - default = 1s
                }
        }
        var normalizarBotao= function(){
            if (realceTimer) {
                clearInterval(realceTimer);
                realceTimer=null;
            }
            for(var k=botoes.length-1;k>=0;k--) {
                var estilo=botoes[k].style;
                estilo.backgroundColor="#efefde";
            }
        }

        var flashIt = function() {
            var corPadrao="#efefde";
            var corRealce="#ff2400";
            if (INFRA_IE==0|| INFRA_IE>8) {
                corPadrao='rgb(239, 239, 222)';
            }
            for(var k=botoes.length-1;k>=0;k--) {
                var estilo=botoes[k].style;
                estilo.backgroundColor=(estilo.backgroundColor==corPadrao)?corRealce:corPadrao;
            }
        }

        var desabilitaSalvar = function() {
            modificado=false;
            if (timerAlertaSalvar!=null) {
                clearTimeout(timerAlertaSalvar);
                timerAlertaSalvar=null;
            }
            normalizarBotao();


            for (inst in CKEDITOR.instances) {
                CKEDITOR.instances[inst].getCommand('save').setState(CKEDITOR.TRISTATE_DISABLED);
                CKEDITOR.instances[inst].resetDirty();
            }

        }

        var validarTags = function () {

            for (inst in CKEDITOR.instances) {
                var editor = CKEDITOR.instances[inst];
                if (!editor.readOnly) {
                    var tags = ['img', 'button', 'input', 'select', 'iframe', 'frame', 'embed', 'object', 'param', 'video', 'audio', 'form'];
                    for (var i = 0; i < tags.length; i++) {
                        var elements = editor.document.getElementsByTag(tags[i]);
                        if (elements.count() > 0) {
                            switch (tags[i]) {
                                case 'img':
                                    var erro=false;
                                    if (arrImgPermitida.length == 0) {
                                        alert('Não são permitidas imagens no conteúdo.');
                                        erro=true;
                                        break;
                                    } else {
                                        var posIni = null;
                                        var posFim = null;
                                        var n = elements.count();
                                        for (var j = 0; j < n; j++) {
                                            ImgSrc = elements.getItem(j).getAttribute('src');
                                            posIni = ImgSrc.indexOf('/');
                                            if (posIni != -1) {
                                                posFim = ImgSrc.indexOf(';', posIni);
                                                if (posFim != -1) {
                                                    posIni = posIni + 1;
                                                    if (arrImgPermitida.indexOf(ImgSrc.substr(posIni, (posFim - posIni))) == -1) {
                                                        alert('Imagem formato "' + ImgSrc.substr(posIni, (posFim - posIni)) + '" não permitida.');
                                                        erro=true;
                                                        break;
                                                    }
                                                } else {
                                                    alert('Não são permitidas imagens referenciadas.');
                                                    erro=true;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    if (erro) break;
                                    continue;
                                case 'button':
                                case 'input':
                                case 'select':
                                    alert('Não são permitidos componentes de formulário HTML no conteúdo.');
                                    break;

                                case 'iframe':
                                    alert('Não são permitidos formulários ocultos no conteúdo.');
                                    break;

                                case 'frame':
                                case 'form':
                                    alert('Não são permitidos formulários no conteúdo.');
                                    break;

                                case 'embed':
                                case 'object':
                                case 'param':
                                    alert('Não são permitidos objetos no conteúdo.');
                                    break;

                                case 'video':
                                    alert('Não são permitidos vídeos no conteúdo.');
                                    break;

                                case 'audio':
                                    alert('Não é permitido áudio no conteúdo.');
                                    break;
                            }

                            editor.getSelection().selectElement(elements.getItem(0));
                            document.getElementById('divEditores').scrollTop = editor.getSelection().getSelectedElement().$.offsetTop;
                            var div = '<div id="divRealce" style="border:1px dashed red"><div>' + editor.getSelection().getSelectedElement().$.outerHTML + '</div></div>';
                            editor.insertHtml(div);
                            editor.focus();
                            return false;
                        }
                    }
                }
            }
            return true;
        }

        function  inicializar(){

            var modoAssinatura = '<?=$modoAssinatura?>';
            document.getElementById('divEditores').style.overflow='scroll';

            CKEDITOR.config.zoom = infraLerCookie('<?=PaginaSEI::getInstance()->getStrPrefixoCookie()?>_zoom_editor');
            if (CKEDITOR.config.zoom==null) CKEDITOR.config.zoom=50;
            CKEDITOR.on('instanceReady', function( evt ) {
                switch(modoAssinatura){
                    case 'evento':
                        for (inst in CKEDITOR.instances) {
                            CKEDITOR.instances[inst].getCommand('assinatura').exec = function (a) {
                                CKEDITOR.instances[inst].getCommand("save").state == CKEDITOR.TRISTATE_ENABLED ? 1 == confirm("Deseja salvar as alterações e assinar?") &&
                                (CKEDITOR.instances[inst].execCommand("save"), window.bolAssinar = !0) : window.parent.postMessage('assinar', '*');
                            }
                        }
                        break;
                }
                redimensionar();

                if (INFRA_IE<9 && INFRA_IE>0){evt.editor.document.$.body.ondrop=function(){var evt={name:'drop'};habilitaSalvar(evt);}}
                else evt.editor.document.on('drop', function (evt) { habilitaSalvar(evt); });
                evt.editor.on( 'beforepaste', function( evt ) {
                    if ( evt.data && !evt.data.$.ctrlKey && !evt.data.$.shiftKey )
                        if(evt.data.type=='auto') evt.data.type='html';
                } );
                evt.editor.on("paste", function(ev){
                    if(CKEDITOR.env.chrome){
                        ev.data.dataValue = ev.data.dataValue.replace(/(<span)(><a[^>]*href="[^>]*controlador\.php\?acao=protocolo_visualizar(?:&|&amp;)id_protocolo=)(\d+)([^>]*>)(\d{7})(<\/a><\/span>)/gi, '$1 contenteditable="false" style="text-indent:0;"><a class="ancoraSei" id="lnkSei$3" style="text-indent:0;">$5$6');
                    }
                    ev.data.dataValue = ev.data.dataValue.replace(/<o:p>/gi, "");
                    ev.data.dataValue = ev.data.dataValue.replace(/<\/o:p>/gi, "");
                    ev.data.dataValue = ev.data.dataValue.replace(/<font\b[^>]*>/gi, "");
                    ev.data.dataValue = ev.data.dataValue.replace(/<\/font>/gi, "");
                    ev.data.dataValue = ev.data.dataValue.replace(/<link[^<>]*\/>/gi, "");
                    ev.data.dataValue = ev.data.dataValue.replace(/<script[^<>]*\/>/gi, "");
                    ev.data.dataValue = ev.data.dataValue.replace(/<script[^<>]*\/script>/gi, "");
                    ev.data.dataValue = ev.data.dataValue.replace(/<object[^<>]*\/>/gi, "");
                    ev.data.dataValue = ev.data.dataValue.replace(/<object[^<>]*\/object>/gi, "");
                    ev.data.dataValue = ev.data.dataValue.replace(/(<table[^<>]*border=)("0"|0)([^<>]*>)/gi, '$1"1"$3');
                    ev.data.dataValue = ev.data.dataValue.replace(/(<a[^<>]*)(target="[^<>]*")([^<>]*>)/gi, "$1$3");
                    ev.data.dataValue = ev.data.dataValue.replace(/(<a[^<>]*)[^<>]*(>)/gi, '$1 target="_blank"$2');
                    if (ev.data.type=='text'){
                        ev.data.dataValue = ev.data.dataValue.replace(/&nbsp;/gi,' ');
                    }
                });

                evt.editor.getCommand('save').setState(CKEDITOR.TRISTATE_DISABLED);

                if (evt.editor.readOnly==true) {
                    evt.editor.document.$.body.style.backgroundColor=readOnlyColor;
                } else {
                    evt.editor.on('saveSnapshot', habilitaSalvar);
                    evt.editor.on('key', habilitaSalvar);
                    evt.editor.on('afterCommandExec', habilitaSalvar);
                    evt.editor.on('tableResize', habilitaSalvar);
                    //evt.editor.on('paste', CK_jQ);
                }
            });

            CKEDITOR.config.disableNativeSpellChecker = true;

            if ('<?=$objEditorDTORetorno->getStrValidacao();?>'=='1'){
                alert('<?=$objEditorDTORetorno->getStrMensagens();?>');
            }
            document.getElementById('frmEditor').hidden=false;
            document.getElementById('divCarregando').style.display="none";
        }

        function CK_jQ(){
            for (inst in CKEDITOR.instances) {
                CKEDITOR.instances[inst].updateElement();
            }
        }

        function redimensionar() {
            setTimeout(function(){

                var tamComandos=document.getElementById('divComandos').offsetHeight;
                var divEd=document.getElementById('divEditores');
                if (tamComandos>divEd.offsetHeight) tamComandos-=divEd.offsetHeight;
                var tamEditor=infraClientHeight()- tamComandos - 20;
                divEd.style.height = (tamEditor>0?tamEditor:1) +'px';
            },0);
        }

        function verificarSalvamento(){
            var ie = infraVersaoIE();

            try{
                if (!ie){
                    docIframe = document.getElementById('ifrEditorSalvar').contentWindow.document;
                }else{
                    docIframe = window.frames['ifrEditorSalvar'].document;
                }
            }catch(e){
                infraOcultarAviso();
                alert('Não foi possível salvar o documento.');
                return;
            }

            ret = docIframe.body.innerHTML;

            document.getElementById('hdnIgnorarNovaVersao').value = 'N';

            clearTimeout(timeoutExibirBotao);

            if (ret != ''){
                if (ret.substring(0,2) != 'OK'){
                    var prefixoValidacao = 'INFRA_VALIDACAO';
                    if (ret.substr(0,15) == prefixoValidacao){
                        var msg = ret.substr(prefixoValidacao.length+1);
                        msg = msg.infraReplaceAll("\\n", "\n");
                        msg = decodeURIComponent(msg);

                        var prefixoNovaVersao = 'Existe uma nova versão';

                        if (msg.substr(0,prefixoNovaVersao.length) == prefixoNovaVersao){
                            if (confirm(msg + "\n\n" + 'Ignorar as alterações e salvar o conteúdo atual como última versão?')){
                                document.getElementById('hdnIgnorarNovaVersao').value = 'S';
                                for (inst in CKEDITOR.instances) {
                                    CKEDITOR.instances[inst].execCommand('save');
                                    return;
                                }
                            }
                        }else{
                            alert(msg);
                        }
                    }else{

                        try{
                            if (docIframe.getElementById('divInfraExcecao')==null){
                                alert('Erro desconhecido salvando documento: \n'  + ret);
                            }else{
                                document.getElementById("ifrEditorSalvar").style.display = 'block';
                                document.getElementById('frmEditor').style.display = 'none';
                                docIframe.getElementById('btnInfraFecharExcecao').value = 'Voltar';
                                if (!ie){
                                    docIframe.getElementById('btnInfraFecharExcecao').innerHTML = 'Voltar';
                                }
                                docIframe.getElementById('btnInfraFecharExcecao').onclick = function() {
                                    document.getElementById("ifrEditorSalvar").style.display = 'none';
                                    document.getElementById('frmEditor').style.display = 'block';
                                }
                            }
                        }catch(e){}
                    }
                }else{

                    document.getElementById('hdnVersao').value = ret.substr(3);

                    var spn = null;
                    for (var inst in CKEDITOR.instances) {
                        if (CKEDITOR.instances[inst].config.dinamico){
                            spn = CKEDITOR.instances[inst].document.getById("spnVersao");
                            if (spn != null){
                                if (spn.getHtml()==ret.substr(3)){
                                    alert('Nenhuma alteração foi encontrada no conteúdo do documento.');
                                }else{
                                    spn.setHtml(ret.substr(3));
                                }
                            }
                            CKEDITOR.instances[inst].resetDirty();
                        }
                    }
                    desabilitaSalvar();
                }

                infraOcultarAviso();
                atualizarArvore(false);
                if (window.bolAssinar) {
                    infraAbrirJanela(window.strLinkAssinatura,'janelaAssinatura',700,450,'location=0,status=1,resizable=1,scrollbars=1');
                    window.bolAssinar=false;
                }
                if (INFRA_IE){
                    window.status='Salvamento finalizado.';
                }
            }
        }

        window.onbeforeunload = function(evt){
            if (modificado){
                return 'Existem alterações que não foram salvas.';
            }
        };
        <?}?>
        //]]></script>
</head>
<body onload="inicializar();" style="margin: 5px;">
<div id='divCarregando'><h2>Carregando...</h2></div>
<form id="frmEditor" style="hidden:true;margin: 0px;" method="post" target="ifrEditorSalvar" action="<?=SessaoSEI::getInstance()->assinarLink('editor/editor_processar.php?acao=editor_salvar&acao_origem='.$_GET['acao'].$strParametros)?>">
    <div id="divComandos" style="margin:0px;"></div>
    <?
    if (PaginaSEI::getInstance()->getNumTipoBrowser()==InfraPagina::$TIPO_BROWSER_IE7 ) { echo '<br style="margin:0;font-size:1px;"/>';
    }
    ?>
    <div id="divEditores" style="overflow: auto;border-top:2px solid;border-bottom:0px;">

        <?=$objEditorDTORetorno->getStrTextareas();?>
        <script type="text/javascript">
            <?=$objEditorDTORetorno->getStrEditores()?>
        </script>
    </div>
    <input type="hidden" id="hdnVersao" name="hdnVersao" value="<?=$objEditorDTORetorno->getNumVersao()?>" />
    <input type="hidden" id="hdnIgnorarNovaVersao" name="hdnIgnorarNovaVersao" value="N" />
    <input type="hidden" id="hdnSiglaUnidade" name="hdnSiglaUnidade" value="<?=SessaoSEI::getInstance()->getStrSiglaUnidadeAtual()?>" />
    <?
    PaginaSEI::getInstance()->montarAreaDebug();
    ?>
    <input type="hidden" id="hdnInfraPrefixoCookie" name="hdnInfraPrefixoCookie" value="<?=PaginaSEI::getInstance()->getStrPrefixoCookie()?>" />
</form>
<iframe id="ifrEditorSalvar" name="ifrEditorSalvar" onload="verificarSalvamento();" border="0" width="100%" height="100%" style="display:none;"></iframe>
</body>
</html>
