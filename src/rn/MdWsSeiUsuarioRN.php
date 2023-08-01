<?php
require_once DIR_SEI_WEB . '/SEI.php';


class MdWsSeiUsuarioRN extends InfraRN {

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
        throw new InfraException('Falha na conexção com o Sistema de Permissões.', $e);
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
     * Retorna o token para criptografar descriptografar
     * @return string
     */
  private function getTokenSecret(){
      $token = ConfiguracaoMdWSSEI::getInstance()->getValor('WSSEI', 'TokenSecret', false);
      return $token;
  }

    /**
     * Retorna a chave da criptografia
     * @return string
     */
  private function getSecret(){
        
      $token = $this->getTokenSecret();
    if((!$token) || (strlen($token)<32)){
        throw new InfraException('Token Secret inexistente ou tamanho menor que o permitido! Verifique o manual de instalação do módulo.');
    }
        
      $data = new DateTime();
      $strData = $data->format('Ymd');
      $secret = sha1($token.$strData);
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
      $_GET['id_contexto'] = $loginData['IdContexto'];
    ;
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
        $result = $this->apiAutenticar($usuarioDTO, $orgaoDTO);
      if(!$result['sucesso']){
          return $result;
      }
        $this->setaVariaveisAutenticacao($result['data']['loginData']);

        return $result;
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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
  public function apiAutenticar(UsuarioDTO $usuarioDTO, OrgaoDTO $orgaoDTO){
    try{
            
        $contexto = null;
        $orgao = $orgaoDTO->getNumIdOrgao();
        $siglaOrgao = ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SiglaOrgaoSistema');
        $strChaveAcesso = ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'ChaveAcesso');
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
            $strChaveAcesso,
            $orgao,
            $contexto,
            $usuarioDTO->getStrSigla(),
            $this->encriptaSenha($usuarioDTO->getStrSenha()),
            ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SiglaSistema'),
              $siglaOrgao
          ); 

      if(!$ret){
        sleep(3);
        throw new InfraException('Usuário ou senha inválido!');
      }
            
          $this->setaVariaveisAutenticacao(get_object_vars($ret));
          session_start();
          $objInfraDadoUsuario = new InfraDadoUsuario(SessaoSEI::getInstance());
            
          //Obtem os dados do carto da assinatura
          $numIdCargoAssinatura = null;
          $strNomeCargoAssinatura = $objInfraDadoUsuario->getValor('ASSINATURA_CARGO_FUNCAO_'.SessaoSEI::getInstance()->getNumIdUnidadeAtual());

          $objAssinanteDTO = new AssinanteDTO();
          $objAssinanteDTO->setStrCargoFuncao($strNomeCargoAssinatura);
          $objAssinanteDTO->retNumIdAssinante();

          $objAssinanteRN = new AssinanteRN();

      if($objAssinanteRN->contarRN1340($objAssinanteDTO) == 1){
        $objAssinanteDTO = $objAssinanteRN->consultarRN1338($objAssinanteDTO);
        $numIdCargoAssinatura = $objAssinanteDTO->getNumIdAssinante();
      }
            
          //dados usuário
          $ret->IdUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
          $ret->sigla = $usuarioDTO->getStrSigla();
          $ret->nome = SessaoSEI::getInstance()->getStrNomeUsuario();
          $ret->idUltimoCargoAssinatura = $numIdCargoAssinatura;
          $ret->ultimoCargoAssinatura = $strNomeCargoAssinatura;
            
          $token = $this->tokenEncode($usuarioDTO->getStrSigla(), $usuarioDTO->getStrSenha(), $orgao, $contexto);

          $arrUnidades = array();
      foreach(SessaoSEI::getInstance()->getArrUnidades() as $unidade){
          $arrUnidades[] = array(
              'id' => $unidade[0],
              'sigla' => $unidade[1],
              'descricao' => $unidade[2]
          );
      }

          $retPerfis = $this->listarPerfisUsuario($strChaveAcesso, $ret->IdSistema, $ret->IdUsuario);
          $objSessao = SessaoSEI::getInstance();
            
          return MdWsSeiRest::formataRetornoSucessoREST(
              null,
              array(
                  'loginData'=> $ret,
                  'perfis' => $retPerfis,
                  'unidades' => $arrUnidades,
                  'identificador' => MdWsSeiRest::geraIdentificadorUsuario($objSessao->getStrSiglaUsuario(), $objSessao->getStrSiglaOrgaoUsuario()),
                  'token' => $token
              )
          );
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }

  }

    /**
     * Método que retorna os perfis do usuário
     * @param $strChaveAcesso
     * @param $idSistema
     * @param $idUsuario
     * @return array
     */
  private function listarPerfisUsuario($strChaveAcesso, $idSistema, $idUsuario){
    try{
        $arrPerfis = array();
        $objSipWs = $this->retornaServicoSip();
        $ret = $objSipWs->carregarPerfis(
            $strChaveAcesso,
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

        return $arrPerfis;

    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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
        InfraArray::ordenarArray($ret, InfraSip::$WS_UNIDADE_SIGLA, InfraArray::$TIPO_ORDENACAO_ASC);
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
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
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
        $arrUsuarioDTO = UsuarioINT::autoCompletarUsuarios($orgao, $palavrachave, false, false, true, false);
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
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

}
