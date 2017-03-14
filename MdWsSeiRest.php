<?
/**
 * Eduardo Romao
 *
 * 26/03/2017 - criado por ejushiro@gmail.com
 *
 */

class MdWsSeiRest extends SeiIntegracao{

  public function __construct(){
  }

  public function getNome(){
    return 'Módulo de provisionamento de serviços REST do SEI';
  }

  public function getVersao() {
    return '1.0.0';
  }

  public function getInstituicao(){
    return 'wssei';
  }

  public function inicializar($strVersaoSEI){
    if (substr($strVersaoSEI, 0, 2) != '3.'){
      die('Módulo "'.$this->getNome().'" ('.$this->getVersao().') não e compatível com esta versão do SEI ('.$strVersaoSEI.').');
    }
  }

  public function montarBotaoControleProcessos(){
      return array();
  }

  public function montarIconeControleProcessos($arrObjProcedimentoAPI){
      return array();
  }

  public function montarIconeAcompanhamentoEspecial($arrObjProcedimentoAPI){
      return array();
  }

  public function montarIconeProcesso(ProcedimentoAPI $objProcedimentoAPI){
      return array();
  }

  public function montarBotaoProcesso(ProcedimentoAPI $objProcedimentoAPI){
      return array();
  }

  public function montarIconeDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI){
      return array();
  }

  public function montarBotaoDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI){
      return array();
  }

  public function alterarIconeArvoreDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI){
      return array();
  }

  public function montarMenuPublicacoes(){
      return array();
  }

  public function montarMenuUsuarioExterno(){
      return array();
  }

  public function montarAcaoControleAcessoExterno($arrObjAcessoExternoAPI){
      return array();
  }

  public function montarAcaoDocumentoAcessoExternoAutorizado($arrObjDocumentoAPI){
      return array();
  }

  public function montarAcaoProcessoAnexadoAcessoExternoAutorizado($arrObjProcedimentoAPI){
      return array();
  }

  public function montarBotaoAcessoExternoAutorizado(ProcedimentoAPI $objProcedimentoAPI){
      return array();
  }

  public function montarBotaoControleAcessoExterno(){
      return array();
  }

  public function processarControlador($strAcao){
    return false;
  }

  public function processarControladorAjax($strAcao){
    return null;
  }

  public function processarControladorPublicacoes($strAcao){
    return false;
  }

  public function processarControladorExterno($strAcao){
    return false;
  }

  public function verificarAcessoProtocolo($arrObjProcedimentoAPI, $arrObjDocumentoAPI){
    return null;
  }

  public function verificarAcessoProtocoloExterno($arrObjProcedimentoAPI, $arrObjDocumentoAPI){
    return null;
  }

  public function montarMensagemProcesso(ProcedimentoAPI $objProcedimentoAPI){
    return null;
  }
}
?>