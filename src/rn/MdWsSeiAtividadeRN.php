<?
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiAtividadeRN extends AtividadeRN {

  protected function inicializarObjInfraIBanco(){
      return BancoSEI::getInstance();
  }

    /**
     * Retorna as atividades de um processo
     * @param AtividadeDTO $atividadeDTOParam
     * @return array
     * @throws InfraException
     */
  protected function listarAtividadesProcessoConectado(AtividadeDTO $atividadeDTOParam){
    try{
        $result = array();
        $procedimentoHistoricoDTO = new ProcedimentoHistoricoDTO();
        $procedimentoHistoricoDTO->setStrStaHistorico(ProcedimentoRN::$TH_RESUMIDO);

      if(!$atividadeDTOParam->isSetDblIdProtocolo()){
        throw new InfraException('O procedimento deve ser informado!');
      }
        $procedimentoHistoricoDTO->setDblIdProcedimento($atividadeDTOParam->getDblIdProtocolo());
      if(empty($atividadeDTOParam->getNumPaginaAtual())){
          $procedimentoHistoricoDTO->setNumPaginaAtual(0);
      }else{
          $procedimentoHistoricoDTO->setNumPaginaAtual($atividadeDTOParam->getNumPaginaAtual());
      }
      if($atividadeDTOParam->getNumMaxRegistrosRetorno()){
          $procedimentoHistoricoDTO->setNumMaxRegistrosRetorno($atividadeDTOParam->getNumMaxRegistrosRetorno());
      }else{
          $procedimentoHistoricoDTO->setNumMaxRegistrosRetorno(10);
      }
        $procedimentoRN = new ProcedimentoRN();
        $ret = $procedimentoRN->consultarHistoricoRN1025($procedimentoHistoricoDTO);

        /** @var AtividadeDTO $atividadeDTO */
      foreach($ret->getArrObjAtividadeDTO() as $atividadeDTO) {
          $dateTime = explode(' ', $atividadeDTO->getDthAbertura());
          $informacao = null;
          $result[] = [
              "id" => $atividadeDTO->getNumIdAtividade(),
              "atributos" => [
                  "idProcesso" => $atividadeDTOParam->getDblIdProtocolo(),
                  "usuario" => ($atividadeDTO->getNumIdUsuarioOrigem()) ? $atividadeDTO->getStrSiglaUsuarioOrigem() : null,
                  "data" => $dateTime[0],
                  "hora" => $dateTime[1],
                  "unidade" => $atividadeDTO->getStrSiglaUnidade(),
                  "informacao" => strip_tags($atividadeDTO->getStrNomeTarefa())
              ]
          ];
      }

        return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $procedimentoHistoricoDTO->getNumTotalRegistros());
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * Método que encapsula os dados para o cadastramento do andamento do processo
     * @param array $post
     * @return AtualizarAndamentoDTO
     */
  public function encapsulaLancarAndamentoProcesso(array $data){
      $entradaLancarAndamentoAPI = new EntradaLancarAndamentoAPI();
      $entradaLancarAndamentoAPI->setIdTarefa(TarefaRN::$TI_ATUALIZACAO_ANDAMENTO);
    if($data['protocolo']){
        $entradaLancarAndamentoAPI->setIdProcedimento($data['protocolo']);
    }

    if($data['descricao']){
        $atributoAndamentoAPI = new AtributoAndamentoAPI();
        $atributoAndamentoAPI->setNome('DESCRICAO');
        $atributoAndamentoAPI->setValor($data['descricao']);
        $atributoAndamentoAPI->setIdOrigem(null);
        $entradaLancarAndamentoAPI->setAtributos(array($atributoAndamentoAPI));
    }

      return $entradaLancarAndamentoAPI;
  }

    /**
     * Método que cadastra o andamento manual de um processo
     * @param EntradaLancarAndamentoAPI $entradaLancarAndamentoAPIParam
     * @info usar o método auxiliar encapsulaLancarAndamentoProcesso para faciliar
     * @return array
     */
  protected function lancarAndamentoProcessoControlado(EntradaLancarAndamentoAPI $entradaLancarAndamentoAPIParam){
    try{
        $seiRN = new SeiRN();
        $seiRN->lancarAndamento($entradaLancarAndamentoAPIParam);

        return MdWsSeiRest::formataRetornoSucessoREST('Observação cadastrada com sucesso!');
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }

    /**
     * Método clonado de AtividadeRN::listarPendenciasRN0754Conectado com alterações para pesquisa de processo
     * @param MdWsSeiPesquisarPendenciaDTO $objPesquisaPendenciaDTO
     * @return array
     * @throws InfraException
     */
  protected function listarPendenciasConectado(MdWsSeiPesquisarPendenciaDTO $objPesquisaPendenciaDTO) {
    try {
      if (!$objPesquisaPendenciaDTO->isSetStrStaTipoAtribuicao()) {
        $objPesquisaPendenciaDTO->setStrStaTipoAtribuicao(self::$TA_TODAS);
      }

      if (!$objPesquisaPendenciaDTO->isSetNumIdUsuarioAtribuicao()) {
          $objPesquisaPendenciaDTO->setNumIdUsuarioAtribuicao(null);
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinMontandoArvore()) {
          $objPesquisaPendenciaDTO->setStrSinMontandoArvore('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinAnotacoes()) {
          $objPesquisaPendenciaDTO->setStrSinAnotacoes('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinSituacoes()) {
          $objPesquisaPendenciaDTO->setStrSinSituacoes('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinMarcadores()) {
          $objPesquisaPendenciaDTO->setStrSinMarcadores('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinInteressados()) {
          $objPesquisaPendenciaDTO->setStrSinInteressados('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinRetornoProgramado()) {
          $objPesquisaPendenciaDTO->setStrSinRetornoProgramado('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinCredenciais()) {
          $objPesquisaPendenciaDTO->setStrSinCredenciais('N');
      }

      if (!$objPesquisaPendenciaDTO->isSetStrSinHoje()) {
          $objPesquisaPendenciaDTO->setStrSinHoje('N');
      }

        $objAtividadeDTO = new MdWsSeiAtividadeDTO();
        $objAtividadeDTO->retNumIdAtividade();
        $objAtividadeDTO->retNumIdTarefa();
        $objAtividadeDTO->retNumIdUsuarioAtribuicao();
        $objAtividadeDTO->retNumIdUsuarioVisualizacao();
        $objAtividadeDTO->retNumTipoVisualizacao();
        $objAtividadeDTO->retNumIdUnidade();
        $objAtividadeDTO->retDthConclusao();
        $objAtividadeDTO->retDblIdProtocolo();
        $objAtividadeDTO->retStrSiglaUnidade();
        $objAtividadeDTO->retStrSinInicial();
        $objAtividadeDTO->retNumIdUsuarioAtribuicao();
        $objAtividadeDTO->retStrSiglaUsuarioAtribuicao();
        $objAtividadeDTO->retStrNomeUsuarioAtribuicao();

        $objAtividadeDTO->setNumIdUnidade($objPesquisaPendenciaDTO->getNumIdUnidade());

      if ($objPesquisaPendenciaDTO->isSetStrProtocoloFormatadoPesquisaProtocolo()) {
          $strProtocoloFormatado = InfraUtil::retirarFormatacao(
              $objPesquisaPendenciaDTO->getStrProtocoloFormatadoPesquisaProtocolo(), false
          );
          $objAtividadeDTO->setStrProtocoloFormatadoPesquisaProtocolo(
              '%'.$strProtocoloFormatado.'%',
              InfraDTO::$OPER_LIKE
          );
      }
      if ($objPesquisaPendenciaDTO->isSetNumIdGrupoAcompanhamentoProcedimento()) {
          $objAtividadeDTO->setNumIdGrupoAcompanhamentoProcedimento($objPesquisaPendenciaDTO->getNumIdGrupoAcompanhamentoProcedimento());
      }

      if ($objPesquisaPendenciaDTO->getStrSinHoje() == 'N') {
          $objAtividadeDTO->setDthConclusao(null);
      } else {
          $objAtividadeDTO->adicionarCriterio(array('Conclusao', 'Conclusao'),
              array(InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_MAIOR_IGUAL),
              array(null, InfraData::getStrDataAtual() . ' 00:00:00'),
              array(InfraDTO::$OPER_LOGICO_OR));
      }

        $objAtividadeDTO->setStrStaProtocoloProtocolo(ProtocoloRN::$TP_PROCEDIMENTO);

      if ($objPesquisaPendenciaDTO->getNumIdUsuario() == null) {
          $objAtividadeDTO->setStrStaNivelAcessoGlobalProtocolo(ProtocoloRN::$NA_SIGILOSO, InfraDTO::$OPER_DIFERENTE);
      } else {
          $objAtividadeDTO->adicionarCriterio(array('StaNivelAcessoGlobalProtocolo', 'IdUsuario'),
              array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IGUAL),
              array(ProtocoloRN::$NA_SIGILOSO, $objPesquisaPendenciaDTO->getNumIdUsuario()),
              array(InfraDTO::$OPER_LOGICO_OR));
      }

      if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao() == self::$TA_MINHAS) {
          $objAtividadeDTO->setNumIdUsuarioAtribuicao($objPesquisaPendenciaDTO->getNumIdUsuario());
      } else if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao() == self::$TA_DEFINIDAS) {
          $objAtividadeDTO->setNumIdUsuarioAtribuicao(null, InfraDTO::$OPER_DIFERENTE);
      } else if ($objPesquisaPendenciaDTO->getStrStaTipoAtribuicao() == self::$TA_ESPECIFICAS) {
          $objAtividadeDTO->setNumIdUsuarioAtribuicao($objPesquisaPendenciaDTO->getNumIdUsuarioAtribuicao());
      }

      if ($objPesquisaPendenciaDTO->isSetDblIdProtocolo()) {
        if (!is_array($objPesquisaPendenciaDTO->getDblIdProtocolo())) {
            $objAtividadeDTO->setDblIdProtocolo($objPesquisaPendenciaDTO->getDblIdProtocolo());
        } else {
            $objAtividadeDTO->setDblIdProtocolo($objPesquisaPendenciaDTO->getDblIdProtocolo(), InfraDTO::$OPER_IN);
        }
      }

      if ($objPesquisaPendenciaDTO->isSetStrStaEstadoProcedimento()) {
        if (is_array($objPesquisaPendenciaDTO->getStrStaEstadoProcedimento())) {
            $objAtividadeDTO->setStrStaEstadoProtocolo($objPesquisaPendenciaDTO->getStrStaEstadoProcedimento(), InfraDTO::$OPER_IN);
        } else {
            $objAtividadeDTO->setStrStaEstadoProtocolo($objPesquisaPendenciaDTO->getStrStaEstadoProcedimento());
        }
      }

      if ($objPesquisaPendenciaDTO->isSetStrSinInicial()) {
          $objAtividadeDTO->setStrSinInicial($objPesquisaPendenciaDTO->getStrSinInicial());
      }

      if ($objPesquisaPendenciaDTO->isSetNumIdMarcador()) {
          $objAtividadeDTO->setNumTipoFkAndamentoMarcador(InfraDTO::$TIPO_FK_OBRIGATORIA);
          $objAtividadeDTO->setNumIdMarcador($objPesquisaPendenciaDTO->getNumIdMarcador());
          $objAtividadeDTO->setStrSinUltimoAndamentoMarcador('S');
      }

        //ordenar pela data de abertura descendente
        //$objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_DESC);
        $objAtividadeDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);


        //paginação
        $objAtividadeDTO->setNumMaxRegistrosRetorno($objPesquisaPendenciaDTO->getNumMaxRegistrosRetorno());
        $objAtividadeDTO->setNumPaginaAtual($objPesquisaPendenciaDTO->getNumPaginaAtual());

        $arrAtividadeDTO = $this->listarRN0036($objAtividadeDTO);

        //paginação
        $objPesquisaPendenciaDTO->setNumTotalRegistros($objAtividadeDTO->getNumTotalRegistros());
        $objPesquisaPendenciaDTO->setNumRegistrosPaginaAtual($objAtividadeDTO->getNumRegistrosPaginaAtual());

        $arrProcedimentos = array();

        //Se encontrou pelo menos um registro
      if (count($arrAtividadeDTO) > 0) {

          $objProcedimentoDTO = new ProcedimentoDTO();

          $objProcedimentoDTO->retStrDescricaoProtocolo();

          $arrProtocolosAtividades = array_unique(InfraArray::converterArrInfraDTO($arrAtividadeDTO, 'IdProtocolo'));
          $objProcedimentoDTO->setDblIdProcedimento($arrProtocolosAtividades, InfraDTO::$OPER_IN);

        if ($objPesquisaPendenciaDTO->getStrSinMontandoArvore() == 'S') {
            $objProcedimentoDTO->setStrSinMontandoArvore('S');
        }

        if ($objPesquisaPendenciaDTO->getStrSinAnotacoes() == 'S') {
            $objProcedimentoDTO->setStrSinAnotacoes('S');
        }

        if ($objPesquisaPendenciaDTO->getStrSinSituacoes() == 'S') {
            $objProcedimentoDTO->setStrSinSituacoes('S');
        }

        if ($objPesquisaPendenciaDTO->getStrSinMarcadores() == 'S') {
            $objProcedimentoDTO->setStrSinMarcadores('S');
        }

        if ($objPesquisaPendenciaDTO->isSetDblIdDocumento()) {
            $objProcedimentoDTO->setArrDblIdProtocoloAssociado(array($objPesquisaPendenciaDTO->getDblIdDocumento()));
        }

          $objProcedimentoRN = new ProcedimentoRN();

          $arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);

          $arrObjParticipanteDTO = null;
        if ($objPesquisaPendenciaDTO->getStrSinInteressados() == 'S') {

            $arrObjParticipanteDTO = array();

            $objParticipanteDTO = new ParticipanteDTO();
            $objParticipanteDTO->retDblIdProtocolo();
            $objParticipanteDTO->retStrSiglaContato();
            $objParticipanteDTO->retStrNomeContato();
            $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
            $objParticipanteDTO->setDblIdProtocolo($arrProtocolosAtividades, InfraDTO::$OPER_IN);

            $objParticipanteRN = new ParticipanteRN();
            $arrTemp = $objParticipanteRN->listarRN0189($objParticipanteDTO);

          foreach ($arrTemp as $objParticipanteDTO) {
            if (!isset($arrObjParticipanteDTO[$objParticipanteDTO->getDblIdProtocolo()])) {
              $arrObjParticipanteDTO[$objParticipanteDTO->getDblIdProtocolo()] = array($objParticipanteDTO);
            } else {
                $arrObjParticipanteDTO[$objParticipanteDTO->getDblIdProtocolo()][] = $objParticipanteDTO;
            }
          }
        }

          $arrObjRetornoProgramadoDTO = null;
        if ($objPesquisaPendenciaDTO->getStrSinRetornoProgramado() == 'S') {

            $objRetornoProgramadoDTO = new RetornoProgramadoDTO();
            $objRetornoProgramadoDTO->retDblIdProtocolo();
            $objRetornoProgramadoDTO->retNumIdUnidadeEnvio();
            $objRetornoProgramadoDTO->retStrSiglaUnidadeEnvio();
            $objRetornoProgramadoDTO->retNumIdUnidadeRetorno();
            $objRetornoProgramadoDTO->retStrSiglaUnidadeRetorno();
            $objRetornoProgramadoDTO->retDtaProgramada();
            $objRetornoProgramadoDTO->retDthAberturaAtividadeRetorno();
            $objRetornoProgramadoDTO->retNumIdAtividadeRetorno();
            $objRetornoProgramadoDTO->retNumIdAtividadeEnvio();
                                        
            $objRetornoProgramadoDTO->retDtaProgramada();
            $objRetornoProgramadoDTO->setNumIdUnidadeEnvio($objPesquisaPendenciaDTO->getNumIdUnidade());
            $objRetornoProgramadoDTO->setDblIdProtocolo($arrProtocolosAtividades, InfraDTO::$OPER_IN);
            $objRetornoProgramadoDTO->setNumIdAtividadeRetorno(null);
            $objRetornoProgramadoDTO->setOrdDtaProgramada(InfraDTO::$TIPO_ORDENACAO_ASC);

            $objRetornoProgramadoRN = new RetornoProgramadoRN();
            $arrObjRetornoProgramadoDTO = InfraArray::indexarArrInfraDTO($objRetornoProgramadoRN->listar($objRetornoProgramadoDTO), 'IdProtocolo', true);
        }


          //Manter ordem obtida na listagem das atividades
          $arrAdicionados = array();
          $arrIdProcedimentoSigiloso = array();

          $arr = InfraArray::indexarArrInfraDTO($arr, 'IdProcedimento');

        foreach ($arrAtividadeDTO as $objAtividadeDTO) {

            $objProcedimentoDTO = $arr[$objAtividadeDTO->getDblIdProtocolo()];

            //pode não existir se o procedimento foi excluído
          if ($objProcedimentoDTO != null) {

              $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();

            if ($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_SIGILOSO) {

              $objProcedimentoDTO->setStrSinCredencialProcesso('N');
              $objProcedimentoDTO->setStrSinCredencialAssinatura('N');

              $arrIdProcedimentoSigiloso[] = $dblIdProcedimento;
            }

            if (!isset($arrAdicionados[$dblIdProcedimento])) {

                $objProcedimentoDTO->setArrObjAtividadeDTO(array($objAtividadeDTO));

              if (is_array($arrObjParticipanteDTO)) {
                if (isset($arrObjParticipanteDTO[$dblIdProcedimento])) {
                        $objProcedimentoDTO->setArrObjParticipanteDTO($arrObjParticipanteDTO[$dblIdProcedimento]);
                } else {
                    $objProcedimentoDTO->setArrObjParticipanteDTO(null);
                }
              }

              if (is_array($arrObjRetornoProgramadoDTO)) {
                if (isset($arrObjRetornoProgramadoDTO[$dblIdProcedimento])) {
                  $objProcedimentoDTO->setArrObjRetornoProgramadoDTO($arrObjRetornoProgramadoDTO[$dblIdProcedimento]);
                } else {
                          $objProcedimentoDTO->setArrObjRetornoProgramadoDTO(null);
                }
              }

                    $arrProcedimentos[] = $objProcedimentoDTO;
                    $arrAdicionados[$dblIdProcedimento] = 0;
            } else {
                $arrAtividadeDTOProcedimento = $objProcedimentoDTO->getArrObjAtividadeDTO();
                $arrAtividadeDTOProcedimento[] = $objAtividadeDTO;
                $objProcedimentoDTO->setArrObjAtividadeDTO($arrAtividadeDTOProcedimento);
            }
          }
        }


        if ($objPesquisaPendenciaDTO->getStrSinCredenciais() == 'S' && count($arrIdProcedimentoSigiloso)) {

            $objAcessoDTO = new AcessoDTO();
            $objAcessoDTO->retDblIdProtocolo();
            $objAcessoDTO->retStrStaTipo();
            $objAcessoDTO->setNumIdUsuario($objPesquisaPendenciaDTO->getNumIdUsuario());
            $objAcessoDTO->setNumIdUnidade($objPesquisaPendenciaDTO->getNumIdUnidade());
            $objAcessoDTO->setStrStaTipo(array(AcessoRN::$TA_CREDENCIAL_PROCESSO, AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO), InfraDTO::$OPER_IN);
            $objAcessoDTO->setDblIdProtocolo($arrIdProcedimentoSigiloso, InfraDTO::$OPER_IN);

            $objAcessoRN = new AcessoRN();
            $arrObjAcessoDTO = $objAcessoRN->listar($objAcessoDTO);

            /*
              foreach($arr as $objProcedimentoDTO){
                $objProcedimentoDTO->setStrSinCredencialProcesso('N');
                $objProcedimentoDTO->setStrSinCredencialAssinatura('N');
              }
            */

          foreach ($arrObjAcessoDTO as $objAcessoDTO) {
            if ($objAcessoDTO->getStrStaTipo() == AcessoRN::$TA_CREDENCIAL_PROCESSO) {
              $arr[$objAcessoDTO->getDblIdProtocolo()]->setStrSinCredencialProcesso('S');
            } else if ($objAcessoDTO->getStrStaTipo() == AcessoRN::$TA_CREDENCIAL_ASSINATURA_PROCESSO) {
                $arr[$objAcessoDTO->getDblIdProtocolo()]->setStrSinCredencialAssinatura('S');
            }
          }

        }
      }

        return $arrProcedimentos;

    } catch (Exception $e) {
        throw new InfraException('Erro recuperando processos abertos.', $e);
    }
  }

    /**
     * Sobrescrevendo método para colocar paginação
     * @param ProcedimentoDTO $objProcedimentoDTO
     * @return mixed
     * @throws InfraException
     */
  protected function listarCredenciaisConectado(ProcedimentoDTO $objProcedimentoDTO) {
    try{
        $objInfraException = new InfraException();

        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->retDblIdProtocolo();
        $objProtocoloDTO->retStrProtocoloFormatado();
        $objProtocoloDTO->retStrStaNivelAcessoGlobal();
        $objProtocoloDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());

        $objProtocoloRN = new ProtocoloRN();
        $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

        $objAcessoDTO = new AcessoDTO();
        $objAcessoDTO->retNumIdAcesso();
        $objAcessoDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
        $objAcessoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAcessoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);
        $objAcessoDTO->setNumMaxRegistrosRetorno(1);

        $objAcessoRN = new AcessoRN();

      if ($objAcessoRN->consultar($objAcessoDTO) == null){
        $objInfraException->adicionarValidacao('Usuário atual não possui credencial de acesso ao processo '.$objProtocoloDTO->getStrProtocoloFormatado().' nesta unidade.');
      }

      if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()!=ProtocoloRN::$NA_SIGILOSO){
          $objInfraException->adicionarValidacao('Não é possível listar credenciais de acesso para um processo não sigiloso ('.$objProtocoloDTO->getStrProtocoloFormatado().').');
      }

        $objInfraException->lancarValidacoes();

        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->retNumIdAtividade();
        $objAtividadeDTO->retStrSiglaUsuario();
        $objAtividadeDTO->retStrNomeUsuario();
        $objAtividadeDTO->retStrSiglaUnidade();
        $objAtividadeDTO->retStrDescricaoUnidade();
        $objAtividadeDTO->retDthAbertura();
        $objAtividadeDTO->retNumIdTarefa();
        $objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtividadeDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());
        $objAtividadeDTO->setNumIdTarefa(array_merge(TarefaRN::getArrTarefasConcessaoCredencial(false), TarefaRN::getArrTarefasCassacaoCredencial(false)), InfraDTO::$OPER_IN);

        // INICIO SOBRESCRITA
      if($objProcedimentoDTO->isSetNumMaxRegistrosRetorno()){
          $objAtividadeDTO->setNumMaxRegistrosRetorno($objProcedimentoDTO->getNumMaxRegistrosRetorno());
      }
      if(!is_null($objProcedimentoDTO->getNumPaginaAtual())){
          $objAtividadeDTO->setNumPaginaAtual($objProcedimentoDTO->getNumPaginaAtual());
      }

        $objAtividadeDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);
        $arrObjAtividadeDTO = $this->listarRN0036($objAtividadeDTO);
        $objProcedimentoDTO->setNumTotalRegistros($objAtividadeDTO->getNumTotalRegistros());
        // FIM SOBRESCRITA

      if (count($arrObjAtividadeDTO)){

          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->retNumIdAtividade();
          $objAtributoAndamentoDTO->retStrNome();
          $objAtributoAndamentoDTO->retStrValor();
          $objAtributoAndamentoDTO->setNumIdAtividade(InfraArray::converterArrInfraDTO($arrObjAtividadeDTO, 'IdAtividade'), InfraDTO::$OPER_IN);

          $objAtributoAndamentoRN = new AtributoAndamentoRN();
          $arrObjAtributoAndamentoDTO = InfraArray::indexarArrInfraDTO($objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO), 'IdAtividade', true);

        foreach($arrObjAtividadeDTO as $objAtividadeDTO){
          if (isset($arrObjAtributoAndamentoDTO[$objAtividadeDTO->getNumIdAtividade()])){
            $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO[$objAtividadeDTO->getNumIdAtividade()]);
          }else{
              $objAtividadeDTO->setArrObjAtributoAndamentoDTO(array());
          }
        }
      }

          return $arrObjAtividadeDTO;

    }catch(Exception $e){
        throw new InfraException('Erro listando credenciais.', $e);
    }
  }


}
