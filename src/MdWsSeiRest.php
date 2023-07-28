<?

/**
 * Eduardo Romao
 *
 * 26/03/2017 - criado por ejushiro@gmail.com
 *
 */
class MdWsSeiRest extends SeiIntegracao
{
    const NOME_MODULO = "MdWsSeiRest";
    const VERSAO_MODULO = "2.1.1";

    public function getNome()
    {
        return 'M�dulo de servi�os REST';
    }

    public function getVersao()
    {
        return self::VERSAO_MODULO;
    }

    public function getInstituicao()
    {
        return 'ME - Minist�rio da Economia';
    }


    /**
     * Converte os dados para UTF8 para ser compativel com json_encode
     * @param $item
     * @return array|string
     */
    public static function dataToUtf8($item)
    {

        if (is_array($item)) {
            $itemArr = $item;
        } else if (is_object($item)) {
            $itemArr = get_object_vars($item);
        } else if (is_bool($item)) {
            return $item;
        } else {
            return utf8_encode(htmlspecialchars($item));
        }
        $response = array();
        foreach ($itemArr as $key => $val) {
            $response[$key] = MdWsSeiRest::dataToUtf8($val);
        }
        return $response;
    }

    public static function dataToIso88591($item)
    {
        if (is_array($item)) {
            $itemArr = $item;
        } else if (is_object($item)) {
            $itemArr = get_object_vars($item);
        } else if (is_bool($item)) {
            return $item;
        } else {
            return mb_convert_encoding($item, 'ISO-8859-1');
        }
        $response = array();
        foreach ($itemArr as $key => $val) {
            $response[$key] = MdWsSeiRest::dataToIso88591($val);
        }
        return $response;
    }

    /**
     * Formata o retorno da mensagem para o padr�o do controlador de servi�os REST
     * @param null $mensagem
     * @param null $result
     * @param null $total
     * @param bool $jsonEncode - Se alterado para true retornar� como json_encode
     * @return array
     */
    public static function formataRetornoSucessoREST($mensagem = null, $result = null, $total = null, $jsonEncode = false)
    {
        $data = array();
        $data['sucesso'] = true;
        if ($mensagem) {
            $data['mensagem'] = $mensagem;
        }
        if ($result) {
            $data['data'] = $result;
        }
        if (!is_null($total)) {
            $data['total'] = $total;
        }
        $retorno = MdWsSeiRest::dataToUtf8($data);

        return !$jsonEncode ? $retorno : json_encode($retorno);
    }

    /**
     * Formata o retorno da mensagem para o padr�o do controlador de servi�os REST
     * @param Exception $e
     * @return array
     */
    public static function formataRetornoErroREST(Exception $e)
    {
        $mensagem = $e->getMessage();
        if ($e instanceof InfraException) {
            if (!$e->getStrDescricao()) {
                /** @var InfraValidacaoDTO $validacaoDTO */
                if (count($e->getArrObjInfraValidacao()) == 1) {
                    $mensagem = $e->getArrObjInfraValidacao()[0]->getStrDescricao();
                } else {
                    foreach ($e->getArrObjInfraValidacao() as $validacaoDTO) {
                        $mensagem[] = $validacaoDTO->getStrDescricao();
                    }
                }
            } else {
                $mensagem = $e->getStrDescricao();
            }

        }
        
        $strmensagemErro = InfraException::inspecionar($e);
        return array(
                "sucesso" => false,
                "mensagem" => MdWsSeiRest::dataToUtf8($mensagem),
                "exception" => MdWsSeiRest::dataToUtf8($strmensagemErro)
        );
    }

    public function __construct()
    {
    }

    /**
     * M�todo que verifica se o m�dulo esta ativo nas configura��es do SEI
     */
    public static function moduloAtivo()
    {
        global $SEI_MODULOS;
        $ativo = false;
        foreach ($SEI_MODULOS as $modulo) {
            if ($modulo instanceof self) {
                $ativo = true;
                break;
            }
        }
        return $ativo;
    }

    /**
     * Retorna se � compativel com a vers�o atual do SEI instalado
     * @param $strVersaoSEI
     * @return bool
     */
    public function verificaCompatibilidade($strVersaoSEI)
    {
        if (substr($strVersaoSEI, 0, 3) != '4.0') {
            return false;
        }
        return true;
    }

    public function inicializar($strVersaoSEI)
    {
        define('DIR_SEI_WEB', realpath(DIR_SEI_CONFIG.'/../web'));
        $this->carregarArquivoConfiguracaoModulo(DIR_SEI_CONFIG);

        if (!$this->verificaCompatibilidade($strVersaoSEI)) {
            die('M�dulo "' . $this->getNome() . '" (' . $this->getVersao() . ') n�o e compat�vel com esta vers�o do SEI (' . $strVersaoSEI . ').');
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
        switch ($strAcao) {
            case 'md_wssei_editor_externo_montar':
            case 'md_wssei_editor_externo_imagem_upload':
                require_once dirname(__FILE__) . '/md_wssei_editor_externo.php';
                return true;
            case 'md_wssei_qrcode':
                require_once dirname(__FILE__) . '/md_wssei_qrcode.php';
                return true;
        }
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

    private static function getNomeArquivoQRCode()
    {
        $nomeArquivoQRCode = 'QRCODE_'
            . self::NOME_MODULO
            . "_"
            . SessaoSEI::getInstance()->getNumIdOrgaoUsuario()
            . "_"
            . 0 //SessaoSEI::getInstance()->getNumIdContextoUsuario()
            . "_"
            . self::VERSAO_MODULO;

        return $nomeArquivoQRCode;
    }

    public static function getQRCodeBase64Img()
    {
        try {

            $nomeArquivo = self::getNomeArquivoQRCode();
            $binQrCode = CacheSEI::getInstance()->getAtributo($nomeArquivo);

            if ($binQrCode) {
                return base64_encode($binQrCode);
            }

            $caminhoAtual = explode("/sei/web", __DIR__);
            $urlSEI = ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL')
                . $caminhoAtual[1]
                . '/controlador_ws.php/api/v2';
            $conteudoQrCode = 'url: ' . $urlSEI
                . ';'
                . 'siglaorgao: ' . SessaoSEI::getInstance()->getStrSiglaOrgaoUsuario()
                . ';'
                . 'orgao: ' . SessaoSEI::getInstance()->getNumIdOrgaoUsuario()
                . ';'
                . 'contexto: ' . 0; //SessaoSEI::getInstance()->getNumIdContextoUsuario();


            $caminhoFisicoQrCode = DIR_SEI_TEMP . '/' . $nomeArquivo;

            InfraQRCode::gerar($conteudoQrCode, $caminhoFisicoQrCode, 'L', 4, 2);

            $infraException = new InfraException();
            if (!file_exists($caminhoFisicoQrCode)) {
                $infraException->lancarValidacao('Arquivo do QRCode n�o encontrado.');
            }
            if (filesize($caminhoFisicoQrCode) == 0) {
                $infraException->lancarValidacao('Arquivo do QRCode vazio.');
            }
            if (($binQrCode = file_get_contents($caminhoFisicoQrCode)) === false) {
                $infraException->lancarValidacao('N�o foi poss�vel ler o arquivo do QRCode.');
            }

            CacheSEI::getInstance()->setAtributo($nomeArquivo, $binQrCode, CacheSEI::getInstance()->getNumTempo());

        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            throw $e;
        }

        return base64_encode($binQrCode);
    }

    /**
     * Gera Identificador �nico do usu�rio logado
     * @return String
     */
    public static function geraIdentificadorUsuario($siglaUsuario, $siglaOrgao)
    {
        $arrDados[] = ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL');
        $arrDados[] = $siglaOrgao;
        $arrDados[] = $siglaUsuario;
        return md5(implode(':', $arrDados));
    }

    private function carregarArquivoConfiguracaoModulo($strDiretorioSeiWeb){
        try{
            $strArquivoConfiguracao = $strDiretorioSeiWeb . '/mod-wssei/ConfiguracaoMdWSSEI.php';
            include_once $strArquivoConfiguracao;       
        } catch(Exception $e){
            LogSEI::getInstance()->gravar("Arquivo de configura��o do m�dulo WSSEI n�o pode ser localizado em " . $strArquivoConfiguracao);
        }
    }   
}

?>