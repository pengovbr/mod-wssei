<?

/**
 * Eduardo Romao
 *
 * 26/03/2017 - criado por ejushiro@gmail.com
 *
 */
class MdWsSeiRest extends SeiIntegracao
{

    /**
     * Converte os dados para UTF8 para ser compativel com json_encode
     * @param $item
     * @return array|string
     */
    public static function dataToUtf8($item){

        if(is_array($item)){
            $itemArr = $item;
        }else if(is_object($item)) {
            $itemArr = get_object_vars($item);
        }else if(is_bool($item)){
            return $item;
        }else{
            return utf8_encode(htmlspecialchars($item));
        }
        $response = array();
        foreach($itemArr as $key => $val){
            $response[$key] = MdWsSeiRest::dataToUtf8($val);
        }
        return $response;
    }

    /**
     * Formata o retorno da mensagem para o padrão do controlador de serviços REST
     * @param null $mensagem
     * @param null $result
     * @param null $total
     * @param bool $jsonEncode - Se alterado para true retornará como json_encode
     * @return array
     */
    public static function formataRetornoSucessoREST($mensagem = null, $result = null, $total = null, $jsonEncode = false){
        $data = array();
        $data['sucesso'] = true;
        if($mensagem){
            $data['mensagem'] = $mensagem;
        }
        if($result){
            $data['data'] = $result;
        }
        if(!is_null($total)){
            $data['total'] = $total;
        }
        $retorno = MdWsSeiRest::dataToUtf8($data);

        return !$jsonEncode ? $retorno : json_encode($retorno);
    }

    /**
     * Formata o retorno da mensagem para o padrão do controlador de serviços REST
     * @param Exception $e
     * @return array
     */
    public static function formataRetornoErroREST(Exception $e){
        $mensagem = $e->getMessage();
        if($e instanceof InfraException){
            if(!$e->getStrDescricao()){
                /** @var InfraValidacaoDTO $validacaoDTO */
                if(count($e->getArrObjInfraValidacao()) == 1){
                    $mensagem = $e->getArrObjInfraValidacao()[0]->getStrDescricao();
                }else{
                    foreach($e->getArrObjInfraValidacao() as $validacaoDTO){
                        $mensagem[] = $validacaoDTO->getStrDescricao();
                    }
                }
            }else{
                $mensagem = $e->getStrDescricao();
            }

        }
        return MdWsSeiRest::dataToUtf8(
            array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            )
        );
    }

    public function __construct()
    {
    }

    public function getNome()
    {
        return 'Módulo de provisionamento de serviços REST do SEI';
    }

    public function getVersao()
    {
        return '1.0.0';
    }

    public function getInstituicao()
    {
        return 'wssei';
    }

    public function inicializar($strVersaoSEI)
    {
        if (substr($strVersaoSEI, 0, 2) != '3.') {
            die('Módulo "' . $this->getNome() . '" (' . $this->getVersao() . ') não e compatível com esta versão do SEI (' . $strVersaoSEI . ').');
        }
    }

    public function montarBotaoControleProcessos()
    {
        return array();
    }

    public function montarIconeControleProcessos($arrObjProcedimentoAPI)
    {
        return array();
    }

    public function montarIconeAcompanhamentoEspecial($arrObjProcedimentoAPI)
    {
        return array();
    }

    public function montarIconeProcesso(ProcedimentoAPI $objProcedimentoAPI)
    {
        return array();
    }

    public function montarBotaoProcesso(ProcedimentoAPI $objProcedimentoAPI)
    {
        return array();
    }

    public function montarIconeDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI)
    {
        return array();
    }

    public function montarBotaoDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI)
    {
        return array();
    }

    public function alterarIconeArvoreDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI)
    {
        return array();
    }

    public function montarMenuPublicacoes()
    {
        return array();
    }

    public function montarMenuUsuarioExterno()
    {
        return array();
    }

    public function montarAcaoControleAcessoExterno($arrObjAcessoExternoAPI)
    {
        return array();
    }

    public function montarAcaoDocumentoAcessoExternoAutorizado($arrObjDocumentoAPI)
    {
        return array();
    }

    public function montarAcaoProcessoAnexadoAcessoExternoAutorizado($arrObjProcedimentoAPI)
    {
        return array();
    }

    public function montarBotaoAcessoExternoAutorizado(ProcedimentoAPI $objProcedimentoAPI)
    {
        return array();
    }

    public function montarBotaoControleAcessoExterno()
    {
        return array();
    }

    public function processarControlador($strAcao)
    {
        return false;
    }

    public function processarControladorAjax($strAcao)
    {
        return null;
    }

    public function processarControladorPublicacoes($strAcao)
    {
        return false;
    }

    public function processarControladorExterno($strAcao)
    {
        return false;
    }

    public function verificarAcessoProtocolo($arrObjProcedimentoAPI, $arrObjDocumentoAPI)
    {
        return null;
    }

    public function verificarAcessoProtocoloExterno($arrObjProcedimentoAPI, $arrObjDocumentoAPI)
    {
        return null;
    }

    public function montarMensagemProcesso(ProcedimentoAPI $objProcedimentoAPI)
    {
        return null;
    }
}

?>