<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiProcedimentoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Metodo de sobrestamento de processo
     * @param EntradaSobrestarProcessoAPI $entradaSobrestarProcessoAPI
     * @return array
     */
    protected function sobrestamentoProcessoControlado(EntradaSobrestarProcessoAPI $entradaSobrestarProcessoAPI){
        try{
            $seiRN = new SeiRN();
            $seiRN->sobrestarProcesso($entradaSobrestarProcessoAPI);
            return array(
                'sucesso' => true,
                'mensage' => 'Processo sobrestado com sucesso'
            );
        }catch (Exception $e){
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
            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }
    }

    /**
     * @param $protocolo
     * @return array
     */
    protected function removerSobrestamentoProcessoControlado(ProcedimentoDTO $procedimentoDTOParam){
        try{
            if(!$procedimentoDTOParam->getDblIdProcedimento()){
                throw new InfraException('Procedimento n?o informado.');
            }
            $seiRN = new SeiRN();
            $entradaRemoverSobrestamentoProcessoAPI = new EntradaRemoverSobrestamentoProcessoAPI();
            $entradaRemoverSobrestamentoProcessoAPI->setIdProcedimento($procedimentoDTOParam->getDblIdProcedimento());

            $seiRN->removerSobrestamentoProcesso($entradaRemoverSobrestamentoProcessoAPI);

            return array(
                'sucesso' => true,
                'mensagem' => 'Sobrestar cancelado com sucesso.'
            );
        }catch (Exception $e){
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
            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }
    }

    protected function listarProcedimentoAcompanhamentoConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOConsulta) {
        try{
            $usuarioAtribuicaoAtividade = null;
            $mdWsSeiProtocoloDTO = new MdWsSeiProtocoloDTO();
            if($mdWsSeiProtocoloDTOConsulta->isSetNumIdUsuarioAtribuicaoAtividade()){
                $mdWsSeiProtocoloDTO->setNumIdUsuarioAtribuicaoAtividade($mdWsSeiProtocoloDTOConsulta->getNumIdUsuarioAtribuicaoAtividade());
                $usuarioAtribuicaoAtividade = $mdWsSeiProtocoloDTOConsulta->getNumIdUsuarioAtribuicaoAtividade();
            }
            if(!$mdWsSeiProtocoloDTOConsulta->isSetNumIdUsuarioGeradorAcompanhamento()){
                $mdWsSeiProtocoloDTO->setNumIdUsuarioGeradorAcompanhamento(SessaoSEI::getInstance()->getNumIdUsuario());
            }else{
                $mdWsSeiProtocoloDTO->setNumIdUsuarioGeradorAcompanhamento($mdWsSeiProtocoloDTOConsulta->getNumIdUsuarioGeradorAcompanhamento());
            }

            if(is_null($mdWsSeiProtocoloDTOConsulta->getNumPaginaAtual())){
                $mdWsSeiProtocoloDTO->setNumPaginaAtual(0);
            }else{
                $mdWsSeiProtocoloDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOConsulta->getNumPaginaAtual());
            }

            if(!$mdWsSeiProtocoloDTOConsulta->isSetNumMaxRegistrosRetorno()){
                $mdWsSeiProtocoloDTO->setNumMaxRegistrosRetorno(10);
            }else{
                $mdWsSeiProtocoloDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOConsulta->getNumMaxRegistrosRetorno());
            }
            $protocoloRN = new ProtocoloRN();
            $mdWsSeiProtocoloDTOConsulta->retTodos();
            $mdWsSeiProtocoloDTOConsulta->retStrSinCienciaProcedimento();
            $mdWsSeiProtocoloDTOConsulta->setOrdDthGeracaoAcompanhamento(InfraDTO::$TIPO_ORDENACAO_ASC);
            $mdWsSeiProtocoloDTOConsulta->retStrNomeTipoProcedimentoProcedimento();
            $ret = $protocoloRN->listarRN0668($mdWsSeiProtocoloDTOConsulta);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);
            return array(
                'sucesso' => true,
                'data' => $result,
                'total' => $mdWsSeiProtocoloDTOConsulta->getNumTotalRegistros()
            );
        }catch (Exception $e){
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
            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }
    }

    /**
     * Metodo que lista os processos
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTO
     * @return array
     */
    protected function listarProcessosConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOConsulta){
        try{
            $mdWsSeiProtocoloDTO = new MdWsSeiProtocoloDTO();
            $mdWsSeiProtocoloDTO->setDthConclusaoAtividade(null);
            $mdWsSeiProtocoloDTO->retDblIdProtocolo();
            $mdWsSeiProtocoloDTO->retTodos();
            $mdWsSeiProtocoloDTO->retStrNomeTipoProcedimentoProcedimento();
            $mdWsSeiProtocoloDTO->retStrSinCienciaProcedimento();
            $mdWsSeiProtocoloDTO->setOrdDthAberturaAtividade(InfraDTO::$TIPO_ORDENACAO_ASC);
            $usuarioAtribuicaoAtividade = null;
            if($mdWsSeiProtocoloDTO->isSetNumIdUsuarioAtribuicaoAtividade()){
                $usuarioAtribuicaoAtividade = $mdWsSeiProtocoloDTO->getNumIdUsuarioAtribuicaoAtividade();
            }

            if(!$mdWsSeiProtocoloDTOConsulta->isSetNumIdUnidadeAtividade()){
                throw new InfraException('Unidade não informada.');
            }
            $mdWsSeiProtocoloDTO->setNumIdUnidadeAtividade($mdWsSeiProtocoloDTOConsulta->getNumIdUnidadeAtividade());

            if($mdWsSeiProtocoloDTOConsulta->isSetNumIdUsuarioAtribuicaoAtividade()){
                $mdWsSeiProtocoloDTO->setNumIdUsuarioAtribuicaoAtividade($mdWsSeiProtocoloDTOConsulta->getNumIdUsuarioAtribuicaoAtividade());
            }

            if(!is_null($mdWsSeiProtocoloDTOConsulta->getNumPaginaAtual())){
                $mdWsSeiProtocoloDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOConsulta->getNumPaginaAtual());
            }else{
                $mdWsSeiProtocoloDTO->setNumPaginaAtual(0);
            }

            if($mdWsSeiProtocoloDTOConsulta->isSetNumMaxRegistrosRetorno()){
                $mdWsSeiProtocoloDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOConsulta->getNumMaxRegistrosRetorno());
            }else{
                $mdWsSeiProtocoloDTO->setNumMaxRegistrosRetorno(10);
            }
            if(!$mdWsSeiProtocoloDTOConsulta->isSetNumIdUsuarioAtribuicaoAtividade()){
                $mdWsSeiProtocoloDTOConsulta->setNumIdUsuarioAtribuicaoAtividade(SessaoSEI::getInstance()->getNumIdUsuario());
            }

            if($mdWsSeiProtocoloDTOConsulta->getStrSinTipoBusca() == MdWsSeiProtocoloDTO::SIN_TIPO_BUSCA_M){
                $mdWsSeiProtocoloDTO->setNumIdUsuarioAtribuicaoAtividade($mdWsSeiProtocoloDTOConsulta->getNumIdUsuarioAtribuicaoAtividade());
            }else if($mdWsSeiProtocoloDTOConsulta->getStrSinTipoBusca() == MdWsSeiProtocoloDTO::SIN_TIPO_BUSCA_G){
                $mdWsSeiProtocoloDTO->adicionarCriterio(
                   array('StaEstado', 'SinInicialAtividade'),
                    array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IGUAL),
                    array(1, 'S'),
                    InfraDTO::$OPER_LOGICO_AND
                );
            }else{
                $mdWsSeiProtocoloDTO->adicionarCriterio(
                    array('StaEstado', 'SinInicialAtividade', 'IdTarefaAtividade'),
                    array(InfraDTO::$OPER_DIFERENTE, InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_DIFERENTE),
                    array(1, 'N', 1),
                    array(InfraDTO::$OPER_LOGICO_AND, InfraDTO::$OPER_LOGICO_AND)
                );
            }

            $protocoloRN = new ProtocoloRN();
            $ret = $protocoloRN->listarRN0668($mdWsSeiProtocoloDTO);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);
            return array(
                'sucesso' => true,
                'data' => $result,
                'total' => $mdWsSeiProtocoloDTO->getNumTotalRegistros()
            );
        }catch (Exception $e){
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
            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }

    }

    /**
     * Metodo que monta o retorno da listagem do processo com base no retorno da consulta
     * @param array $ret
     * @param null $usuarioAtribuicaoAtividade
     * @return array
     */
    private function montaRetornoListagemProcessos(array $ret, $usuarioAtribuicaoAtividade = null){

        $result = array();
        /** @var MdWsSeiProtocoloDTO $protocoloDTO */
        foreach($ret as $protocoloDTO){
            $usuarioAtribuido = null;
            $documentoNovo = 'N';
            $documentoPublicado = 'N';
            $possuiAnotacao = 'N';
            $possuiAnotacaoPrioridade = 'N';
            $usuarioVisualizacao = 'N';
            $tipoVisualizacao = 'N';
            $retornoProgramado = 'N';
            $retornoAtrasado = 'N';

            $atividadeRN = new AtividadeRN();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $atividadeDTOConsulta->retTodos(true);
            $atividadeDTOConsulta->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);
            $arrAtividades = $atividadeRN->listarRN0036($atividadeDTOConsulta);
            if($arrAtividades){
                /** @var AtividadeDTO $atividadeDTO */
                $atividadeDTO = $arrAtividades[0];
                $documentoNovo = $atividadeDTO->getNumIdTarefa() == 1 ? 'S' : 'N';
                $usuarioAtribuido = $atividadeDTO->getStrNomeUsuarioAtribuicao();
                $dadosRetornoProgramado = $this->checaRetornoProgramado($atividadeDTO);
                $retornoProgramado = $dadosRetornoProgramado['retornoProgramado'];
                $retornoAtrasado = $dadosRetornoProgramado['expirado'];
                $tipoVisualizacao = $atividadeDTO->getNumTipoVisualizacao() == 0 ? 'S' : 'N';
                if($atividadeDTO->getNumIdUsuarioVisualizacao() == $usuarioAtribuicaoAtividade){
                    $usuarioVisualizacao = 'S';
                }
            }
            $documentoRN = new DocumentoRN();
            $documentoDTOConsulta = new DocumentoDTO();
            $documentoDTOConsulta->setDblIdProcedimento($protocoloDTO->getDblIdProtocolo());
            $documentoDTOConsulta->retDblIdDocumento();
            $arrDocumentos = $documentoRN->listarRN0008($documentoDTOConsulta);
            if($arrDocumentos){
                $arrIdDocumentos = array();
                /** @var DocumentoDTO $documentoDTO */
                foreach($arrDocumentos as $documentoDTO){
                    $arrIdDocumentos[] = $documentoDTO->getDblIdDocumento();
                }
                $publiacaoRN = new PublicacaoRN();
                $publicacaoDTO = new PublicacaoDTO();
                $publicacaoDTO->retNumIdPublicacao();
                $publicacaoDTO->setNumMaxRegistrosRetorno(1);
                $publicacaoDTO->adicionarCriterio(
                    array('IdDocumento'),
                    array(InfraDTO::$OPER_IN),
                    array($arrIdDocumentos)
                );
                $arrPublicacaoDTO = $publiacaoRN->listarRN1045($publicacaoDTO);
                $documentoPublicado = count($arrPublicacaoDTO) ? 'S' : 'N';
            }

            $anotacaoRN = new AnotacaoRN();
            $anotacaoDTO = new AnotacaoDTO();
            $anotacaoDTO->setNumMaxRegistrosRetorno(1);
            $anotacaoDTO->retNumIdAnotacao();
            $anotacaoDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $arrAnotacao = $anotacaoRN->listar($anotacaoDTO);
            $possuiAnotacao = count($arrAnotacao) ? 'S' : 'N';
            $anotacaoDTO->setStrSinPrioridade('S');
            $arrAnotacaoPrioridade = $anotacaoRN->listar($anotacaoDTO);
            $possuiAnotacaoPrioridade = count($arrAnotacaoPrioridade) ? 'S' : 'N';

            $result[] = array(
                'id' => $protocoloDTO->getDblIdProtocolo(),
                'status' => $protocoloDTO->getStrStaProtocolo(),
                'atributos' => array(
                    'idProcedimento' => $protocoloDTO->getDblIdProtocolo(),
                    'idProtocolo' => $protocoloDTO->getDblIdProtocolo(),
                    'numero' => $protocoloDTO->getStrProtocoloFormatado(),
                    'tipoProcesso' => $protocoloDTO->getStrNomeTipoProcedimentoProcedimento(),
                    'descricao' => $protocoloDTO->getStrDescricao(),
                    'usuarioAtribuido' => $usuarioAtribuido,
                    'status' => array(
                        'documentoSigiloso' => $protocoloDTO->getStrStaGrauSigilo(),
                        'documentoRestrito' => $protocoloDTO->getStrStaNivelAcessoLocal() == 1 ? 'S' : 'N',
                        'documentoNovo' => $documentoNovo,
                        'documentoPublicado' => $documentoPublicado,
                        'anotacao' => $possuiAnotacao,
                        'anotacaoPrioridade' => $possuiAnotacaoPrioridade,
                        'ciencia' => $protocoloDTO->getStrSinCienciaProcedimento(),
                        'retornoProgramado' => $retornoProgramado,
                        'retornoAtrasado' => $retornoAtrasado,
                        'processoAcessadoUsuario' => $tipoVisualizacao,
                        // foi invertido o processoAcessadoUsuario e processoAcessadoUnidade,
                        // pois em todos os outros metodos e igual e somente neste era diferente...
                        'processoAcessadoUnidade' => $usuarioVisualizacao,
                    )
                )
            );
        }

        return $result;
    }

    private function checaRetornoProgramado($atividade){
        $retProgramado = 'N';
        $expirado = 'N';

        if ($atividade instanceof AtividadeDTO) {
            $retornoProgramadoRN = new RetornoProgramadoRN();
            $retornoProgramadoDTO = new RetornoProgramadoDTO();
            $retornoProgramadoDTO->adicionarCriterio(
                array('IdAtividadeEnvio', 'IdAtividadeRetorno'),
                array(InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_IGUAL),
                array($atividade->getNumIdAtividade(), null)
            );
            $retornoProgramadoDTO = $retornoProgramadoRN->consultar($retornoProgramadoDTO);

            if ($retornoProgramadoDTO) {
                echo 556;
                $expirado = ($retornoProgramadoDTO->getDtaProgramada() < new Datetime());
                $retProgramado = 'S';
            }
        }

        return ['retornoProgramado' => $retProgramado, 'expirado' => $expirado];
    }

    /**
     * Metodo que retorna as ciencias nos processos
     * @param ProtocoloDTO $protocoloDTOParam
     * @return array
     */
    protected function listarCienciaProcessoConectado(ProtocoloDTO $protocoloDTOParam){
        try{
            if(!$protocoloDTOParam->isSetDblIdProtocolo()){
                throw new InfraException('Protocolo não informado.');
            }

            $result = array();
            $mdWsSeiProcessoRN = new MdWsSeiProcessoRN();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->setDblIdProtocolo($protocoloDTOParam->getDblIdProtocolo());
            $atividadeDTOConsulta->setNumIdTarefa(TarefaRN::$TI_PROCESSO_CIENCIA);
            $atividadeDTOConsulta->retDthAbertura();
            $atividadeDTOConsulta->retStrSiglaUnidade();
            $atividadeDTOConsulta->retStrNomeTarefa();
            $atividadeDTOConsulta->retStrSiglaUsuarioOrigem();
            $atividadeDTOConsulta->retNumIdAtividade();
            $atividadeRN = new AtividadeRN();
            $ret = $atividadeRN->listarRN0036($atividadeDTOConsulta);
            /** @var AtividadeDTO $atividadeDTO */
            foreach($ret as $atividadeDTO){
                $mdWsSeiProcessoDTO = new MdWsSeiProcessoDTO();
                $mdWsSeiProcessoDTO->setStrTemplate($atividadeDTO->getStrNomeTarefa());
                $mdWsSeiProcessoDTO->setNumIdAtividade($atividadeDTO->getNumIdAtividade());
                $result[] = array(
                    'data' => $atividadeDTO->getDthAbertura(),
                    'unidade' => $atividadeDTO->getStrSiglaUnidade(),
                    'nome' => $atividadeDTO->getStrSiglaUsuarioOrigem(),
                    'descricao' => $mdWsSeiProcessoRN->traduzirTemplate($mdWsSeiProcessoDTO)
                );
            }
            return array(
                'sucesso' => true,
                'data' => $result
            );
        }catch (Exception $e){
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

            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }
    }


    /**
     * Metodo que da ciencia ao processo/procedimento
     * @param ProcedimentoDTO $procedimentoDTO
     * @info E obrigatorio informar o id do procedimento
     * @return array
     */
    protected function darCienciaControlado(ProcedimentoDTO $procedimentoDTO){
        try{
            if(!$procedimentoDTO->isSetDblIdProcedimento()){
                throw new InfraException('E obrigatorio informar o procedimento!');
            }

            $procedimentoRN = new ProcedimentoRN();
            $procedimentoRN->darCiencia($procedimentoDTO);

            return array(
                'sucesso' => true,
                'mensagem' => 'Ciência processo realizado com sucesso!'
            );
        }catch (Exception $e){
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
            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }
    }

    /**
     * Metodo que conclui o procedimento/processo
     * @param EntradaConcluirProcessoAPI $entradaConcluirProcessoAPI
     * @info ele recebe o n?mero do ProtocoloProcedimentoFormatadoPesquisa da tabela protocolo
     * @return array
     */
    protected function concluirProcessoControlado(EntradaConcluirProcessoAPI $entradaConcluirProcessoAPI){
        try{
            if(!$entradaConcluirProcessoAPI->getProtocoloProcedimento()){
                throw new InfraException('E obrigtorio informar o protocolo do procedimento!');
            }

            $objSeiRN = new SeiRN();
            $objSeiRN->concluirProcesso($entradaConcluirProcessoAPI);

            return array(
                'sucesso' => true,
                'mensagem' => 'Processo concluído com sucesso!'
            );
        }catch (Exception $e){
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
            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }
    }

    /**
     * Metodo que atribui o processo a uma pessoa
     * @param EntradaAtribuirProcessoAPI $entradaAtribuirProcessoAPI
     * @info Os parametros IdUsuario, ProtocoloProcedimento e SinReabrir sao obrigatorios. O parametro ProtocoloProcedimento
     * recebe o n?mero do ProtocoloProcedimentoFormatadoPesquisa da tabela protocolo
     * @return array
     */
    protected function atribuirProcessoControlado(EntradaAtribuirProcessoAPI $entradaAtribuirProcessoAPI){
        try{
            if(!$entradaAtribuirProcessoAPI->getProtocoloProcedimento()){
                throw new InfraException('E obrigatorio informar o protocolo do processo!');
            }
            if(!$entradaAtribuirProcessoAPI->getIdUsuario()){
                throw new InfraException('E obrigatorio informar o usu?rio do processo!');
            }

            $objSeiRN = new SeiRN();
            $objSeiRN->atribuirProcesso($entradaAtribuirProcessoAPI);

            return array(
                'sucesso' => true,
                'mensagem' => 'Processo atribuído com sucesso!'
            );
        }catch (Exception $e){
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
            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }
    }

    /**
     * Encapsula o objeto ENtradaEnviarProcessoAPI para o metodo enviarProcesso
     * @param array $post
     * @return EntradaEnviarProcessoAPI
     */
    public function encapsulaEnviarProcessoEntradaEnviarProcessoAPI(array $post){
        $entradaEnviarProcessoAPI = new EntradaEnviarProcessoAPI();
        if(isset($post['numeroProcesso'])){
            $entradaEnviarProcessoAPI->setProtocoloProcedimento($post['numeroProcesso']);
        }
        if(isset($post['unidadeDestino'])){
            $entradaEnviarProcessoAPI->setUnidadesDestino($post['unidadeDestino']);
        }
        if(isset($post['sinManterAbertoUnidade'])){
            $entradaEnviarProcessoAPI->setSinManterAbertoUnidade($post['sinManterAbertoUnidade']);
        }
        if(isset($post['sinRemoverAnotacao'])){
            $entradaEnviarProcessoAPI->setSinRemoverAnotacao($post['sinRemoverAnotacao']);
        }
        if(isset($post['sinEnviarEmailNotificacao'])){
            $entradaEnviarProcessoAPI->setSinEnviarEmailNotificacao($post['sinEnviarEmailNotificacao']);
        }
        if(isset($post['dataRetornoProgramado'])){
            $entradaEnviarProcessoAPI->setDataRetornoProgramado($post['dataRetornoProgramado']);
        }
        if(isset($post['diasRetornoProgramado'])){
            $entradaEnviarProcessoAPI->setDiasRetornoProgramado($post['diasRetornoProgramado']);
        }
        if(isset($post['sinDiasUteisRetornoProgramado'])){
            $entradaEnviarProcessoAPI->setSinDiasUteisRetornoProgramado($post['sinDiasUteisRetornoProgramado']);
        }
        if(isset($post['sinReabrir'])){
            $entradaEnviarProcessoAPI->setSinReabrir($post['sinReabrir']);
        }

        return $entradaEnviarProcessoAPI;
    }

    /**
     * Metodo que envia o processo para outra unidade
     * @param EntradaEnviarProcessoAPI $entradaEnviarProcessoAPI
     * @info Metodo auxiliar para encapsular dados encapsulaEnviarProcessoEntradaEnviarProcessoAPI
     * @return array
     */
    protected function enviarProcessoControlado(EntradaEnviarProcessoAPI $entradaEnviarProcessoAPI){
        try{
            $objSeiRN = new SeiRN();
            $objSeiRN->enviarProcesso($entradaEnviarProcessoAPI);

            return array(
                'sucesso' => true,
                'mensagem' => 'Processo enviado com sucesso!'
            );
        }catch (Exception $e){
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
            return array (
                "sucesso" => false,
                "mensagem" => $mensagem,
                "exception" => $e
            );
        }
    }



}