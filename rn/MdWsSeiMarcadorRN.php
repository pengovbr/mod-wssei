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

}