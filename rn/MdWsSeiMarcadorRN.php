<?php
require_once dirname(__FILE__).'/../../../SEI.php';


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
            $marcadorDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $marcadorDTOConsulta->setOrdNumIdMarcador(InfraDTO::$TIPO_ORDENACAO_ASC);

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
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}