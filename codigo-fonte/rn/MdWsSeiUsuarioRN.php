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
        if(count($tokenData) != 4){
            return null;
        }
        $tokenData[0] = $this->decriptaSenha($tokenData[0]);
        $tokenData[1] = $this->decriptaSenha($tokenData[1]);

        return $tokenData;
    }

    /**
     * Método que criptografa o token
     * @param $sigla
     * @param $senha
     * @param null $orgao
     * @param null $contexto
     * @return string
     */
    public function tokenEncode($sigla, $senha, $orgao = null, $contexto = null){
        $token = base64_encode(
            $this->getSecret()
            .base64_encode(
                $this->encriptaSenha($sigla)
                .'||'.$this->encriptaSenha($senha)
                .'||'.$orgao
                .'||'.$contexto
            )
        );

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
        $_GET['id_login'] = $loginData['IdLogin'];
        $_GET['id_sistema'] = $loginData['IdSistema'];
        $_GET['id_usuario'] = $loginData['IdUsuario'];
        $_GET['hash_agente'] = SessaoSEI::gerarHashAgente();
        $_GET['infra_sip'] = true;
        $_GET['id_contexto'] = $loginData['IdContexto'];;
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
                throw new InfraException('Token inválido!');
            }
            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->setStrSigla($tokenData[0]);
            $usuarioDTO->setStrSenha($tokenData[1]);
            $orgaoDTO = new OrgaoDTO();
            $orgaoDTO->setNumIdOrgao($tokenData[2]);
            $contextoDTO = new ContextoDTO();
            $contextoDTO->setNumIdContexto($tokenData[3]);
            $result = $this->apiAutenticar($usuarioDTO, $contextoDTO, $orgaoDTO);
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
     * @param UsuarioDTO $usuarioDTO
     * @param ContextoDTO $contextoDTO
     * @param OrgaoDTO $orgaoDTO
     * @return array
     */
    public function apiAutenticar(UsuarioDTO $usuarioDTO, ContextoDTO $contextoDTO, OrgaoDTO $orgaoDTO){
        try{
            $contexto = $contextoDTO->getNumIdContexto();
            $orgao = $orgaoDTO->getNumIdOrgao();
            $siglaOrgao = ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SiglaOrgaoSistema');

            $orgaoRN = new OrgaoRN();

            if(is_null($orgao)){
                $objOrgaoDTO = new OrgaoDTO();
                $objOrgaoDTO->setBolExclusaoLogica(false);
                $objOrgaoDTO->retNumIdOrgao();
                $objOrgaoDTO->retStrSigla();
                $objOrgaoDTO->setStrSigla($siglaOrgao);
                /**
                 * @var $orgaoCarregdo OrgaoDTO
                 * Orgao da sessao do sistema
                 */
                $orgaoCarregdo = $orgaoRN->consultarRN1352($objOrgaoDTO);
                $orgao = $orgaoCarregdo->getNumIdOrgao();
            }

            $objSipWs = $this->retornaServicoSip();
            $ret = $objSipWs->autenticarCompleto(
                $orgao,
                $contexto,
                $usuarioDTO->getStrSigla(),
                $this->encriptaSenha($usuarioDTO->getStrSenha()),
                ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SiglaSistema'),
                $siglaOrgao
            ); 

            if(!$ret){
                throw new InfraException('Usuário ou senha inválido!');
            }
            $this->setaVariaveisAutenticacao(get_object_vars($ret));
            
            //dados usuário
            $ret->IdUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
            $ret->sigla = $usuarioDTO->getStrSigla();
            $ret->nome = SessaoSEI::getInstance()->getStrNomeUsuario();
            
            $token = $this->tokenEncode($usuarioDTO->getStrSigla(), $usuarioDTO->getStrSenha(), $orgao, $contexto);

            $arrUnidades = array();
            foreach(SessaoSEI::getInstance()->getArrUnidades() as $unidade){
                $arrUnidades[] = array(
                    'id' => $unidade[0],
                    'sigla' => $unidade[1],
                    'descricao' => $unidade[2]
                );
            }

            $arrPerfis = array();
            $retPerfis = $this->listarPerfisUsuario($ret->IdSistema, $ret->IdUsuario);
            if($retPerfis && $retPerfis['data']){
                $arrPerfis = $retPerfis['data'];
            }

            return MdWsSeiRest::formataRetornoSucessoREST(
                null,
                array(
                    'loginData'=> $ret,
                    'perfis' => $arrPerfis,
                    'unidades' => $arrUnidades,
                    'token' => $token
                )
            );
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }

    }

    /**
     * Método que retorna os perfis do usuário
     * @param $idSistema
     * @param $idUsuario
     * @return array
     */
    private function listarPerfisUsuario($idSistema, $idUsuario){
        try{
            $arrPerfis = array();
            $objSipWs = $this->retornaServicoSip();
            $ret = $objSipWs->carregarPerfis(
                $idSistema,
                $idUsuario
            );
            $arrPerfis = array();
            foreach ($ret as $perfil) {
                $arrPerfis[] = array(
                    'idPerfil' => $perfil[0],
                    'nome' => $perfil[1],
                    'stAtivo' => $perfil[3]
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrPerfis);

        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Retorna a lista de usuarios por unidade
     * @param UnidadeDTO $unidadeDTOParam
     * @return array
     */
    protected function listarUsuariosConectado(UnidadeDTO $unidadeDTOParam){
        try{
            $idUnidade = null;
            $limit = 10;
            $start = 0;
            if($unidadeDTOParam->isSetNumMaxRegistrosRetorno()){
               $limit = $unidadeDTOParam->getNumMaxRegistrosRetorno();
            }
            if(!is_null($unidadeDTOParam->getNumPaginaAtual())){
                $start = $unidadeDTOParam->getNumPaginaAtual();
            }
            if($unidadeDTOParam->isSetNumIdUnidade()){
                $idUnidade = $unidadeDTOParam->getNumIdUnidade();
            }
            $result = array();
            $unidadeDTO = new UnidadeDTO();
            $unidadeDTO->setNumIdUnidade($idUnidade);
            $usuarioRN = new UsuarioRN();
            $arrUsuarioDTO = $usuarioRN->listarPorUnidadeRN0812($unidadeDTO);

            //Paginação lógica pois o SIP não retorna os usuários paginados...
            $total = count($arrUsuarioDTO);
            $paginado = array_slice($arrUsuarioDTO, ($limit*$start), $limit);
            /** @var UsuarioDTO $usuarioDTO */
            foreach ($paginado as $usuarioDTO){
                $result[] = array(
                    'id_usuario' => $usuarioDTO->getNumIdUsuario(),
                    'sigla' => $usuarioDTO->getStrSigla(),
                    'nome' => $usuarioDTO->getStrNome(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $total);
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

    /**
     * Retorna as unidades do usuário
     * @param UsuarioDTO $usuarioDTO
     */
    public function listarUnidadesUsuarioConectado(UsuarioDTO $usuarioDTO){
        try{
            $objInfraSip = new InfraSip(SessaoSEI::getInstance());
            $ret = array_values($objInfraSip->carregarUnidades(SessaoSEI::getInstance()->getNumIdSistema(), $usuarioDTO->getNumIdUsuario()));
            InfraArray::ordenarArray($ret,InfraSip::$WS_UNIDADE_SIGLA,InfraArray::$TIPO_ORDENACAO_ASC);
            $result = array();
            foreach($ret as $uni){
                //somente unidades ativas, todas as unidades de outros usuários, se for o usuário atual não mostra a unidade atual
                if ($uni[InfraSip::$WS_UNIDADE_SIN_ATIVO]=='S' && ($usuarioDTO->getNumIdUsuario() != SessaoSEI::getInstance()->getNumIdUsuario() ||$uni[InfraSip::$WS_UNIDADE_ID] != SessaoSEI::getInstance()->getNumIdUnidadeAtual())){
                    $result[] = array(
                        'id' => $uni[InfraSip::$WS_UNIDADE_ID],
                        'sigla' => $uni[InfraSip::$WS_UNIDADE_SIGLA],
                        'nome' => $uni[InfraSip::$WS_UNIDADE_DESCRICAO]
                    );
                }
            }
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Pesquisa o usuário pelo nome
     * @param $palavrachave
     * @param null $orgao
     * @return array
     */
    public function apiPesquisarUsuario($palavrachave, $orgao = null){
        try{
            $result = array();
            $arrUsuarioDTO = UsuarioINT::autoCompletarUsuarios($orgao,$palavrachave,false,false,true,false);
            /** @var UsuarioDTO $usuarioDTO */
            foreach($arrUsuarioDTO as $usuarioDTO){
                $result[] = array(
                    'id_contato' => $usuarioDTO->getNumIdContato(),
                    'id_usuario' => $usuarioDTO->getNumIdUsuario(),
                    'sigla' => $usuarioDTO->getStrSigla(),
                    'nome' => $usuarioDTO->getStrNome()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}