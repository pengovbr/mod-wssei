<?php
require_once DIR_SEI_WEB . '/SEI.php';


class MdWsSeiMarcadorRN extends MarcadorRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Pesquisa os marcadores
     * @param MarcadorDTO $marcadorDTOConsulta
     * @return array
     */
    protected function pesquisarConectado(MarcadorDTO $marcadorDTOConsulta)
    {
        try{
            $result = array();
            $marcadorDTOConsulta->retTodos();
            $marcadorDTOConsulta->setBolExclusaoLogica(false);
            $marcadorDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $marcadorDTOConsulta->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

            if($marcadorDTOConsulta->isSetStrSinAtivo() && !in_array($marcadorDTOConsulta->getStrSinAtivo(), array('S', 'N'))){
                throw new InfraException('Parametro ativo inválido.');
            }

            if($marcadorDTOConsulta->isSetStrNome()){
                $marcadorDTOConsulta->setStrNome(
                    '%'.$marcadorDTOConsulta->getStrNome().'%',
                    InfraDTO::$OPER_LIKE
                );
            }

            $marcadorRN = new MarcadorRN();
            /** Acessa o componente SEI para retornar os marcadores da pesquisa */
            $ret = $marcadorRN->listar($marcadorDTOConsulta);
            /** Chama o componente SEI para retornar as cores disponíveis para o Marcador */
            $arrIconeMarcadorDTO = $this->listarValoresIcone();
            $arrIconeMarcadorDTO = InfraArray::indexarArrInfraDTO($arrIconeMarcadorDTO, 'StaIcone');

            /** @var MarcadorDTO $marcadorDTO */
            foreach($ret as $marcadorDTO){

                $result[] = array(
                    'id' => $marcadorDTO->getNumIdMarcador(),
                    'nome' => $marcadorDTO->getStrNome(),
                    'ativo' => $marcadorDTO->getStrSinAtivo(),
                    'idCor' => $marcadorDTO->getStrStaIcone(),
                    'descricaoCor' => $arrIconeMarcadorDTO[$marcadorDTO->getStrStaIcone()]->getStrDescricao(),
                    'arquivoCor' => $arrIconeMarcadorDTO[$marcadorDTO->getStrStaIcone()]->getStrArquivo()
                );
            }
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $marcadorDTOConsulta->getNumTotalRegistros());
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Lista as cores dos marcadores
     * @return array
     */
    public function listarCores()
    {
        try{
            /** Acessa o componente SEI para retornar as cores dos marcadores */
            $ret = $this->listarValoresIcone();

            /** @var IconeMarcadorDTO $iconeMarcadorDTO */
            foreach($ret as $index => $iconeMarcadorDTO){

                $result[] = array(
                    'id' => $iconeMarcadorDTO->getStrStaIcone(),
                    'descricao' => $iconeMarcadorDTO->getStrDescricao(),
                    'arquivo' => $iconeMarcadorDTO->getStrArquivo(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, count($result));
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Cadastra um marcador
     * @param MarcadorDTO $marcadorDTO
     * @return array
     */
    protected function cadastrarControlado(MarcadorDTO $marcadorDTO)
    {
        try{
            $marcadorDTO->setNumIdMarcador(null);
            $marcadorDTO->setStrDescricao(null);
            $marcadorDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $marcadorDTO->setStrSinAtivo('S');
            $marcadorRN = new MarcadorRN();
            /** Chama o componente SEI para realizar o cadastro de um marcador */
            $marcadorDTO = $marcadorRN->cadastrar($marcadorDTO);

            /** Chama o componente SEI para retornar as cores disponíveis para o Marcador */
            $arrIconeMarcadorDTO = $marcadorRN->listarValoresIcone();

            $result = array(
                'id' => $marcadorDTO->getNumIdMarcador(),
                'nome' => $marcadorDTO->getStrNome(),
                'ativo' => $marcadorDTO->getStrSinAtivo(),
                'idCor' => $marcadorDTO->getStrStaIcone(),
                'descricaoCor' => $arrIconeMarcadorDTO[$marcadorDTO->getStrStaIcone()]->getStrDescricao(),
                'arquivoCor' => $arrIconeMarcadorDTO[$marcadorDTO->getStrStaIcone()]->getStrArquivo()
            );

            return MdWsSeiRest::formataRetornoSucessoREST('Marcador cadastrado com sucesso.', $result);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Edita um marcador
     * @param MarcadorDTO $marcadorDTO
     * @return array
     */
    protected function alterarControlado(MarcadorDTO $marcadorDTO)
    {
        try{
            if(!$marcadorDTO->getNumIdMarcador()){
                throw new InfraException('Marcador não informado.');
            }
            $marcadorRN = new MarcadorRN();
            $marcadorDTOConsulta = new MarcadorDTO();
            $marcadorDTOConsulta->retNumIdUnidade();
            $marcadorDTOConsulta->retStrSinAtivo();
            $marcadorDTOConsulta->retNumIdMarcador();
            $marcadorDTOConsulta->setNumIdMarcador($marcadorDTO->getNumIdMarcador());
            $marcadorDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $marcadorDTOConsulta->setBolExclusaoLogica(false);
            /** Chama o componente SEI para realizar a validaçao de existencia do marcador */
            $marcadorDTOConsulta = $marcadorRN->consultar($marcadorDTOConsulta);

            if(!$marcadorDTOConsulta){
                throw new InfraException('Marcador não encontrado.');
            }

            $marcadorDTOConsulta->setStrNome($marcadorDTO->getStrNome());
            $marcadorDTOConsulta->setStrStaIcone($marcadorDTO->getStrStaIcone());
            /** Chama o componente SEI para realizar a edição de um marcador */
            $marcadorRN->alterar($marcadorDTOConsulta);

            /** Chama o componente SEI para retornar as cores disponíveis para o Marcador */
            $arrIconeMarcadorDTO = $marcadorRN->listarValoresIcone();

            $result = array(
                'id' => $marcadorDTOConsulta->getNumIdMarcador(),
                'nome' => $marcadorDTOConsulta->getStrNome(),
                'ativo' => $marcadorDTOConsulta->getStrSinAtivo(),
                'idCor' => $marcadorDTOConsulta->getStrStaIcone(),
                'descricaoCor' => $arrIconeMarcadorDTO[$marcadorDTOConsulta->getStrStaIcone()]->getStrDescricao(),
                'arquivoCor' => $arrIconeMarcadorDTO[$marcadorDTOConsulta->getStrStaIcone()]->getStrArquivo()
            );

            return MdWsSeiRest::formataRetornoSucessoREST('Marcador alterado com sucesso.', $result);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Consulta o marcador de um processo
     * @param AndamentoMarcadorDTO $andamentoMarcadorDTO
     * @return array
     */
    protected function marcadorProcessoConsultarConectado(AndamentoMarcadorDTO $andamentoMarcadorDTOParam)
    {
        try{
            if(!$andamentoMarcadorDTOParam->getDblIdProcedimento()){
                throw new InfraException('Processo não informado.');
            }

            $procedimentoDTO = new ProcedimentoDTO();
            $procedimentoDTO->setDblIdProcedimento($andamentoMarcadorDTOParam->getDblIdProcedimento());
            $procedimentoDTO->retDblIdProcedimento();

            $procedimentoRN = new ProcedimentoRN();
            /** Acessa o componente SEI para consulta da existencia do processo */
            $procedimentoDTO = $procedimentoRN->consultarRN0201($procedimentoDTO);

            if ($procedimentoDTO == null) {
                throw new InfraException("Processo não encontrado.");
            }

            $result = array();

            $marcadorRN = new MarcadorRN();
            /** Chama o componente SEI para retornar as cores disponíveis para o Marcador */
            $arrIconeMarcadorDTO = $marcadorRN->listarValoresIcone();

            $andamentoMarcadorRN = new AndamentoMarcadorRN();
            $andamentoMarcadorDTO = new AndamentoMarcadorDTO();
            $andamentoMarcadorDTO->setDistinct(true);
            $andamentoMarcadorDTO->retNumIdMarcador();
            $andamentoMarcadorDTO->retStrStaIconeMarcador();
            $andamentoMarcadorDTO->retDblIdProcedimento();
            $andamentoMarcadorDTO->retStrTexto();
            $andamentoMarcadorDTO->setDblIdProcedimento(array($andamentoMarcadorDTOParam->getDblIdProcedimento()),InfraDTO::$OPER_IN);
            $andamentoMarcadorDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $andamentoMarcadorDTO->setStrSinUltimo('S');
            /** Chama o componente SEI para retornar o andamento do marcador */
            $arrObjAndamentoMarcadorDTO = $andamentoMarcadorRN->listar($andamentoMarcadorDTO);
            $andamentoMarcadorDTO = $arrObjAndamentoMarcadorDTO[0];
            if($andamentoMarcadorDTO){
                $result = array(
                    'idMarcador' => $andamentoMarcadorDTO->getNumIdMarcador(),
                    'idProtocolo' => $andamentoMarcadorDTO->getDblIdProcedimento(),
                    'texto' => $andamentoMarcadorDTO->getStrTexto(),
                    'idCor' => $andamentoMarcadorDTO->getStrStaIconeMarcador(),
                    'descricaoCor' => $arrIconeMarcadorDTO[$andamentoMarcadorDTO->getStrStaIconeMarcador()]->getStrDescricao(),
                    'arquivoCor' => $arrIconeMarcadorDTO[$andamentoMarcadorDTO->getStrStaIconeMarcador()]->getStrArquivo()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Lista o histórico do marcador do processo
     * @param AndamentoMarcadorDTO $andamentoMarcadorDTO
     * @return array
     */
    protected function listarHistoricoProcessoConectado(AndamentoMarcadorDTO $andamentoMarcadorDTOParam)
    {
        try{
            if(!$andamentoMarcadorDTOParam->getDblIdProcedimento()){
                throw new InfraException('Processo não informado.');
            }

            $procedimentoDTO = new ProcedimentoDTO();
            $procedimentoDTO->setDblIdProcedimento($andamentoMarcadorDTOParam->getDblIdProcedimento());
            $procedimentoDTO->retDblIdProcedimento();

            $procedimentoRN = new ProcedimentoRN();
            /** Acessa o componente SEI para consulta da existencia do processo */
            $procedimentoDTO = $procedimentoRN->consultarRN0201($procedimentoDTO);

            if ($procedimentoDTO == null) {
                throw new InfraException("Processo não encontrado.");
            }

            $result = array();

            $andamentoMarcadorDTOParam->retNumIdMarcador();
            $andamentoMarcadorDTOParam->retStrNomeMarcador();
            $andamentoMarcadorDTOParam->retStrSinAtivoMarcador();
            $andamentoMarcadorDTOParam->retStrTexto();
            $andamentoMarcadorDTOParam->retDthExecucao();
            $andamentoMarcadorDTOParam->retNumIdUsuario();
            $andamentoMarcadorDTOParam->retStrSiglaUsuario();
            $andamentoMarcadorDTOParam->retStrNomeUsuario();
            $andamentoMarcadorDTOParam->retNumIdAndamentoMarcador();
            $andamentoMarcadorDTOParam->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $andamentoMarcadorDTOParam->setOrdNumIdAndamentoMarcador(InfraDTO::$TIPO_ORDENACAO_DESC);

            $andamentoMarcadorRN = new AndamentoMarcadorRN();
            /** Chamada ao componente SEI para retorno da lista de histórico de marcador */
            $ret = $andamentoMarcadorRN->listar($andamentoMarcadorDTOParam);

            foreach($ret as $andamentoMarcadorDTO) {
                $result[] = array(
                    'marcadorAtivo' => $andamentoMarcadorDTO->getStrSinAtivoMarcador(),
                    'data' => substr($andamentoMarcadorDTO->getDthExecucao(), 0, 16),
                    'texto' => $andamentoMarcadorDTO->getStrTexto(),
                    'nomeMarcador' => ($andamentoMarcadorDTO->getNumIdMarcador() ? $andamentoMarcadorDTO->getStrNomeMarcador() : '[REMOVIDO]'),
                    'nomeUsuario' => $andamentoMarcadorDTO->getStrNomeUsuario(),
                    'siglaUsuario' => $andamentoMarcadorDTO->getStrSiglaUsuario(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que exclui marcadores
     * @param $arrIdMarcadores
     * @return array
     */
    public function excluirMarcadores(array $arrIdMarcadores)
    {
        try{
            if(empty($arrIdMarcadores)){
                throw new Exception('Marcador não informado.');
            }
            $marcadorRN = new MarcadorRN();
            $arrMarcadoresExclusao = array();
            foreach($arrIdMarcadores as $idMarcador) {
                $marcadorDTO = new MarcadorDTO();
                $marcadorDTO->setNumIdMarcador($idMarcador);
                $arrMarcadoresExclusao[] = $marcadorDTO;
            }
            /** Chama o componente SEI para exclusão de marcadores */
            $marcadorRN->excluir($arrMarcadoresExclusao);

            return MdWsSeiRest::formataRetornoSucessoREST('Marcador(es) excluído(s) com sucesso.', null);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que desativa marcadores
     * @param $arrIdMarcadores
     * @return array
     */
    public function desativarMarcadores(array $arrIdMarcadores)
    {
        try{
            if(empty($arrIdMarcadores)){
                throw new Exception('Marcador não informado.');
            }
            $marcadorRN = new MarcadorRN();
            $arrMarcadoresDesativar = array();
            foreach($arrIdMarcadores as $idMarcador) {
                $marcadorDTO = new MarcadorDTO();
                $marcadorDTO->setNumIdMarcador($idMarcador);
                $arrMarcadoresDesativar[] = $marcadorDTO;
            }
            /** Chama o componente SEI para desativar de marcadores */
            $marcadorRN->desativar($arrMarcadoresDesativar);

            return MdWsSeiRest::formataRetornoSucessoREST('Marcador(es) desativado(s) com sucesso.', null);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que reativar marcadores
     * @param $arrIdMarcadores
     * @return array
     */
    public function reativarMarcadores(array $arrIdMarcadores)
    {
        try{
            if(empty($arrIdMarcadores)){
                throw new Exception('Marcador não informado.');
            }
            $marcadorRN = new MarcadorRN();
            $arrMarcadoresResativar = array();
            foreach($arrIdMarcadores as $idMarcador) {
                $marcadorDTO = new MarcadorDTO();
                $marcadorDTO->setNumIdMarcador($idMarcador);
                $arrMarcadoresResativar[] = $marcadorDTO;
            }
            /** Chama o componente SEI para desativar de marcadores */
            $marcadorRN->reativar($arrMarcadoresResativar);

            return MdWsSeiRest::formataRetornoSucessoREST('Marcador(es) resativado(s) com sucesso.', null);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que relaciona um processo a um marcador
     * @param AndamentoMarcadorDTO $andamentoMarcadorDTO
     * @return array
     */
    protected function marcarProcessoControlado(AndamentoMarcadorDTO $andamentoMarcadorDTO)
    {
        try{
            if(empty($andamentoMarcadorDTO->getDblIdProcedimento())){
                throw new Exception('Processo não informado.');
            }

            $andamentoMarcadorRN = new AndamentoMarcadorRN();
            /** Chamando componente SEI para marcar processo */
            $andamentoMarcadorRN->cadastrar($andamentoMarcadorDTO);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo marcado com sucesso.', null);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}