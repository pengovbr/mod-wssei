<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiUsuarioRN extends InfraRN {

    CONST TOKEN_SECRET = '<!RWR1YXJkbyBSb23Do28!>';

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Metodo que retorna o servico SOAP do SIP
     * @return SoapClient
     * @throws InfraException
     */
    private function retornaServicoSip(){
        $strWSDL = ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SipWsdl');
        try{
            if (!InfraUtil::isBolUrlValida($strWSDL)){
                if(!@file_get_contents($strWSDL)) {
                    throw new InfraException('Arquivo WSDL '.$strWSDL.' nao encontrado.');
                }
            }
        }catch(Exception $e){
            throw new InfraException('Falha na conexção com o Sistema de Permissões.',$e);
        }

        try{
            $objSipWS = new SoapClient(
                $strWSDL,
                array(
                    'encoding' => 'ISO-8859-1',
                    'exceptions' => true
                )
            );
            return $objSipWS;
        }catch(Exception $e){
            throw new InfraException('Erro acessando o Sistema de Permissões.');
        }
    }

    /**
     * M?todo que descriptografa o token
     * @param $token
     * @return string
     */
    public function tokenDecode($token){
        $fase1 = base64_decode($token);
        $fase2 = str_replace($this->getSecret(), '', $fase1);
        $fase3 = base64_decode($fase2);
        $tokenData = explode('||', $fase3);
        if(count($tokenData) != 2){
            return null;
        }
        $tokenData[0] = $this->decriptaSenha($tokenData[0]);
        $tokenData[1] = $this->decriptaSenha($tokenData[1]);

        return $tokenData;
    }

    /**
     * M?todo que criptografa o token
     * @param $sigla
     * @param $senha
     * @return string
     */
    public function tokenEncode($sigla, $senha){
        $token = base64_encode($this->getSecret().base64_encode($this->encriptaSenha($sigla).'||'.$this->encriptaSenha($senha)));

        return $token;
    }

    /**
     * Retorna a chave da criptografia
     * @return string
     */
    private function getSecret(){
        $data = new DateTime();
        $strData = $data->format('Ymd');
        $secret = sha1(self::TOKEN_SECRET.$strData);
        return $secret;
    }

    /**
     * Go horse para autenticar usuario... Nao ha como instanciar o SessaoSEI por metodos convencionais.
     * @param stdClass $loginData
     */
    private function setaVariaveisAutenticacao(array $loginData){
        $_GET['id_login'] = $loginData['id_login'];
        $_GET['id_sistema'] = $loginData['id_sistema'];
        $_GET['id_usuario'] = $loginData['id_usuario'];
        $_GET['hash_agente'] = SessaoSEI::gerarHashAgente();
        $_GET['infra_sip'] = true;
        $_GET['id_contexto'] = '';
    }

    /**
     * Metodo que autentica o usuario pelo token
     * @param $token
     * @return bool
     * @throws InfraException
     */
    public function autenticarToken($token){
        try{

            $tokenData = $this->tokenDecode($token);
            if(!$tokenData){
                throw new InfraException('Token inv?lido!');
            }

            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->setStrSigla($tokenData[0]);
            $usuarioDTO->setStrSenha($tokenData[1]);
            $result = $this->autenticar($usuarioDTO);
            if(!$result['sucesso']){
                return $result;
            }
            $this->setaVariaveisAutenticacao($result['data']['loginData']);

            return $result;
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo de autenticacao de usuarios usando SIP
     * @param UsuarioDTO
     *      @param $sigla
     *      @param $senha
     *      @param $IdOrgao
     */
    protected function autenticarConectado(UsuarioDTO $usuarioDTO){
        try{
            if(!$usuarioDTO->isSetNumIdOrgao()){
                $orgaoRN = new OrgaoRN();
                $objOrgaoDTO = new OrgaoDTO();
                $objOrgaoDTO->setBolExclusaoLogica(false);
                $objOrgaoDTO->retNumIdOrgao();
                $objOrgaoDTO->setStrSigla(ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SiglaOrgaoSistema'));
                /**
                 * @var $orgaoCarregdo OrgaoDTO
                 * Orgao da sessao do sistema
                 */
                $orgaoCarregdo = $orgaoRN->consultarRN1352($objOrgaoDTO);
                $usuarioDTO->setNumIdOrgao($orgaoCarregdo->getNumIdOrgao());
            }
            $objSipWs = $this->retornaServicoSip();
            $ret = $objSipWs->autenticarCompleto(
                $usuarioDTO->getNumIdOrgao(),
                null,
                $usuarioDTO->getStrSigla(),
                $this->encriptaSenha($usuarioDTO->getStrSenha()),
                ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SiglaSistema'),
                ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SiglaOrgaoSistema')
            );

            if(!$ret){
                throw new InfraException('Usuário ou senha inválido!');
            }
            $this->setaVariaveisAutenticacao(get_object_vars($ret));
            $ret->id_unidade_atual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
            $token = $this->tokenEncode($usuarioDTO->getStrSigla(), $usuarioDTO->getStrSenha());
            $unidadeRN = new MdWsSeiUnidadeRN();
            $unidadeDTOConsulta = new UnidadeDTO();
            $unidadeDTOConsulta->setNumMaxRegistrosRetorno(99999);//pedido da MBA
            $arrUnidades = $unidadeRN->pesquisarUnidade($unidadeDTOConsulta);

            return MdWsSeiRest::formataRetornoSucessoREST(
                null,
                array(
                    'loginData'=> $ret,
                    'unidades' => $arrUnidades['data'],
                    'token' => $token
                )
            );
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }

    }

    /**
     * Retorna a lista de usuarios por unidade
     * @param UsuarioDTO
     *      @param $idUsuario
     */
    protected function listarUsuariosConectado(UsuarioDTO $usuarioDTO){
        try{
            $objEntradaListarUsuariosAPI = new EntradaListarUsuariosAPI();
            $objEntradaListarUsuariosAPI->setIdUsuario($usuarioDTO->getNumIdUsuario());
            $objSeiRN = new SeiRN();
            $result = $objSeiRN->listarUsuarios($objEntradaListarUsuariosAPI);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    private function decriptaSenha($senha){
        $decoded = base64_decode($senha);
        for($i = 0; $i < strlen($decoded); $i++){
            $decoded[$i] = ~$decoded[$i];
        }

        return $decoded;
    }

    private function encriptaSenha($senha){
        for($i = 0; $i < strlen($senha); $i++){
            $senha[$i] = ~$senha[$i];
        }

        return base64_encode($senha);
    }

    /**
     * Altera a unidade atual do Usuário
     * @param $idUnidade
     */
    public function alterarUnidadeAtual($idUnidade){
        try{
            $_POST['selInfraUnidades'] = $idUnidade;
            SessaoSEI::getInstance()->trocarUnidadeAtual();
            return MdWsSeiRest::formataRetornoSucessoREST('Unidade alterada com sucesso!');
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}