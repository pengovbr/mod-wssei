<?php
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiProcedimentoRN extends InfraRN
{

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    /**
     * Consulta o processo pelo protocolo
     * @param $protocolo
     * @return array
     */
    public function apiConsultarProcessoDigitado($protocolo){
        try{
            $result = ProcedimentoINT::pesquisarDigitadoRI1023($protocolo);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Retorna o total de unidades do processo
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    protected function listarUnidadesProcessoConectado(ProtocoloDTO $protocoloDTO)
    {
        try {
            if (!$protocoloDTO->getDblIdProtocolo()) {
                throw new InfraException('Protocolo n�o informado.');
            }
            $result = array();

            $relProtocoloProtocoloDTOConsulta = new RelProtocoloProtocoloDTO();
            $relProtocoloProtocoloDTOConsulta->setDblIdProtocolo1($protocoloDTO->getDblIdProtocolo());
            $relProtocoloProtocoloDTOConsulta->retDblIdProtocolo1();
            $relProtocoloProtocoloDTOConsulta->setNumMaxRegistrosRetorno(1);
            $relProtocoloProtocoloDTOConsulta->setNumPaginaAtual(0);
            $relProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            $ret = $relProtocoloProtocoloRN->listarRN0187($relProtocoloProtocoloDTOConsulta);
            if ($ret) {
                /** @var RelProtocoloProtocoloDTO $relProtocoloProtocoloDTO */
                $relProtocoloProtocoloDTO = $ret[0];
                $result['processo'] = $relProtocoloProtocoloDTO->getDblIdProtocolo1();
                $result['unidades'] = $relProtocoloProtocoloDTOConsulta->getNumTotalRegistros();
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

     /**
     * Retorna todos tipos de procedimentos filtrados
     * @param MdWsSeiTipoProcedimentoDTO $objGetMdWsSeiTipoProcedimentoDTO
     * @return array
     */
    protected function listarTipoProcedimentoConectado(MdWsSeiTipoProcedimentoDTO $objGetMdWsSeiTipoProcedimentoDTO)
    {
        try {
            
            $id         = $objGetMdWsSeiTipoProcedimentoDTO->getNumIdTipoProcedimento();
            $nome       = $objGetMdWsSeiTipoProcedimentoDTO->getStrNome();
//            $interno    = $objGetMdWsSeiTipoProcedimentoDTO->getStrSinInterno();
            $favoritos  = $objGetMdWsSeiTipoProcedimentoDTO->getStrFavoritos();
            $start      = $objGetMdWsSeiTipoProcedimentoDTO->getNumStart();
            $limit      = $objGetMdWsSeiTipoProcedimentoDTO->getNumLimit();


            // DTO QUE REPRESENTA OS TIPOS DE PROCESSO. 
            $objTipoProcedimentoDTO = new TipoProcedimentoDTO(); 
            $objTipoProcedimentoDTO->setStrSinSomenteUtilizados($favoritos); //Flag de FAVORITOS S (true) / N (false)
            
            //RETORNOS ESPERADOS NOS PAR�METROS DE SA�DA
            $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
            $objTipoProcedimentoDTO->retStrNome();
            $objTipoProcedimentoDTO->retStrSinInterno();

            //M�TODO QUE RETORNA A BUSCA DOS TIPOS DE PROCESSO APLICANDO AS REGRAS DE RESTRI��O POR �RG�O, UNIDADE E OUVIDORIA
            $objTipoProcedimentoRN = new TipoProcedimentoRN();
            $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarTiposUnidade($objTipoProcedimentoDTO); //Lista os tipos de processo
            
            $arrayObjs = array();
            //FILTRA NOME, ID e INTERNO
            if($arrObjTipoProcedimentoDTO){
                foreach ($arrObjTipoProcedimentoDTO as $aux) {

                    setlocale(LC_CTYPE, 'pt_BR'); // Defines para pt-br

                    $objDtoFormatado = strtolower(iconv('ISO-8859-1', 'ASCII//TRANSLIT', $aux->getStrNome()));
                    $nomeFormatado = str_replace('?','',strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome)));

                    if(
                        ($aux->getNumIdTipoProcedimento() == $id     || !$id)    
                            &&
                        (($nome && strpos($objDtoFormatado, $nomeFormatado) !== false) || !$nomeFormatado)
//                            &&
//                        ($aux->getStrSinInterno() == $interno       || !$interno)
                    ){
                        $arrayObjs[] = array(
                                            "id"                => $aux->getNumIdTipoProcedimento(),
                                            "nome"              => $aux->getStrNome()
                                        );
                    }
                }
            }
                      
            $arrayRetorno = array();
            $i = 0;
            //PERMITE SIGILOSO
            if(count($arrayObjs) > 0){
                foreach ($arrayObjs as $aux) {
                    $i++;
                    $objNivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
                    $objNivelAcessoPermitidoDTO->setNumIdTipoProcedimento($aux["id"]); // ID DO TIPO DE PROCESSO 
                    $objNivelAcessoPermitidoDTO->setStrStaNivelAcesso(ProtocoloRN::$NA_SIGILOSO);
                    
                    $objNivelAcessoPermitidoRN = new NivelAcessoPermitidoRN();
                    $permiteSigiloso = $objNivelAcessoPermitidoRN->contar($objNivelAcessoPermitidoDTO) > 0 ? true : false;            
                     

                    $arrayRetorno[] = array(
                                            "id"                => $aux["id"],
                                            "nome"              => $aux["nome"],
                                            "permiteSigiloso"   => $permiteSigiloso
                                        );
                }
            }

            $total = 0;
            $total = count($arrayRetorno);

            if($start) $arrayRetorno = array_slice($arrayRetorno, ($start-1));   
            if($limit) $arrayRetorno = array_slice($arrayRetorno, 0,($limit));


            /*$total = 0;
            $total = count($arrayRetorno);*/

            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno, $total);    
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
    
    
    
     /**
     * Retorna todos tipos de procedimentos filtrados
     * @param MdWsSeiTipoProcedimentoDTO $objGetMdWsSeiTipoProcedimentoDTO
     * @return array
     */
    protected function buscarTipoTemplateConectado(MdWsSeiTipoProcedimentoDTO $dto)
    {
        try {
            
            $id = $dto->getNumIdTipoProcedimento();

            //DTO QUE REPRESENTA A RELA��O ENTRE OS ASSUNTOS E OS TIPOS DE PROCESSO
            $relTipoProcedimentoAssuntoDTO = new RelTipoProcedimentoAssuntoDTO();
            $relTipoProcedimentoAssuntoDTO->setNumIdTipoProcedimento($id); // FILTRO PELO TIPO DE PROCESSO
            $relTipoProcedimentoAssuntoDTO->retNumIdAssunto(); // ID DO ASSUNTO QUE DEVE SE RETORNADO
            $relTipoProcedimentoAssuntoDTO->retStrCodigoEstruturadoAssunto(); // C�DIGO DO ASSUNTO QUE DEVE SE RETORNADO
            $relTipoProcedimentoAssuntoDTO->retStrDescricaoAssunto(); // DESCRI��O DO ASSUNTO QUE DEVE SER RETORNADA

            //CONSULTA QUE LISTA TODOS OS ASSUNTOS SUGERIDOS PARA O TIPO DE PROCESSO
            $relTipoProcedimentoAssuntoRN = new RelTipoProcedimentoAssuntoRN();
            $arrRelTipoProcedimentoAssuntoDTO = $relTipoProcedimentoAssuntoRN->listarRN0192($relTipoProcedimentoAssuntoDTO);
            
            $arrayRetorno = array();
            if($arrRelTipoProcedimentoAssuntoDTO){
                foreach ($arrRelTipoProcedimentoAssuntoDTO as $obj) {
                    $arrayRetorno["assuntos"][] = array(
                                        "id"        => $obj->getNumIdAssunto(),
                                        "codigo"    => $obj->getStrCodigoEstruturadoAssunto(),
                                        "descricao" => $obj->getStrDescricaoAssunto() 
                                    );
                }
            }
            
            //CONSULTA QUE LISTA TODOS OS N�VES DE ACESSOS PERMITIDOS PARA OS TIPO DE PROCESSO
            $nivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
            $nivelAcessoPermitidoDTO->setNumIdTipoProcedimento($id); // FILTRO PELO TIPO DE PROCESSO
            $nivelAcessoPermitidoDTO->retStrStaNivelAcesso(); // ID DO N�VEL DE ACESSO - ProtocoloRN::$NA_PUBLICO, ProtocoloRN::$NA_RESTRITO ou ProtocoloRN::$NA_SIGILOSO

            // A CONSULTA RETORNAR� OS N�VEL DE ACESSO PERMITIDOS PARA O TIPO DE PROCESSO ESPECIFICADO NO DTO. AQUELES QUE N�O FOREM RETORNADOS NESSA
            $nivelAcessoPermitidoRN = new NivelAcessoPermitidoRN();
            $arrNivelAcessoPermitido = $nivelAcessoPermitidoRN->listar($nivelAcessoPermitidoDTO);
            if($arrNivelAcessoPermitido){
                foreach ($arrNivelAcessoPermitido as $nivel) {
                    if($nivel->getStrStaNivelAcesso() == ProtocoloRN::$NA_PUBLICO)  $publico    = true;
                    if($nivel->getStrStaNivelAcesso() == ProtocoloRN::$NA_RESTRITO) $restrito   = true;
                    if($nivel->getStrStaNivelAcesso() == ProtocoloRN::$NA_SIGILOSO) $sigiloso   = true;
                }
            }
            $arrayRetorno["nivelAcessoPermitido"] = array(
                "publico"   =>$publico  ? $publico  : false,
                "restrito"  =>$restrito ? $restrito : false,
                "sigiloso"  =>$sigiloso ? $sigiloso : false,
            );
                       
            
            $tipoProcedimentoDTO = new TipoProcedimentoDTO();
            $tipoProcedimentoDTO->setNumIdTipoProcedimento($id);
            $tipoProcedimentoDTO->retStrStaNivelAcessoSugestao();
            $tipoProcedimentoDTO->retStrStaGrauSigiloSugestao();
            $tipoProcedimentoDTO->retNumIdHipoteseLegalSugestao();

            $tipoProcedimentoRN = new TipoProcedimentoRN();
            $tipoProcedimentoDTO = $tipoProcedimentoRN->consultarRN0267($tipoProcedimentoDTO);
            
//            $arrayRetorno["nivelAcessoSugerido"]    = $tipoProcedimentoDTO ? $tipoProcedimentoDTO->getStrStaNivelAcessoSugestao() : false;
//            $arrayRetorno["hipoteseLegalSugerida"]  = $tipoProcedimentoDTO ? $tipoProcedimentoDTO->getNumIdHipoteseLegalSugestao() : false;
//            $arrayRetorno["grauSigiloSugerido"]     = $tipoProcedimentoDTO ? $tipoProcedimentoDTO->getStrStaGrauSigiloSugestao() : false;
            
            
            //CONSULTA NO PAR�METRO QUE INFORMA SE A HIP�TESE LEGAL � OBRIGAT�RIO PARA UM TIPO DE PROCESSO
            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $obrigatoriedadeHipoteseLegal = $objInfraParametro->getValor('SEI_HABILITAR_HIPOTESE_LEGAL');

            //CONSULTA NO PAR�METRO QUE INFORMA SE UM GRAU DE SIGILO � OBRIGAT�RIO PARA UM TIPO DE PROCESSO
            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $obrigatoriedadeGrauSigilo = $objInfraParametro->getValor('SEI_HABILITAR_GRAU_SIGILO');
            
            $arrayRetorno["obrigatoriedadeHipoteseLegal"]   = $obrigatoriedadeHipoteseLegal;
            $arrayRetorno["obrigatoriedadeGrauSigilo"]      = $obrigatoriedadeGrauSigilo;
                                            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno);    
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Pesquisa Assuntos de um documento
     * @param AssuntoDTO $assuntoDTOParam
     * @return array
     */
    protected function pesquisarAssuntoConectado(AssuntoDTO $assuntoDTOParam)
    {
        try {
            $result = array();
            $assuntoDTOParam->retNumIdAssunto();
            $assuntoDTOParam->retStrCodigoEstruturado();
            $assuntoDTOParam->retStrDescricao();
            $assuntoDTOParam->setStrSinEstrutural('N');
            $assuntoDTOParam->setStrSinAtualTabelaAssuntos('S');
            $assuntoDTOParam->setOrdStrCodigoEstruturado(InfraDTO::$TIPO_ORDENACAO_ASC);
            $assuntoRN = new AssuntoRN();
            /** Chamando componente SEI para pesquisa de assuntos */
            $ret = $assuntoRN->pesquisarRN0246($assuntoDTOParam);

            /** @var AssuntoDTO $assuntoDTO */
            foreach ($ret as $assuntoDTO) {
                $result[] = array(
                    'codigoestruturadoformatado' => AssuntoINT::formatarCodigoDescricaoRI0568($assuntoDTO->getStrCodigoEstruturado(),$assuntoDTO->getStrDescricao()),
                    'descricao' => $assuntoDTO->getStrDescricao(),
                    'codigoestruturado' => $assuntoDTO->getStrCodigoEstruturado(),
                    'id' => $assuntoDTO->getNumIdAssunto()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $assuntoDTOParam->getNumTotalRegistros());
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Retorna todos tipos de procedimentos filtrados
     * @param MdWsSeiTipoProcedimentoDTO $objGetMdWsSeiTipoProcedimentoDTO
     * @return array
     */
    protected function listarAssuntoConectado(MdWsSeiAssuntoDTO $objGetMdWsSeiAssuntoDTO)
    {
        try {
            $id         = $objGetMdWsSeiAssuntoDTO->getNumIdAssunto();
            $filter     = $objGetMdWsSeiAssuntoDTO->getStrFilter();
            $start      = $objGetMdWsSeiAssuntoDTO->getNumStart();
            $limit      = $objGetMdWsSeiAssuntoDTO->getNumLimit();

            $assuntoDTO = new AssuntoDTO();
            if($id)
                $assuntoDTO->setNumIdAssunto($id);

            if($filter)  $assuntoDTO->adicionarCriterio(array('CodigoEstruturado','Descricao','Observacao'),array(InfraDTO::$OPER_LIKE,InfraDTO::$OPER_LIKE,InfraDTO::$OPER_LIKE),array('%'.utf8_decode($filter).'%','%'.utf8_decode($filter).'%','%'.utf8_decode($filter).'%'), array(InfraDTO::$OPER_LOGICO_OR,InfraDTO::$OPER_LOGICO_OR));
//                $objInfraAgendamentoTarefaDTO->adicionarCriterio(array('SinAtivo','IdInfraAgendamentoTarefa'),array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),array('S',$strValorItemSelecionado),InfraDTO::$OPER_LOGICO_OR);
//                $assuntoDTO->setStrCodigoEstruturado('%'.$filter.'%',InfraDTO::$OPER_LIKE);


            $assuntoRN = new AssuntoRN();

            $assuntoCountDTO = $assuntoDTO; // APENAS PARA TOTALIZAR OS REGISTROS DE RETORNO
            $assuntoCountDTO->retNumIdAssunto();
            $assuntoCountDTO = $assuntoRN->listarRN0247($assuntoCountDTO);


            if($limit)
                $assuntoDTO->setNumMaxRegistrosRetorno($limit);
            if($start)
                $assuntoDTO->setNumPaginaAtual($start);

            $assuntoDTO->retNumIdAssunto();
            $assuntoDTO->retStrCodigoEstruturado();
            $assuntoDTO->retStrDescricao();
            $assuntoDTO->retStrSinEstrutural();

            // REALIZA A CHAMADA DA DE ASSUNTOS

            $arrAssuntoDTO = $assuntoRN->listarRN0247($assuntoDTO);

            $arrayRetorno = array();
            if($arrAssuntoDTO){
                foreach ($arrAssuntoDTO as $obj) {

                    $arrayRetorno[]   = array(
                        "id"        => $obj->getNumIdAssunto(),
                        "codigo"    => $obj->getStrCodigoEstruturado(),
                        "descricao" => $obj->getStrDescricao(),
                        "item_apenas_estrutural" => ($obj->getStrSinEstrutural() == "S") ? true : false
                    );
                }
            }

//            $arrayRetorno = array();
//            if($start) $arrayRetorno = array_slice($arrayRetorno, ($start-1));
//            if($limit) $arrayRetorno = array_slice($arrayRetorno, 0,($limit));

            $total = 0;
            $total = count($assuntoCountDTO);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno, $total);
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que consulta o processo realizando todas as valida��es de permiss�o de acesso
     * @param $idProtocolo
     * @return array
     */
    protected function consultarConectado($idProtocolo)
    {
        try{
            $protocoloRN = new ProtocoloRN();
            $protocoloDTO = new MdWsSeiProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($idProtocolo);
            $protocoloDTO->retDblIdProtocolo();
            $protocoloDTO->retNumIdUnidadeGeradora();
            $protocoloDTO->retStrStaProtocolo();
            $protocoloDTO->retStrProtocoloFormatado();
            $protocoloDTO->retStrNomeTipoProcedimentoProcedimento();
            $protocoloDTO->retStrDescricao();
            $protocoloDTO->retStrSiglaUnidadeGeradora();
            $protocoloDTO->retStrStaGrauSigilo();
            $protocoloDTO->retStrStaNivelAcessoLocal();
            $protocoloDTO->retStrStaNivelAcessoGlobal();
            $protocoloDTO->retStrSinCienciaProcedimento();
            $protocoloDTO->retStrStaEstado();

            /** Chama o componente SEI para valida��o de existencia de Processo e retorno dos dados*/
            $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);

            if(!$protocoloDTO){
                throw new InfraException('Processo n�o encontrado.');
            }

            $pesquisaProtocoloDTO = new PesquisaProtocoloDTO();
            $pesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
            $pesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
            $pesquisaProtocoloDTO->setDblIdProtocolo($idProtocolo);

            /** Chama o componente SEI para verifica��o de permiss�o de acesso ao processo */
            $arrProtocoloDTO = $protocoloRN->pesquisarRN0967($pesquisaProtocoloDTO);

            if(empty($arrProtocoloDTO)){
                throw new InfraException("Acesso ao processo " . $idProtocolo . " n�o autorizado.");
            }

            /** Realiza montagem e enriquecimento de informa��es do processo */
            $result = $this->montaRetornoListagemProcessos(array($protocoloDTO));

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result[0]);
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
    
     /**
     * Realiza a consulta dos metadados de um processo especifico.
     * @param MdWsSeiProcedimentoDTO $dto
     * @return array
     */
    protected function consultarProcessoConectado(MdWsSeiProcedimentoDTO $dto)
    {
        try {
            $arrayRetorno = array();
            $id = $dto->getNumIdProcedimento();
           
            //Id do processo enviado por par�metro
            $processo = $id;

            // Recupera os dados do processo inseridos na tabela de protocolo 
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo($processo);
            $objProtocoloDTO->retStrDescricao(); // Recupera a especifica��o do processo
            $objProtocoloDTO->retStrStaNivelAcessoLocal(); // Recupera o n�vel de acesso do processo 
            $objProtocoloDTO->retNumIdHipoteseLegal(); // Recupera o id da hip�tese legal
            $objProtocoloDTO->retStrStaGrauSigilo(); // Recupera o grau de sigilo

            $protocoloRN = new ProtocoloRN();
            $objProtocoloDTO = $protocoloRN->consultarRN0186($objProtocoloDTO);


            if (!$objProtocoloDTO) {
                throw new Exception('N�o foi encontrado processo com id ' . $processo);
            }

            // Recupera o tipo de processo da tabela de procedimento
            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setDblIdProcedimento($processo);
            $objProcedimentoDTO->retNumIdTipoProcedimento();

            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

            // Recupera os assuntos do processo
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setDblIdProtocolo($processo);
            $objRelProtocoloAssuntoDTO->retStrCodigoEstruturadoAssunto();
            $objRelProtocoloAssuntoDTO->retStrDescricaoAssunto();
            $objRelProtocoloAssuntoDTO->retNumIdAssunto();

            $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
            $objRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);
            
            $assuntos = array();
            if($objRelProtocoloAssuntoDTO){
                foreach ($objRelProtocoloAssuntoDTO as $obj) {
                    $assuntos[] = array(
                                        "id"        => $obj->getNumIdAssunto(),
                                        "codigo"    => $obj->getStrCodigoEstruturadoAssunto(),
                                        "descricao" => $obj->getStrDescricaoAssunto() 
                                    );
                }
            }
            
            //Recupera os interessados do processo
            $objParticipanteDTO = new ParticipanteDTO();
            $objParticipanteDTO->setDblIdProtocolo($processo);
            $objParticipanteDTO->setStrStaParticipacao('I');
            $objParticipanteDTO->retNumIdContato();
            $objParticipanteDTO->retStrNomeContato();

            $objParticipanteRN = new ParticipanteRN();
            $objParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);
            
            $interessados = array();
            if($objParticipanteDTO){
                foreach ($objParticipanteDTO as $obj) {
                    $interessados[] = array(
                                        "id"    => $obj->getNumIdContato(),
                                        "nome"  => $obj->getStrNomeContato()
                                    );
                }
            }
            
            //Recupera as observa��es do process
            $objObservacaoDTO = new ObservacaoDTO();
            $objObservacaoDTO->setDblIdProtocolo($processo);
            $objObservacaoDTO->retStrDescricao();
            $objObservacaoDTO->retNumIdUnidade();

            $objObservacaoRN = new ObservacaoRN();
            $objObservacaoDTO = $objObservacaoRN->listarRN0219($objObservacaoDTO);
            
            $observacoes = array();
            if($objObservacaoDTO){
                foreach ($objObservacaoDTO as $obj) {
                    $observacoes[] = array(
                        "unidade"       =>$obj->getNumIdUnidade(),
                        "observacao"    =>$obj->getStrDescricao()
                    );
                }
            }
            
            $arrayRetorno = array(
                "especificacao"     => $objProtocoloDTO->getStrDescricao(),
                "tipoProcesso"      => $objProcedimentoDTO->getNumIdTipoProcedimento(),
                "assuntos"          => $assuntos,
                "interessados"      => $interessados,
                "nivelAcesso"       => $objProtocoloDTO->getStrStaNivelAcessoLocal(),
                "hipoteseLegal"     => $objProtocoloDTO->getNumIdHipoteseLegal(),
                "grauSigilo"        => $objProtocoloDTO->getStrStaGrauSigilo(),
                "observacoes"       => $observacoes 
            );
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno);    
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
    
    
    
     /**
     * Gerar Procedimento
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    protected function gerarProcedimentoConectado(MdWsSeiProcedimentoDTO $procedimentoDTO)
    {
        try {

//          Assuntos
            $arrayAssuntos = array();
            if($procedimentoDTO->getArrObjAssunto()){
                $i = 0;
                foreach ($procedimentoDTO->getArrObjAssunto() as $assunto) {
                    $i++;
                    $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                    $objRelProtocoloAssuntoDTO->setNumIdAssunto($assunto['id']);
                    $objRelProtocoloAssuntoDTO->setNumSequencia($i);
                    $arrayAssuntos[] = $objRelProtocoloAssuntoDTO;
                }
            }
//          Interessados 
            $arrayInteressados = array();
            if($procedimentoDTO->getArrObjInteressado()){
                $i = 0;
                foreach ($procedimentoDTO->getArrObjInteressado() as $interessado) {
                    $i++;
                    $objParticipanteDTO = new ParticipanteDTO();
                    $objParticipanteDTO->setNumIdContato($interessado['id']);
                    $objParticipanteDTO->setNumSequencia($i);
                    $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
                    
                    $arrayInteressados[] = $objParticipanteDTO;
                }
            }
            
            
            
            $objObservacaoDTO = new ObservacaoDTO();
            $objObservacaoDTO->setStrDescricao($procedimentoDTO->getStrObservacao());
            
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setStrDescricao($procedimentoDTO->getStrEspecificacao()); 
           // $objProtocoloDTO->setStrDescricaoObservacao('praxedes'); 
            $objProtocoloDTO->setStrStaNivelAcessoLocal($procedimentoDTO->getNumNivelAcesso());
            $objProtocoloDTO->setNumIdHipoteseLegal($procedimentoDTO->getNumIdHipoteseLegal());
//            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO(array($objRelProtocoloAssuntoDTO));
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrayAssuntos);
            $objProtocoloDTO->setArrObjParticipanteDTO($arrayInteressados);
            $objProtocoloDTO->setArrObjObservacaoDTO(array($objObservacaoDTO));
            $objProtocoloDTO->setStrStaGrauSigilo($procedimentoDTO->getStrStaGrauSigilo());
            
            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setNumIdTipoProcedimento($procedimentoDTO->getNumIdTipoProcedimento());
            $objProcedimentoDTO->setDblIdProcedimento(null);
            $objProcedimentoDTO->setObjProtocoloDTO($objProtocoloDTO);           
            $objProcedimentoDTO->setStrSinGerarPendencia('S');
                        
            
            $objProcedimentoRN = new ProcedimentoRN();
            $retorno = $objProcedimentoRN->gerarRN0156($objProcedimentoDTO);
//            var_dump($retorno);
            

        //ObjParticipanteDTO
        //ObjRelProtocoloAssuntoDTO
            
            
       /*     $objProcedimentoAPI = new ProcedimentoAPI();
            $objProcedimentoAPI->setIdTipoProcedimento($procedimentoDTO->getNumIdTipoProcedimento()); 
            $objProcedimentoAPI->setEspecificacao($procedimentoDTO->getStrEspecificacao());
            $objProcedimentoAPI->setAssuntos($procedimentoDTO->getArrObjAssunto());
            $objProcedimentoAPI->setInteressados($procedimentoDTO->getArrObjInteressado());
            $objProcedimentoAPI->setObservacao($procedimentoDTO->getStrObservacao());
                  
            // 0 publico
            // 1 restrito
            // 2 sigiloso
            $objProcedimentoAPI->setNivelAcesso($procedimentoDTO->getNumNivelAcesso());
            $objProcedimentoAPI->setIdHipoteseLegal($procedimentoDTO->getNumIdHipoteseLegal());
            
            
            $objEntradaGerarProcedimentoAPI = new EntradaGerarProcedimentoAPI();
            $objEntradaGerarProcedimentoAPI->setProcedimento($objProcedimentoAPI);
            
           // var_dump($objEntradaGerarProcedimentoAPI); die();
            $objSeiRN = new SeiRN();
            $aux = $objSeiRN->gerarProcedimento($objEntradaGerarProcedimentoAPI);*/
            
            return MdWsSeiRest::formataRetornoSucessoREST(null,
                    array(
                        "IdProcedimento"        => $retorno->getDblIdProcedimento(),
                        "ProtocoloFormatado"    => $retorno->getStrProtocoloProcedimentoFormatado())
                    );
        
        } catch (InfraException $e) {
            //die($e->getStrDescricao());
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que altera um processo atrav�s de uma requisi��o do Slim
     * @param \Slim\Http\Request $request
     * @return array
     */
    public function alterarProcessoRequest(\Slim\Http\Request $request)
    {
        try{
            if (!$request->getAttribute('route')->getArgument('protocolo')) {
                throw new Exception('O procedimento n�o foi informado.');
            }
            $procedimentoDTO = new ProcedimentoDTO();
            $procedimentoDTO->setDblIdProcedimento($request->getAttribute('route')->getArgument('protocolo'));
            $procedimentoDTO->retTodos(true);

            $procedimentoRN = new ProcedimentoRN();
            $procedimentoDTO = $procedimentoRN->consultarRN0201($procedimentoDTO);
            if(!$procedimentoDTO){
                throw new Exception('Procedimento n�o encontrado.');
            }
            $post = $request->getParams();
            $procedimentoDTO = self::encapsulaProcesso($post, $procedimentoDTO);
        }catch (Exception $e){
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }

        return $this->alterarProcedimento($procedimentoDTO);
    }

    /**
     * M�todo que altera o processo
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    protected function alterarProcedimentoControlado(ProcedimentoDTO $procedimentoDTO)
    {
        try {
            if (empty($procedimentoDTO->getDblIdProcedimento())) {
                throw new Exception('� obrigatorio informar o procedimento!');
            }
            $protocoloDTO = $procedimentoDTO->getObjProtocoloDTO();
            if(!empty($protocoloDTO->getArrObjParticipanteDTO()) && $protocoloDTO->isSetNumIdTipoProcedimentoProcedimento()){
                $tipoProcedimentoRN = new TipoProcedimentoRN();
                $tipoProcedimentoDTO = new TipoProcedimentoDTO();
                $tipoProcedimentoDTO->retStrSinIndividual();
                $tipoProcedimentoDTO->setNumIdTipoProcedimento($protocoloDTO->getNumIdTipoProcedimentoProcedimento());
                /** Consulta no componente SEI o tipo de processo */
                $tipoProcedimentoDTO = $tipoProcedimentoRN->consultarRN0267($tipoProcedimentoDTO);
                if(!$tipoProcedimentoDTO){
                    throw new Exception('Tipo de processo n�o encontrado.');
                }
                if($tipoProcedimentoDTO->getStrSinIndividual() == 'S' && count($protocoloDTO->getArrObjParticipanteDTO()) > 1){
                    throw new Exception('O tipo de processo � individual e foi informado mais de um interessado.');
                }
            }
            $procedimentoDTO->setStrSinGerarPendencia('N');
            $procedimentoRN = new ProcedimentoRN();
            /** Chama o componente SEI para alterar um processo */
            $procedimentoRN->alterarRN0202($procedimentoDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Procedimento alterado.');
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }


    /**
     * Apoia encapsulamento do processo na cria��o/edi��o
     * @param array $post
     * @param ProcedimentoDTO $objProcedimentoDTO
     * @return ProcedimentoDTO
     */
    public static function encapsulaProcesso(array $post, ProcedimentoDTO $objProcedimentoDTO = null)
    {
        $objProtocoloDTO = new ProtocoloDTO();
        if(!$objProcedimentoDTO){
            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProtocoloDTO->setDtaGeracao($post['dataGeracao']);
        }else{
            $objProtocoloDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
        }

        $objProtocoloDTO->setStrDescricao($post['especificacao']);
        $objProtocoloDTO->setStrStaNivelAcessoLocal($post['nivelAcesso']);
        $objProtocoloDTO->setNumIdHipoteseLegal($post['idHipoteseLegal']);
        $objProtocoloDTO->setStrStaGrauSigilo($post['grauSigilo']);
        $objProcedimentoDTO->setNumIdTipoProcedimento($post['idTipoProcesso']);
        $objProtocoloDTO->setNumIdTipoProcedimentoProcedimento($post['idTipoProcesso']);

        $objObservacaoDTO  = new ObservacaoDTO();
        $objObservacaoDTO->setStrDescricao($post['observacao']);
        $objProtocoloDTO->setArrObjObservacaoDTO(array($objObservacaoDTO));

        if (isset($post['assuntos']) && $post['assuntos'] != '') {
            $arrAssuntos = explode(',', $post['assuntos']);
            $arrObjAssuntosDTO = array();
            for ($x = 0; $x < count($arrAssuntos); $x++) {
                $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                $objRelProtocoloAssuntoDTO->setNumIdAssunto($arrAssuntos[$x]);
                $objRelProtocoloAssuntoDTO->setNumSequencia($x);
                $arrObjAssuntosDTO[$x] = $objRelProtocoloAssuntoDTO;
            }
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrObjAssuntosDTO);
        }else{
            $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO(array());
        }

        $arrObjParticipantesDTO = array();

        if (isset($post['interessados']) && $post['interessados'] != '') {
            $arrParticipantes = explode(',', $post['interessados']);
            for ($i = 0; $i < count($arrParticipantes); $i++) {
                $objParticipante = new ParticipanteDTO();
                $objParticipante->setNumIdContato($arrParticipantes[$i]);
                $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
                $objParticipante->setNumSequencia($i);
                $arrObjParticipantesDTO[] = $objParticipante;
            }
        }

        $objProtocoloDTO->setArrObjParticipanteDTO($arrObjParticipantesDTO);
        $objProcedimentoDTO->setObjProtocoloDTO($objProtocoloDTO);

        return $objProcedimentoDTO;
    }
    
    /**
     * M�todo que lista o sobrestamento de um processo
     * @param AtividadeDTO $atividadeDTOParam
     * @return array
     */
    protected function listarSobrestamentoProcessoConectado(AtividadeDTO $atividadeDTOParam)
    {
        try {
            if (!$atividadeDTOParam->isSetDblIdProtocolo()) {
                throw new InfraException('Protocolo n�o informado.');
            }
            if (!$atividadeDTOParam->isSetNumIdUnidade()) {
                $atividadeDTOParam->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            }

            $result = array();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->retTodos();
            $atividadeDTOConsulta->setDblIdProtocolo($atividadeDTOParam->getDblIdProtocolo());
            $atividadeDTOConsulta->setDthConclusao(null);
            $atividadeDTOConsulta->setNumIdTarefa(TarefaRN::$TI_SOBRESTAMENTO);
            $atividadeDTOConsulta->setNumMaxRegistrosRetorno(1);
            $atividadeRN = new AtividadeRN();
            $ret = $atividadeRN->listarRN0036($atividadeDTOConsulta);

            /** @var AtividadeDTO $atividadeDTO */
            foreach ($ret as $atividadeDTO) {
                $result[] = array(
                    'idAtividade' => $atividadeDTO->getNumIdAtividade(),
                    'idProtocolo' => $atividadeDTO->getDblIdProtocolo(),
                    'dthAbertura' => $atividadeDTO->getDthAbertura(),
                    'sinInicial' => $atividadeDTO->getStrSinInicial(),
                    'dtaPrazo' => $atividadeDTO->getDtaPrazo(),
                    'tipoVisualizacao' => $atividadeDTO->getNumTipoVisualizacao(),
                    'dthConclusao' => $atividadeDTO->getDthConclusao(),
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo de sobrestamento de processo
     * @param RelProtocoloProtocoloDTO $relProtocoloProtocoloDTO
     * @return array
     */
    protected function sobrestamentoProcessoControlado(RelProtocoloProtocoloDTO $relProtocoloProtocoloDTO)
    {
        try {
            if(!$relProtocoloProtocoloDTO->isSetDblIdProtocolo2()){
                throw new Exception('Processo n�o informado!');
            }
            if(!$relProtocoloProtocoloDTO->isSetStrMotivo()){
                throw new Exception('Informe o motivo!');
            }
            $procedimentoRN = new ProcedimentoRN();
            $procedimentoRN->sobrestarRN1014(array($relProtocoloProtocoloDTO));

            return MdWsSeiRest::formataRetornoSucessoREST('Processo sobrestado com sucesso');
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * @param $protocolo
     * @return array
     */
    protected function removerSobrestamentoProcessoControlado(ProcedimentoDTO $procedimentoDTOParam)
    {
        try {
            if (!$procedimentoDTOParam->getDblIdProcedimento()) {
                throw new InfraException('Procedimento n�o informado.');
            }
            $seiRN = new SeiRN();
            $entradaRemoverSobrestamentoProcessoAPI = new EntradaRemoverSobrestamentoProcessoAPI();
            $entradaRemoverSobrestamentoProcessoAPI->setIdProcedimento($procedimentoDTOParam->getDblIdProcedimento());

            $seiRN->removerSobrestamentoProcesso($entradaRemoverSobrestamentoProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Sobrestar cancelado com sucesso.');
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que retorna os procedimentos com acompanhamento do usu�rio
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOConsulta
     * @return array
     */
    protected function listarProcedimentoAcompanhamentoUsuarioConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam)
    {
        try {
            $usuarioAtribuicaoAtividade = null;
            $mdWsSeiProtocoloDTOConsulta = new MdWsSeiProtocoloDTO();
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()) {
                $mdWsSeiProtocoloDTOConsulta->setNumIdGrupoAcompanhamentoProcedimento($mdWsSeiProtocoloDTOParam->getNumIdGrupoAcompanhamentoProcedimento());
            }

            if (!$mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioGeradorAcompanhamento()) {
                $mdWsSeiProtocoloDTOConsulta->setNumIdUsuarioGeradorAcompanhamento(SessaoSEI::getInstance()->getNumIdUsuario());
            } else {
                $mdWsSeiProtocoloDTOConsulta->setNumIdUsuarioGeradorAcompanhamento($mdWsSeiProtocoloDTOParam->getNumIdUsuarioGeradorAcompanhamento());
            }

            if (empty($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $mdWsSeiProtocoloDTOConsulta->setNumPaginaAtual(0);
            } else {
                $mdWsSeiProtocoloDTOConsulta->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            }

            if (!$mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()) {
                $mdWsSeiProtocoloDTOConsulta->setNumMaxRegistrosRetorno(10);
            } else {
                $mdWsSeiProtocoloDTOConsulta->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            }

            $protocoloRN = new ProtocoloRN();
            $mdWsSeiProtocoloDTOConsulta->retTodos();
            $mdWsSeiProtocoloDTOConsulta->retDblIdProtocolo();
            $mdWsSeiProtocoloDTOConsulta->retStrNomeTipoProcedimentoProcedimento();
            $mdWsSeiProtocoloDTOConsulta->retStrSiglaUnidadeGeradora();
            $mdWsSeiProtocoloDTOConsulta->retStrSinCienciaProcedimento();
            $mdWsSeiProtocoloDTOConsulta->setOrdDthGeracaoAcompanhamento(InfraDTO::$TIPO_ORDENACAO_ASC);
            $mdWsSeiProtocoloDTOConsulta->retStrNomeTipoProcedimentoProcedimento();

            $ret = $protocoloRN->listarRN0668($mdWsSeiProtocoloDTOConsulta);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $mdWsSeiProtocoloDTOConsulta->getNumTotalRegistros());
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que retorna os procedimentos com acompanhamento da unidade
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOConsulta
     * @return array
     */
    protected function listarProcedimentoAcompanhamentoUnidadeConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam)
    {
        try {
            $acompanhamentoRN = new AcompanhamentoRN();
            $acompanhamentoDTO = new AcompanhamentoDTO();
            $acompanhamentoDTO->setOrdNumIdAcompanhamento(InfraDTO::$TIPO_ORDENACAO_DESC);
            if(!$mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()){
                throw new Exception('O grupo deve ser informado!');
            }else{
                $acompanhamentoDTO->setNumIdGrupoAcompanhamento($mdWsSeiProtocoloDTOParam->getNumIdGrupoAcompanhamentoProcedimento());
            }
            if (empty($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $acompanhamentoDTO->setNumPaginaAtual(0);
            } else {
                $acompanhamentoDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            }

            if (!$mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()) {
                $acompanhamentoDTO->setNumMaxRegistrosRetorno(10);
            } else {
                $acompanhamentoDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            }

            $arrAcompanhamentoDTO = $acompanhamentoRN->listarAcompanhamentosUnidade($acompanhamentoDTO);
            $totalRegistros = $acompanhamentoDTO->getNumTotalRegistros() ?: 0;

            $ret = array();
            foreach($arrAcompanhamentoDTO as $acompanhamentoDTO){
                $ret[] = $acompanhamentoDTO->getObjProcedimentoDTO();
            }
            $result = $this->montaRetornoListagemProcessos($ret, null);


            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $totalRegistros);
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que pesquisa todos o procedimentos em todas as unidades
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam
     * @return array
     */
    protected function pesquisarTodosProcessosConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam)
    {
        try {
            $pesquisaPendenciaDTO = new MdWsSeiPesquisarPendenciaDTO();

            $usuarioAtribuicaoAtividade = null;
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioAtribuicaoAtividade()) {
                $usuarioAtribuicaoAtividade = $mdWsSeiProtocoloDTOParam->getNumIdUsuarioAtribuicaoAtividade();
            }

            if (empty($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
            } else {
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            }

            if ($mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()) {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            } else {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno(10);
            }
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()) {
                $pesquisaPendenciaDTO->setNumIdGrupoAcompanhamentoProcedimento(
                    $mdWsSeiProtocoloDTOParam->getNumIdGrupoAcompanhamentoProcedimento()
                );
            }
            if ($mdWsSeiProtocoloDTOParam->isSetStrProtocoloFormatadoPesquisa()) {
                $strProtocoloFormatado = InfraUtil::retirarFormatacao(
                    $mdWsSeiProtocoloDTOParam->getStrProtocoloFormatadoPesquisa(), false
                );
                $pesquisaPendenciaDTO->setStrProtocoloFormatadoPesquisaProtocolo(
                    '%' . $strProtocoloFormatado . '%',
                    InfraDTO::$OPER_LIKE
                );
            }

            $atividadeRN = new MdWsSeiAtividadeRN();
            $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL, ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
            $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
            $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
            $pesquisaPendenciaDTO->setStrSinCredenciais('S');
            $pesquisaPendenciaDTO->setStrSinSituacoes('S');
            $pesquisaPendenciaDTO->setStrSinMarcadores('S');

            $ret = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $pesquisaPendenciaDTO->getNumTotalRegistros());
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que retorna os procedimentos com acompanhamento com filtro opcional de grupo de acompanhamento e protocolo
     * formatado
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam
     * @return array
     */
    protected function pesquisarProcedimentoConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam)
    {
        try {
            $pesquisaPendenciaDTO = new MdWsSeiPesquisarPendenciaDTO();

            $usuarioAtribuicaoAtividade = null;
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioAtribuicaoAtividade()) {
                $usuarioAtribuicaoAtividade = $mdWsSeiProtocoloDTOParam->getNumIdUsuarioAtribuicaoAtividade();
            }

            if (empty($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
            } else {
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            }

            if ($mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()) {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            } else {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno(10);
            }
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()) {
                $pesquisaPendenciaDTO->setNumIdGrupoAcompanhamentoProcedimento(
                    $mdWsSeiProtocoloDTOParam->getNumIdGrupoAcompanhamentoProcedimento()
                );
            }
            if ($mdWsSeiProtocoloDTOParam->isSetStrProtocoloFormatadoPesquisa()) {
                $strProtocoloFormatado = InfraUtil::retirarFormatacao(
                    $mdWsSeiProtocoloDTOParam->getStrProtocoloFormatadoPesquisa(), false
                );
                $pesquisaPendenciaDTO->setStrProtocoloFormatadoPesquisaProtocolo(
                    '%' . $strProtocoloFormatado . '%',
                    InfraDTO::$OPER_LIKE
                );
            }

            $atividadeRN = new MdWsSeiAtividadeRN();
            $pesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $pesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL, ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
            $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
            $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
            $pesquisaPendenciaDTO->setStrSinCredenciais('S');
            $pesquisaPendenciaDTO->setStrSinSituacoes('S');
            $pesquisaPendenciaDTO->setStrSinMarcadores('S');

            $ret = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $pesquisaPendenciaDTO->getNumTotalRegistros());
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que lista os processos
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTO
     * @return array
     */
    protected function listarProcessosConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam)
    {
        try {
            $pesquisaPendenciaDTO = new MdWsSeiPesquisarPendenciaDTO();

            $usuarioAtribuicaoAtividade = null;
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioAtribuicaoAtividade()) {
                $usuarioAtribuicaoAtividade = $mdWsSeiProtocoloDTOParam->getNumIdUsuarioAtribuicaoAtividade();
            }

            if (empty($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
            } else {
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            }
            
            if($mdWsSeiProtocoloDTOParam->isSetDblIdProtocolo()) {
                $pesquisaPendenciaDTO->setDblIdProtocolo($mdWsSeiProtocoloDTOParam->getDblIdProtocolo());
            }

            if ($mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()) {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            } else {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno(10);
            }
            if ($mdWsSeiProtocoloDTOParam->getStrSinApenasMeus() == 'S') {
                $pesquisaPendenciaDTO->setStrStaTipoAtribuicao('M');
            }

            $atividadeRN = new MdWsSeiAtividadeRN();
            $pesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $pesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL, ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
            $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
            $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
            $pesquisaPendenciaDTO->setStrSinCredenciais('S');
            $pesquisaPendenciaDTO->setStrSinSituacoes('S');
            $pesquisaPendenciaDTO->setStrSinMarcadores('S');

            if ($mdWsSeiProtocoloDTOParam->getStrSinTipoBusca() == MdWsSeiProtocoloDTO::SIN_TIPO_BUSCA_R) {
                $pesquisaPendenciaDTO->setStrSinInicial('N');
            } else if ($mdWsSeiProtocoloDTOParam->getStrSinTipoBusca() == MdWsSeiProtocoloDTO::SIN_TIPO_BUSCA_G) {
                $pesquisaPendenciaDTO->setStrSinInicial('S');
            }
            /** Chama o componente SEI para retorno da lista de processos */
            $ret = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
            /** Chama m�todo para padroniza��o e montagem de resultado */
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade, $mdWsSeiProtocoloDTOParam->getStrSinTipoBusca());

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $pesquisaPendenciaDTO->getNumTotalRegistros());
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }

    }

    /**
     * Metodo que monta o retorno da listagem do processo com base no retorno da consulta
     * @param array $ret
     * @param null $usuarioAtribuicaoAtividade
     * @return array
     */
    private function montaRetornoListagemProcessos(array $ret, $usuarioAtribuicaoAtividade = null , $typeSource = null)
    {

        $result = array();
        $protocoloRN = new ProtocoloRN();
        foreach ($ret as $dto) {
            $usuarioAtribuido = null;
            $documentoNovo = 'N';
            $documentoPublicado = 'N';
            $possuiAnotacao = 'N';
            $podeGerenciarCredenciais = 'N';
            $possuiAnotacaoPrioridade = 'N';
            $usuarioVisualizacao = 'N';
            $tipoVisualizacao = 'N';
            $retornoProgramado = 'N';
            $retornoAtrasado = 'N';
            $processoAberto = false;
            $acaoReabrirProcesso = SessaoSEI::getInstance()->verificarPermissao('procedimento_reabrir');
            $acaoRegistrarAnotacao = SessaoSEI::getInstance()->verificarPermissao('anotacao_registrar');
            $acaoRemoverSobrestamento = SessaoSEI::getInstance()->verificarPermissao('procedimento_remover_sobrestamento');
            $processoEmTramitacao = false;
            $processoSobrestado = false;
            $processoAnexado = false;
            $podeReabrirProcesso = false;
            $podeRegistrarAnotacao = false;
            $arrDadosAbertura = array();
            $procedimentoDTO = null;
            $resultAnotacao = array();
            $protocoloDTO = new MdWsSeiProtocoloDTO();
            if ($dto instanceof ProcedimentoDTO) {
                $protocoloDTO = new MdWsSeiProtocoloDTO();
                $protocoloDTO->setDblIdProtocolo($dto->getDblIdProcedimento());
                $protocoloDTO->retDblIdProtocolo();
                $protocoloDTO->retNumIdUnidadeGeradora();
                $protocoloDTO->retStrStaProtocolo();
                $protocoloDTO->retStrProtocoloFormatado();
                $protocoloDTO->retStrNomeTipoProcedimentoProcedimento();
                $protocoloDTO->retStrDescricao();
                $protocoloDTO->retStrSiglaUnidadeGeradora();
                $protocoloDTO->retStrStaGrauSigilo();
                $protocoloDTO->retStrStaNivelAcessoLocal();
                $protocoloDTO->retStrStaNivelAcessoGlobal();
                $protocoloDTO->retStrSinCienciaProcedimento();
                $protocoloDTO->retStrStaEstado();
                /** Chama o componente SEI para retorno dos dados do processo */
                $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
            } else {
                $protocoloDTO = $dto;
            }

            $processoBloqueado = $protocoloDTO->getStrStaEstado() == ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO ? 'S' : 'N';
            $processoRemocaoSobrestamento = 'N';
            $processoDocumentoIncluidoAssinado = 'N';
            $processoPublicado = 'N';

            $atividadeRN = new MdWsSeiAtividadeRN();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $atividadeDTOConsulta->retDblIdProtocolo();
            $atividadeDTOConsulta->retNumIdTarefa();
            $atividadeDTOConsulta->retNumTipoVisualizacao();
            $atividadeDTOConsulta->retStrNomeUsuarioAtribuicao();
            $atividadeDTOConsulta->retNumIdUsuarioVisualizacao();
            $atividadeDTOConsulta->retNumIdAtividade();

            $atividadeDTOConsulta->setNumMaxRegistrosRetorno(1);
            $atividadeDTOConsulta->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);

            $arrAtividadePendenciaDTO = array();
            if ($dto instanceof ProcedimentoDTO && $dto->isSetArrObjAtividadeDTO()) {
                $procedimentoDTO = $dto;
                $arrAtividadePendenciaDTO = $procedimentoDTO->getArrObjAtividadeDTO();
            } else {
                $pesquisaPendenciaDTO = new MdWsSeiPesquisarPendenciaDTO();
                $pesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                $pesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL, ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
                $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
                $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
                $pesquisaPendenciaDTO->setStrSinCredenciais('S');
                $pesquisaPendenciaDTO->setStrSinSituacoes('S');
                $pesquisaPendenciaDTO->setStrSinMarcadores('S');
                $pesquisaPendenciaDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
                /** Chama o componente SEI para retorno de dados complementares (nivel de acesso, etc) do processo */
                $arrProcedimentoDTO = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
                if ($arrProcedimentoDTO) {
                    $procedimentoDTO = $arrProcedimentoDTO[0];
                    $arrAtividadePendenciaDTO = $procedimentoDTO->getArrObjAtividadeDTO();
                }
            }

            $objAtividadesAbertasDTO = new AtividadeDTO();
            $objAtividadesAbertasDTO->retNumIdAtividade();
            $objAtividadesAbertasDTO->retNumTipoVisualizacao();
            $objAtividadesAbertasDTO->setDthConclusao(null);
            $objAtividadesAbertasDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $objAtividadesAbertasDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            /** CHama o componente SEI para retorno das permiss�es de acesso do usu�rio */
            $arrObjAtividadesAbertasDTO = $atividadeRN->listarRN0036($objAtividadesAbertasDTO);

            if ($arrObjAtividadesAbertasDTO) {
                $numTipoVisualizacao=$arrObjAtividadesAbertasDTO[0]->getNumTipoVisualizacao();    
            }

            if ($numTipoVisualizacao && ($numTipoVisualizacao == AtividadeRN::$TV_NAO_VISUALIZADO)){
                $usuarioVisualizacao = 'N';
            }
            else {
                $usuarioVisualizacao = 'S';
            }
            

            if ($arrAtividadePendenciaDTO) {
                $atividadePendenciaDTO = $arrAtividadePendenciaDTO[0];
                if ($atividadePendenciaDTO->getNumTipoVisualizacao() & AtividadeRN::$TV_REMOCAO_SOBRESTAMENTO) {
                    $processoRemocaoSobrestamento = 'S';
                }
                if ($atividadePendenciaDTO->getNumTipoVisualizacao() & AtividadeRN::$TV_ATENCAO) {
                    $processoDocumentoIncluidoAssinado = 'S';
                }
                if ($atividadePendenciaDTO->getNumTipoVisualizacao() & AtividadeRN::$TV_PUBLICACAO) {
                    $processoPublicado = 'S';
                }
                $retornoProgramadoDTOConsulta = new RetornoProgramadoDTO();
                $retornoProgramadoDTOConsulta->retDblIdProtocolo();
                
                $retornoProgramadoDTOConsulta->retStrSiglaUnidadeRetorno();
                $retornoProgramadoDTOConsulta->retStrSiglaUnidadeEnvio();
                $retornoProgramadoDTOConsulta->retDtaProgramada();
                $retornoProgramadoDTOConsulta->setNumIdUnidadeRetorno(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $retornoProgramadoDTOConsulta->setDblIdProtocolo(array_unique(InfraArray::converterArrInfraDTO($arrAtividadePendenciaDTO, 'IdProtocolo')), InfraDTO::$OPER_IN);
                $retornoProgramadoDTOConsulta->setNumIdAtividadeRetorno(null);
                $objRetornoProgramadoRN = new RetornoProgramadoRN();
                $arrRetornoProgramadoDTO = $objRetornoProgramadoRN->listar($retornoProgramadoDTOConsulta);
                if ($arrRetornoProgramadoDTO) {
                    $retornoProgramado = 'S';
                    $strDataAtual = InfraData::getStrDataAtual();
                    foreach ($arrRetornoProgramadoDTO as $retornoProgramadoDTO) {
                        $numPrazo = InfraData::compararDatas($strDataAtual, $retornoProgramadoDTO->getDtaProgramada());
                        if ($numPrazo < 0) {
                            $retornoAtrasado = 'S';
                        }
                        $retornoData = array(
                            'dataProgramada' => $retornoProgramadoDTO->getDtaProgramada(),
                            'unidade' => $retornoProgramadoDTO->getStrSiglaUnidadeEnvio(),
                        );
                    }
                }
            }
            $documentoRN = new DocumentoRN();
            $documentoDTOConsulta = new DocumentoDTO();
            $documentoDTOConsulta->setDblIdProcedimento($protocoloDTO->getDblIdProtocolo());
            $documentoDTOConsulta->retDblIdDocumento();
            /** Chama o componente SEI para retorno dos documentos do processo */
            $arrDocumentos = $documentoRN->listarRN0008($documentoDTOConsulta);
            if ($arrDocumentos) {
                $arrIdDocumentos = array();
                /** @var DocumentoDTO $documentoDTO */
                foreach ($arrDocumentos as $documentoDTO) {
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
                /** Chama o componente SEI para verifica��o de documentos publicados */
                $arrPublicacaoDTO = $publiacaoRN->listarRN1045($publicacaoDTO);
                $documentoPublicado = count($arrPublicacaoDTO) ? 'S' : 'N';
            }
            $anotacaoRN = new AnotacaoRN();
            $anotacaoDTOConsulta = new AnotacaoDTO();
            $anotacaoDTOConsulta->setNumMaxRegistrosRetorno(1);
            $anotacaoDTOConsulta->retDblIdProtocolo();
            $anotacaoDTOConsulta->retStrDescricao();
            $anotacaoDTOConsulta->retNumIdUnidade();
            $anotacaoDTOConsulta->retNumIdUsuario();
            $anotacaoDTOConsulta->retDthAnotacao();
            $anotacaoDTOConsulta->retStrSinPrioridade();
            $anotacaoDTOConsulta->retStrStaAnotacao();
            $anotacaoDTOConsulta->retNumIdAnotacao();
            $anotacaoDTOConsulta->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $anotacaoDTOConsulta->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            /** Chama o componente SEI para verifica��o de anota��es no processo */
            $arrAnotacao = $anotacaoRN->listar($anotacaoDTOConsulta);
            $possuiAnotacao = count($arrAnotacao) ? 'S' : 'N';
            foreach ($arrAnotacao as $anotacaoDTO) {
                if ($anotacaoDTO->getStrSinPrioridade() == 'S') {
                    $possuiAnotacaoPrioridade = 'S';
                    break;
                }
            }
            /** @var AnotacaoDTO $anotacaoDTO */
            foreach ($arrAnotacao as $anotacaoDTO) {
                $resultAnotacao[] = array(
                    'idAnotacao' => $anotacaoDTO->getNumIdAnotacao(),
                    'idProtocolo' => $anotacaoDTO->getDblIdProtocolo(),
                    'descricao' => $anotacaoDTO->getStrDescricao(),
                    'idUnidade' => $anotacaoDTO->getNumIdUnidade(),
                    'idUsuario' => $anotacaoDTO->getNumIdUsuario(),
                    'dthAnotacao' => $anotacaoDTO->getDthAnotacao(),
                    'sinPrioridade' => $anotacaoDTO->getStrSinPrioridade(),
                    'staAnotacao' => $anotacaoDTO->getStrStaAnotacao()
                );
            }
            if ($protocoloDTO->getStrStaEstado() != ProtocoloRN::$TE_PROCEDIMENTO_ANEXADO) {
                $procedimentoDTOParam = new ProcedimentoDTO();
                $procedimentoDTOParam->setDblIdProcedimento($protocoloDTO->getDblIdProtocolo());
                $procedimentoDTOParam->setStrStaNivelAcessoGlobalProtocolo($protocoloDTO->getStrStaNivelAcessoGlobal());
                /** Chama o componente SEI para verifica��o de unidades em que se encontra aberto */
                $arrDadosAbertura = $this->listarUnidadeAberturaProcedimento($procedimentoDTOParam);
            }

            if($protocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO){
                $podeGerenciarCredenciais = SessaoSEI::getInstance()->verificarPermissao('procedimento_credencial_gerenciar') ? 'S' : 'N';
            }

            $pesquisaPendenciaDTO = new PesquisaPendenciaDTO();
            $pesquisaPendenciaDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $pesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $pesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $pesquisaPendenciaDTO->setStrSinMontandoArvore('S');
            $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');

            /** Chama o componente SEI para verificar se existe retorno programado para o processo */
            $processoEmTramitacao = $processoAberto = count($atividadeRN->listarPendenciasRN0754($pesquisaPendenciaDTO)) == 1;
            if ($protocoloDTO->getNumIdUnidadeGeradora() == SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
                $processoEmTramitacao = true;
            }else{
                $atividadeDTO = new AtividadeDTO();
                $atividadeDTO->retNumIdAtividade();
                $atividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual(),InfraDTO::$OPER_DIFERENTE);
                $atividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $atividadeDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
                $atividadeDTO->setNumMaxRegistrosRetorno(1);

                /** Chama o componente SEI para verifica��o de tramita��o de processo */
                if ($atividadeRN->consultarRN0033($atividadeDTO)!=null){
                    $processoEmTramitacao = true;
                }
            }
            if ($protocoloDTO->getStrStaEstado() == ProtocoloRN::$TE_PROCEDIMENTO_SOBRESTADO){
                if ($processoAberto){
                    $processoAberto = false;
                }
                $processoSobrestado = true;
            }else if($protocoloDTO->getStrStaEstado()==ProtocoloRN::$TE_PROCEDIMENTO_ANEXADO){
                $processoAnexado = true;
            }
            if (!$processoAberto && $acaoReabrirProcesso && $processoEmTramitacao && !$processoSobrestado && !$processoAnexado) {
                $podeReabrirProcesso = true;
            }
            if ($processoEmTramitacao && $acaoRegistrarAnotacao) {
                $podeRegistrarAnotacao = true;
            }

            $podeRemoverSobrestamento = (
                !$processoBloqueado
                && count($atividadeRN->listarPendenciasRN0754($pesquisaPendenciaDTO)) == 1
                && $processoSobrestado && $acaoRemoverSobrestamento
            );

            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $processoGeradoRecebido = $protocoloDTO->getNumIdUnidadeGeradora() == SessaoSEI::getInstance()->getNumIdUnidadeAtual() ? 'G' : 'R';

            $arrDadosMarcador = array();

            $andamentoMarcadorRN = new AndamentoMarcadorRN();
            $andamentoMarcadorDTO = new AndamentoMarcadorDTO();
            $andamentoMarcadorDTO->setDblIdProcedimento($protocoloDTO->getDblIdProtocolo());
            $andamentoMarcadorDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $andamentoMarcadorDTO->setStrSinUltimo('S');
            $andamentoMarcadorDTO->retNumIdMarcador();
            $andamentoMarcadorDTO->retStrStaIconeMarcador();
            $andamentoMarcadorDTO->retStrNomeMarcador();
            $andamentoMarcadorDTO->retStrTexto();
            $andamentoMarcadorDTO->retStrNomeMarcador();
            /** Consulta o componente SEI para retorno do marcador do Processo **/
            $andamentoMarcadorDTO = $andamentoMarcadorRN->consultar($andamentoMarcadorDTO);
            $marcadorRN = new MarcadorRN();
            /** Chama o componente SEI para retornar as cores dispon�veis para o Marcador */
            $arrIconeMarcadorDTO = $marcadorRN->listarValoresIcone();
            if($andamentoMarcadorDTO && $arrIconeMarcadorDTO[$andamentoMarcadorDTO->getStrStaIconeMarcador()]){
                $arrDadosMarcador = array(
                    'idMarcador' => $andamentoMarcadorDTO->getNumIdMarcador(),
                    'nome' => $andamentoMarcadorDTO->getStrNomeMarcador(),
                    'nome' => $andamentoMarcadorDTO->getStrNomeMarcador(),
                    'texto' => $andamentoMarcadorDTO->getStrTexto(),
                    'idCor' => $andamentoMarcadorDTO->getStrStaIconeMarcador(),
                    'descricaoCor' => $arrIconeMarcadorDTO[$andamentoMarcadorDTO->getStrStaIconeMarcador()]->getStrDescricao(),
                    'arquivoCor' => $arrIconeMarcadorDTO[$andamentoMarcadorDTO->getStrStaIconeMarcador()]->getStrArquivo()
                );
            }

            $atividadeDTO = new AtividadeDTO();
            $atividadeDTO->retNumIdUsuarioAtribuicao();
            $atividadeDTO->retNumIdAtividade();
            $atividadeDTO->retNumIdTarefa();
            $atividadeDTO->retStrSiglaUsuarioAtribuicao();
            $atividadeDTO->retStrNomeUsuarioAtribuicao();
            $atividadeDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $atividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $atividadeDTO->setNumIdTarefa(
                array(TarefaRN::$TI_REMOCAO_ATRIBUICAO, TarefaRN::$TI_PROCESSO_ATRIBUIDO),
                InfraDTO::$OPER_IN
            );
            $atividadeDTO->setNumMaxRegistrosRetorno(1);
            $atividadeDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);
            $atividadeRN = new AtividadeRN();
            /** Consulta o componente SEI para retornar a atividade referente a atribui��o do usu�rio */
            $ret = $atividadeRN->listarRN0036($atividadeDTO);
            if(!empty($ret) && $ret[0]->getNumIdTarefa() != TarefaRN::$TI_REMOCAO_ATRIBUICAO){
                $usuarioAtribuido = array(
                    'idAtividade' => $ret[0]->getNumIdAtividade(),
                    'idUsuario' => $ret[0]->getNumIdUsuarioAtribuicao(),
                    'sigla' => $ret[0]->getStrSiglaUsuarioAtribuicao(),
                    'nome' => $ret[0]->getStrNomeUsuarioAtribuicao(),
                    'nomeformatado' => $ret[0]->getStrSiglaUsuarioAtribuicao().' - '.$ret[0]->getStrNomeUsuarioAtribuicao()
                );
            }

            $procedimentoHistoricoDTO = new ProcedimentoHistoricoDTO();
            $procedimentoHistoricoDTO->setNumPaginaAtual(0);
            $procedimentoHistoricoDTO->setNumMaxRegistrosRetorno(10);
            $procedimentoHistoricoDTO->setDblIdProcedimento($protocoloDTO->getDblIdProtocolo());

            /** Chamando servi�o para retorno das ciencias do processo **/
            $arrDadosCiencias = $this->listarCienciaProcesso($procedimentoHistoricoDTO);
            if(!$arrDadosCiencias['sucesso']){
                throw new InfraException(MdWsSeiRest::dataToIso88591($arrDadosCiencias['mensagem']));
            }

            $arrDadosCiencias = MdWsSeiRest::dataToIso88591($arrDadosCiencias);

            $arrCiencias = $arrDadosCiencias['data'];

            $result[] = array(
                'id' => $protocoloDTO->getDblIdProtocolo(),
                'status' => $protocoloDTO->getStrStaProtocolo(),
                'seiNumMaxDocsPasta' => $objInfraParametro->getValor('SEI_NUM_MAX_DOCS_PASTA'),
                'atributos' => array(
                    'idProcedimento' => $protocoloDTO->getDblIdProtocolo(),
                    'idProtocolo' => $protocoloDTO->getDblIdProtocolo(),
                    'numero' => $protocoloDTO->getStrProtocoloFormatado(),
                    'tipoProcesso' => $protocoloDTO->getStrNomeTipoProcedimentoProcedimento(),
                    'descricao' => $protocoloDTO->getStrDescricao(),
                    'usuarioAtribuido' => $usuarioAtribuido,
                    'unidade' => array(
                        'idUnidade' => $protocoloDTO->getNumIdUnidadeGeradora(),
                        'sigla' => $protocoloDTO->getStrSiglaUnidadeGeradora()
                    ),
                    'ciencias' => $arrCiencias,
                    'marcador' => $arrDadosMarcador,
                    'dadosAbertura' => $arrDadosAbertura,
                    'anotacoes' => $resultAnotacao,
                    'status' => array(
                        'documentoSigiloso' => $protocoloDTO->getStrStaGrauSigilo(),
                        'documentoRestrito' => $protocoloDTO->getStrStaNivelAcessoLocal() == 1 ? 'S' : 'N',
                        'documentoNovo' => $documentoNovo,
                        'documentoPublicado' => $documentoPublicado,
                        'anotacao' => $possuiAnotacao,
                        'anotacaoPrioridade' => $possuiAnotacaoPrioridade,//verificar
                        'ciencia' => $protocoloDTO->getStrSinCienciaProcedimento(),
                        'retornoProgramado' => $retornoProgramado,
                        'retornoData' => $retornoData,
                        'retornoAtrasado' => $retornoAtrasado,
                        'processoAcessadoUsuario' => $tipoVisualizacao,
                        'processoAcessadoUnidade' => $usuarioVisualizacao,
                        'processoRemocaoSobrestamento' => $processoRemocaoSobrestamento,
                        'processoBloqueado' => $processoBloqueado,
                        'processoDocumentoIncluidoAssinado' => $processoDocumentoIncluidoAssinado,
                        'processoPublicado' => $processoPublicado,
                        'nivelAcessoGlobal' => $protocoloDTO->getStrStaNivelAcessoGlobal(),
                        'podeGerenciarCredenciais' => $podeGerenciarCredenciais,
                        'processoAberto' => $processoAberto ? 'S' : 'N',
                        'processoEmTramitacao' => $processoEmTramitacao ? 'S' : 'N',
                        'processoSobrestado' => $processoSobrestado ? 'S' : 'N',
                        'processoAnexado' => $processoAnexado ? 'S' : 'N',
                        'podeReabrirProcesso' => $podeReabrirProcesso ? 'S' : 'N',
                        'podeRegistrarAnotacao' => $podeRegistrarAnotacao ? 'S' : 'N',
                        'podeRemoverSobrestamento' => $podeRemoverSobrestamento,
                        'tipo' => $typeSource,
                        'processoGeradoRecebido' => $processoGeradoRecebido
                    )
                )
            );
        }

        return $result;
    }

    protected function listarUnidadeAberturaProcedimentoConectado(ProcedimentoDTO $procedimentoDTO)
    {
        $result = array();
        $atividadeRN = new MdWsSeiAtividadeRN();
        $strStaNivelAcessoGlobal = $procedimentoDTO->getStrStaNivelAcessoGlobalProtocolo();
        $dblIdProcedimento = $procedimentoDTO->getDblIdProcedimento();
        $atividadeDTO = new AtividadeDTO();
        $atividadeDTO->setDistinct(true);
        $atividadeDTO->retStrSiglaUnidade();
        $atividadeDTO->retNumIdUnidade();
        $atividadeDTO->retStrDescricaoUnidade();

        $atividadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

        if ($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO) {
            $atividadeDTO->retNumIdUsuario();
            $atividadeDTO->retStrSiglaUsuario();
            $atividadeDTO->retStrNomeUsuario();
        } else {
            $atividadeDTO->retNumIdUsuarioAtribuicao();
            $atividadeDTO->retStrSiglaUsuarioAtribuicao();
            $atividadeDTO->retStrNomeUsuarioAtribuicao();

            //ordena descendente pois no envio de processo que j� existe na unidade e est� atribu�do ficar� com mais de um andamento em aberto
            //desta forma os andamentos com usu�rio nulo (envios do processo) ser�o listados depois
            $atividadeDTO->setOrdStrSiglaUsuarioAtribuicao(InfraDTO::$TIPO_ORDENACAO_DESC);

        }
        $atividadeDTO->setDblIdProtocolo($dblIdProcedimento);
        $atividadeDTO->setDthConclusao(null);

        //sigiloso sem credencial nao considera o usuario atual
        if ($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO) {

            $acessoDTO = new AcessoDTO();
            $acessoDTO->setDistinct(true);
            $acessoDTO->retNumIdUsuario();
            $acessoDTO->setDblIdProtocolo($dblIdProcedimento);
            $acessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);

            $acessoRN = new AcessoRN();
            $arrAcessoDTO = $acessoRN->listar($acessoDTO);

            $atividadeDTO->setNumIdUsuario(InfraArray::converterArrInfraDTO($arrAcessoDTO, 'IdUsuario'), InfraDTO::$OPER_IN);
        }
        $arrAtividadeDTO = $atividadeRN->listarRN0036($atividadeDTO);

        if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
            $arrAtividadeDTO = InfraArray::distinctArrInfraDTO($arrAtividadeDTO, 'SiglaUnidade');
        }
        if (count($arrAtividadeDTO) == 0) {
            $result['info'] = 'Processo n�o possui andamentos abertos.';
            $result['lista'] = array();
            $result['unidades'] = array();
        } else {
            if (count($arrAtividadeDTO) == 1) {
                $atividadeDTO = $arrAtividadeDTO[0];
                if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                    $result['info'] = 'Processo aberto somente na unidade:';
                    $result['unidades'][] = array(
                        'id' => $atividadeDTO->getNumIdUnidade(),
                        'nome' => $atividadeDTO->getStrSiglaUnidade()
                    );
                    $result['lista'][] = array(
                        'sigla' => $atividadeDTO->getStrSiglaUnidade()
                    );
                } else {
                    $result['info'] = 'Processo aberto com o usu�rio:';
                    $atividadeDTO = $arrAtividadeDTO[0];
                    $result['unidades'][] = array(
                        'id' => $atividadeDTO->getNumIdUnidade(),
                        'nome' => $atividadeDTO->getStrSiglaUnidade()
                    );
                    $result['lista'][] = array(
                        'sigla' => $atividadeDTO->getStrNomeUsuario()
                    );
                }
            } else {
                if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                    $result['info'] = 'Processo aberto nas unidades:';
                    foreach ($arrAtividadeDTO as $atividadeDTO) {
                        $result['unidades'][] = array(
                            'id' => $atividadeDTO->getNumIdUnidade(),
                            'nome' => $atividadeDTO->getStrSiglaUnidade()
                        );
                        $sigla = $atividadeDTO->getStrSiglaUnidade();
                        if ($atividadeDTO->getNumIdUsuarioAtribuicao() != null) {
                            $sigla .= ' (atribu�do a ' . $atividadeDTO->getStrNomeUsuarioAtribuicao() . ')';
                        }
                        $result['lista'][] = array(
                            'sigla' => $sigla
                        );
                    }
                } else {
                    $result['info'] = 'Processo aberto com os usu�rios:';
                    foreach ($arrAtividadeDTO as $atividadeDTO) {
                        $result['unidades'][] = array(
                            'id' => $atividadeDTO->getNumIdUnidade(),
                            'nome' => $atividadeDTO->getStrSiglaUnidade()
                        );
                        $sigla = $atividadeDTO->getStrNomeUsuario() . ' na unidade ' . $atividadeDTO->getStrSiglaUnidade();
                        $result['lista'][] = array(
                            'sigla' => $sigla
                        );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Metodo que retorna a lista de ciencias do processo
     * @param ProcedimentoHistoricoDTO $procedimentoHistoricoDTO
     * @return array
     */
    protected function listarCienciaProcessoConectado(ProcedimentoHistoricoDTO $procedimentoHistoricoDTO)
    {
        try {
            $result = array();
            if(!$procedimentoHistoricoDTO->isSetDblIdProcedimento()){
                throw new InfraException('Processo n�o informado.');
            }
            $procedimentoHistoricoDTO->setStrStaHistorico(ProcedimentoRN::$TH_PERSONALIZADO);
            $procedimentoHistoricoDTO->setStrSinGerarLinksHistorico('N');
            $procedimentoHistoricoDTO->setNumIdTarefa(array(TarefaRN::$TI_PROCESSO_CIENCIA,TarefaRN::$TI_PROCESSO_ANEXADO_CIENCIA,TarefaRN::$TI_DOCUMENTO_CIENCIA));

            $procedimentoRN = new ProcedimentoRN();
            /** Acessa o componente SEI para retorno das ciencias do processo **/
            $procedimentoDTO = $procedimentoRN->consultarHistoricoRN1025($procedimentoHistoricoDTO);

            if($procedimentoDTO){
                $arrAtividadeDTO = $procedimentoDTO->getArrObjAtividadeDTO();
                foreach($arrAtividadeDTO as $atividadeDTO) {
                    $result[] = array(
                        'idProtocolo' => $atividadeDTO->getDblIdProtocolo(),
                        'idAtividade' => $atividadeDTO->getNumIdAtividade(),
                        'data' => $atividadeDTO->getDthAbertura(),
                        'idUnidade' => $atividadeDTO->getNumIdUnidade(),
                        'unidade' => $atividadeDTO->getStrDescricaoUnidade(),
                        'siglaUnidade' => $atividadeDTO->getStrSiglaUnidade(),
                        'idUsuario' => $atividadeDTO->getNumIdUsuarioOrigem(),
                        'siglaUsuario' => $atividadeDTO->getStrNomeUsuarioOrigem(),
                        'nomeUsuario' => $atividadeDTO->getStrSiglaUsuarioOrigem(),
                        'descricao' => $atividadeDTO->getStrNomeTarefa(),
                    );
                }
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $procedimentoHistoricoDTO->getNumTotalRegistros());
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }


    /**
     * Metodo que da ciencia ao processo/procedimento
     * @param ProcedimentoDTO $procedimentoDTO
     * @info E obrigatorio informar o id do procedimento
     * @return array
     */
    protected function darCienciaControlado(ProcedimentoDTO $procedimentoDTOParam)
    {
        try {
            if (!$procedimentoDTOParam->isSetDblIdProcedimento()) {
                throw new InfraException('E obrigatorio informar o procedimento!');
            }

            $procedimentoRN = new ProcedimentoRN();
            $procedimentoRN->darCiencia($procedimentoDTOParam);

            return MdWsSeiRest::formataRetornoSucessoREST('Ci�ncia processo realizado com sucesso!');
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que conclui o procedimento/processo
     * @param EntradaConcluirProcessoAPI $entradaConcluirProcessoAPI
     * @info ele recebe o n�mero do ProtocoloProcedimentoFormatadoPesquisa da tabela protocolo
     * @return array
     */
    protected function concluirProcessoControlado(EntradaConcluirProcessoAPI $entradaConcluirProcessoAPI)
    {
        try {
            if (!$entradaConcluirProcessoAPI->getProtocoloProcedimento()) {
                throw new InfraException('E obrigtorio informar o protocolo do procedimento!');
            }

            $objSeiRN = new SeiRN();
            $objSeiRN->concluirProcesso($entradaConcluirProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo conclu�do com sucesso!');
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que reabre o procedimento/processo
     * @param EntradaReabrirProcessoAPI $entradaReabrirProcessoAPI
     * @return array
     */
    protected function reabrirProcessoControlado(EntradaReabrirProcessoAPI $entradaReabrirProcessoAPI)
    {
        try {
            if (!$entradaReabrirProcessoAPI->getIdProcedimento()) {
                throw new InfraException('E obrigtorio informar o id do procedimento!');
            }
            $objSeiRN = new SeiRN();
            $objSeiRN->reabrirProcesso($entradaReabrirProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo reaberto com sucesso!');
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que atribui o processo a uma pessoa
     * @param EntradaAtribuirProcessoAPI $entradaAtribuirProcessoAPI
     * @info Os parametros IdUsuario, ProtocoloProcedimento e SinReabrir sao obrigatorios. O parametro ProtocoloProcedimento
     * recebe o n?mero do ProtocoloProcedimentoFormatadoPesquisa da tabela protocolo
     * @return array
     */
    protected function atribuirProcessoControlado(EntradaAtribuirProcessoAPI $entradaAtribuirProcessoAPI)
    {
        try {
            if (!$entradaAtribuirProcessoAPI->getProtocoloProcedimento()) {
                throw new InfraException('E obrigatorio informar o protocolo do processo!');
            }
            if (!$entradaAtribuirProcessoAPI->getIdUsuario()) {
                throw new InfraException('E obrigatorio informar o usu?rio do processo!');
            }

            $objSeiRN = new SeiRN();
            $objSeiRN->atribuirProcesso($entradaAtribuirProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo atribu�do com sucesso!');
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que remove a atribui��o de um usu�rio a um processo
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    public function removerAtribuicaoControlado(ProtocoloDTO $protocoloDTO)
    {
        try{
            if(!$protocoloDTO->isSetDblIdProtocolo() || !$protocoloDTO->getDblIdProtocolo()){
                throw new Exception('Protocolo n�o informado.');
            }
            $atribuirDTO = new AtribuirDTO();
            $atribuirDTO->setNumIdUsuarioAtribuicao(null);
            $atribuirDTO->setArrObjProtocoloDTO(array($protocoloDTO));
            $atividadeRN = new AtividadeRN();
            /** Chamada ao componente SEI para remover a atribui��o */
            $atividadeRN->atribuirRN0985($atribuirDTO);
            return MdWsSeiRest::formataRetornoSucessoREST('Atribui��o removida com sucesso!');
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que retorna a atribui��o de um usu�rio a um processo
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    public function consultarAtribuicaoConectado(ProtocoloDTO $protocoloDTO)
    {
        try{
            if(!$protocoloDTO->isSetDblIdProtocolo() || !$protocoloDTO->getDblIdProtocolo()){
                throw new Exception('Protocolo n�o informado.');
            }
            $result = array();
            $atividadeDTO = new AtividadeDTO();
            $atividadeDTO->retNumIdUsuarioAtribuicao();
            $atividadeDTO->retNumIdAtividade();
            $atividadeDTO->retNumIdTarefa();
            $atividadeDTO->retStrSiglaUsuarioAtribuicao();
            $atividadeDTO->retStrNomeUsuarioAtribuicao();
            $atividadeDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $atividadeDTO->setNumIdTarefa(
                array(TarefaRN::$TI_REMOCAO_ATRIBUICAO, TarefaRN::$TI_PROCESSO_ATRIBUIDO),
                InfraDTO::$OPER_IN
            );
            $atividadeDTO->setNumMaxRegistrosRetorno(1);
            $atividadeDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);
            $atividadeRN = new AtividadeRN();
            /** Consulta o componente SEI para retornar a atividade referente a atribui��o do usu�rio */
            $ret = $atividadeRN->listarRN0036($atividadeDTO);
            if(!empty($ret) && $ret[0]->getNumIdTarefa() != TarefaRN::$TI_REMOCAO_ATRIBUICAO){
                $result = array(
                    'idAtividade' => $ret[0]->getNumIdAtividade(),
                    'idUsuario' => $ret[0]->getNumIdUsuarioAtribuicao(),
                    'sigla' => $ret[0]->getStrSiglaUsuarioAtribuicao(),
                    'nome' => $ret[0]->getStrNomeUsuarioAtribuicao(),
                    'nomeformatado' => $ret[0]->getStrSiglaUsuarioAtribuicao().' - '.$ret[0]->getStrNomeUsuarioAtribuicao()
                );
            }
            /** Chamada ao componente SEI para consultar a atribui��o */
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Encapsula o objeto ENtradaEnviarProcessoAPI para o metodo enviarProcesso
     * @param array $post
     * @return EntradaEnviarProcessoAPI
     */
    public function encapsulaEnviarProcessoEntradaEnviarProcessoAPI(array $post)
    {
        $entradaEnviarProcessoAPI = new EntradaEnviarProcessoAPI();
        if (isset($post['numeroProcesso'])) {
            $entradaEnviarProcessoAPI->setProtocoloProcedimento($post['numeroProcesso']);
        }
        if (isset($post['unidadesDestino'])) {
            $entradaEnviarProcessoAPI->setUnidadesDestino(explode(',', $post['unidadesDestino']));
        }
        if (isset($post['sinManterAbertoUnidade'])) {
            $entradaEnviarProcessoAPI->setSinManterAbertoUnidade($post['sinManterAbertoUnidade']);
        }
        if (isset($post['sinRemoverAnotacao'])) {
            $entradaEnviarProcessoAPI->setSinRemoverAnotacao($post['sinRemoverAnotacao']);
        }
        if (isset($post['sinEnviarEmailNotificacao'])) {
            $entradaEnviarProcessoAPI->setSinEnviarEmailNotificacao($post['sinEnviarEmailNotificacao']);
        } else {
            $entradaEnviarProcessoAPI->setSinEnviarEmailNotificacao('N');
        }
        if (isset($post['dataRetornoProgramado'])) {
            $entradaEnviarProcessoAPI->setDataRetornoProgramado($post['dataRetornoProgramado']);
        }
        if (isset($post['diasRetornoProgramado'])) {
            $entradaEnviarProcessoAPI->setDiasRetornoProgramado($post['diasRetornoProgramado']);
        }
        if (isset($post['sinDiasUteisRetornoProgramado'])) {
            $entradaEnviarProcessoAPI->setSinDiasUteisRetornoProgramado($post['sinDiasUteisRetornoProgramado']);
        }
        if (isset($post['sinReabrir'])) {
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
    protected function enviarProcessoControlado(EntradaEnviarProcessoAPI $entradaEnviarProcessoAPI)
    {
        try {
            $objSeiRN = new SeiRN();
            $objSeiRN->enviarProcesso($entradaEnviarProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo enviado com sucesso!');
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que verifica o acesso a um processo ou documento
     * @param ProtocoloDTO $protocoloDTOParam
     * - Se acesso liberado e chamar autentica��o for false, o usu�rio n�o pode de jeito nenhum visualizar o processo/documento
     * @return array
     */
    protected function verificaAcessoConectado(ProtocoloDTO $protocoloDTOParam)
    {
        try {
            $acessoLiberado = false;
            $chamarAutenticacao = false;
            $protocoloRN = new ProtocoloRN();
            $protocoloDTO = new ProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($protocoloDTOParam->getDblIdProtocolo());
            $protocoloDTO->retStrStaNivelAcessoGlobal();
            $protocoloDTO->retDblIdProtocolo();
            $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
            if (!$protocoloDTO) {
                throw new Exception('Processo n�o encontrado!');
            }
            if ($protocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO) {
                $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
                $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
                $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
                $objPesquisaProtocoloDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());

                $objProtocoloRN = new ProtocoloRN();
                $arrProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);
                if ($arrProtocoloDTO) {
                    $chamarAutenticacao = true;
                }
            } else {
                $acessoLiberado = true;
                $chamarAutenticacao = false;
            }

            return MdWsSeiRest::formataRetornoSucessoREST(
                null,
                array('acessoLiberado' => $acessoLiberado, 'chamarAutenticacao' => $chamarAutenticacao)
            );

        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Identifica o acesso do usu�rio em um processo
     * @param UsuarioDTO $usuarioDTO
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    public function apiIdentificacaoAcesso(UsuarioDTO $usuarioDTO, ProtocoloDTO $protocoloDTO)
    {
        try {
            $objInfraSip = new InfraSip(SessaoSEI::getInstance());
            $objInfraSip->autenticar(SessaoSEI::getInstance()->getNumIdOrgaoUsuario(), null, SessaoSEI::getInstance()->getStrSiglaUsuario(), $usuarioDTO->getStrSenha());
            AuditoriaSEI::getInstance()->auditar('usuario_validar_acesso');
            $ret = $this->verificaAcesso($protocoloDTO);
            if (!$ret['sucesso']) {
                return $ret;
            }
            $acessoAutorizado = false;
            if ($ret['data']['acessoLiberado'] || $ret['data']['chamarAutenticacao']) {
                $acessoAutorizado = true;
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, array('acessoAutorizado' => $acessoAutorizado));
        } catch (InfraException $e) {
            $infraValidacaoDTO = $e->getArrObjInfraValidacao()[0];
            $eAuth = new Exception($infraValidacaoDTO->getStrDescricao(), $e->getCode(), $e);
            return MdWsSeiRest::formataRetornoErroREST($eAuth);
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que monta a estrutura de pesquisa no solr
     * @param MdWsSeiPesquisaProtocoloSolrDTO $pesquisaProtocoloSolrDTO
     */
    private function montaConsultaSolr(MdWsSeiPesquisaProtocoloSolrDTO $pesquisaProtocoloSolrDTO)
    {
        $partialfields = '';

        if ($pesquisaProtocoloSolrDTO->isSetStrDescricao() && $pesquisaProtocoloSolrDTO->getStrDescricao() != null) {
            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }
            $partialfields .= '(' . InfraSolrUtil::formatarOperadores($pesquisaProtocoloSolrDTO->getStrDescricao(), 'desc') . ')';
        }

        if ($pesquisaProtocoloSolrDTO->isSetNumIdUnidadeGeradora() && $pesquisaProtocoloSolrDTO->getNumIdUnidadeGeradora() != null) {
            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }
            $partialfields .= '(id_uni_ger:' . $pesquisaProtocoloSolrDTO->getNumIdUnidadeGeradora() . ')';
        }

        if ($pesquisaProtocoloSolrDTO->isSetStrObservacao() && $pesquisaProtocoloSolrDTO->getStrObservacao() != null) {
            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }
            $partialfields .= '(' . InfraSolrUtil::formatarOperadores($pesquisaProtocoloSolrDTO->getStrObservacao(), 'obs_' . SessaoSEI::getInstance()->getNumIdUnidadeAtual()) . ')';
        }

        if ($pesquisaProtocoloSolrDTO->isSetDblIdProcedimento() && $pesquisaProtocoloSolrDTO->getDblIdProcedimento() != null) {
            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }

            $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
            $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
            $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
            $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($pesquisaProtocoloSolrDTO->getDblIdProcedimento());

            $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            /** Chama o componente SEI para retorno da lista de processos anexados */
            $arrIdProcessosAnexados = InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO), 'IdProtocolo2');

            if (count($arrIdProcessosAnexados) == 0) {
                $partialfields .= '(id_proc:' . $pesquisaProtocoloSolrDTO->getDblIdProcedimento() . ')';
            } else {

                $strProcessos = 'id_proc:' . $pesquisaProtocoloSolrDTO->getDblIdProcedimento();
                foreach ($arrIdProcessosAnexados as $dblIdProcessoAnexado) {
                    $strProcessos .= ' OR id_proc:' . $dblIdProcessoAnexado;
                }

                $partialfields .= '(' . $strProcessos . ')';
            }
        }

        if ($pesquisaProtocoloSolrDTO->isSetNumIdAssunto() && $pesquisaProtocoloSolrDTO->getNumIdAssunto() != null) {

            $objAssuntoProxyDTO = new AssuntoProxyDTO();
            $objAssuntoProxyDTO->retNumIdAssuntoProxy();
            $objAssuntoProxyDTO->setNumIdAssunto($pesquisaProtocoloSolrDTO->getNumIdAssunto());

            $objAssuntoProxyRN = new AssuntoProxyRN();
            /** Chama o componente SEI para o retorno da lista de assuntos */
            $arrObjAssuntoProxyDTO = $objAssuntoProxyRN->listar($objAssuntoProxyDTO);

            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }

            $arrAssuntos = array();
            foreach($arrObjAssuntoProxyDTO as $objAssuntoProxyDTO){
                array_push($arrAssuntos, 'id_assun:*;' . $objAssuntoProxyDTO->getNumIdAssuntoProxy() . ';*');
            }

            $partialfields .= '(' . implode(" OR ", $arrAssuntos) . ')';
        }

        if ($pesquisaProtocoloSolrDTO->isSetStrProtocoloPesquisa() && $pesquisaProtocoloSolrDTO->getStrProtocoloPesquisa() != null) {
            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }
            $partialfields .= '(prot_pesq:*' . InfraUtil::retirarFormatacao($pesquisaProtocoloSolrDTO->getStrProtocoloPesquisa(), false) . '*)';
        }

        if ($pesquisaProtocoloSolrDTO->isSetNumIdTipoProcedimento() && $pesquisaProtocoloSolrDTO->getNumIdTipoProcedimento() != null) {
            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }
            $partialfields .= '(id_tipo_proc:' . $pesquisaProtocoloSolrDTO->getNumIdTipoProcedimento() . ')';
        }

        if ($pesquisaProtocoloSolrDTO->isSetNumIdSerie() && $pesquisaProtocoloSolrDTO->getNumIdSerie() != null) {
            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }
            $partialfields .= '(id_serie:' . $pesquisaProtocoloSolrDTO->getNumIdSerie() . ')';
        }

        if ($pesquisaProtocoloSolrDTO->isSetStrNumero() && $pesquisaProtocoloSolrDTO->getStrNumero() != null) {
            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }
            $partialfields .= '(numero:*' . $pesquisaProtocoloSolrDTO->getStrNumero() . '*)';
        }

        $dtaInicio = null;
        $dtaFim = null;
        if($pesquisaProtocoloSolrDTO->isSetStrStaTipoData()){
            if ($pesquisaProtocoloSolrDTO->getStrStaTipoData() == '0') {
                $dtaInicio = $pesquisaProtocoloSolrDTO->getDtaInicio();
                $dtaFim = $pesquisaProtocoloSolrDTO->getDtaFim();
            } else if ($pesquisaProtocoloSolrDTO->getStrStaTipoData() == '30') {
                $dtaInicio = InfraData::calcularData(30, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ATRAS);
                $dtaFim = InfraData::getStrDataAtual();
            } else if ($pesquisaProtocoloSolrDTO->getStrStaTipoData() == '60') {
                $dtaInicio = InfraData::calcularData(60, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ATRAS);
                $dtaFim = InfraData::getStrDataAtual();
            }
        }

        if ($dtaInicio != null && $dtaFim != null) {
            $dia1 = substr($dtaInicio, 0, 2);
            $mes1 = substr($dtaInicio, 3, 2);
            $ano1 = substr($dtaInicio, 6, 4);

            $dia2 = substr($dtaFim, 0, 2);
            $mes2 = substr($dtaFim, 3, 2);
            $ano2 = substr($dtaFim, 6, 4);

            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }

            $partialfields .= 'dta_ger:[' . $ano1 . '-' . $mes1 . '-' . $dia1 . 'T00:00:00Z TO ' . $ano2 . '-' . $mes2 . '-' . $dia2 . 'T00:00:00Z]';
        }

        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->setBolExclusaoLogica(false);
        $objUnidadeDTO->retStrSinProtocolo();
        $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

        $objUnidadeRN = new UnidadeRN();
        /** Chama o componente SEI para retorno de dados da unidade atual */
        // TODO: 
        $objUnidadeDTOAtual = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

        if ($objUnidadeDTOAtual->getStrSinProtocolo() == 'N') {

            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }

            $partialfields .= '(tipo_aces_g:P OR id_uni_aces:*;' . SessaoSEI::getInstance()->getNumIdUnidadeAtual() . ';*)';
        }

        if($pesquisaProtocoloSolrDTO->isSetNumIdGrupoAcompanhamentoProcedimento() && $pesquisaProtocoloSolrDTO->getNumIdGrupoAcompanhamentoProcedimento()) {
            $protocoloRN = new ProtocoloRN();
            $mdWsSeiProtocoloDTO = new MdWsSeiProtocoloDTO();
            $mdWsSeiProtocoloDTO->setNumIdGrupoAcompanhamentoProcedimento($pesquisaProtocoloSolrDTO->getNumIdGrupoAcompanhamentoProcedimento());
            $mdWsSeiProtocoloDTO->retDblIdProtocolo();

            /** Chama o componente SEI para retorno de dados de consulta de acompanhamento de processos */
            $ret = $protocoloRN->listarRN0668($mdWsSeiProtocoloDTO);
            if(!$ret){
                return MdWsSeiRest::formataRetornoSucessoREST(null, array(), 0);
            }
            if ($partialfields != '') {
                $partialfields .= ' AND ';
            }
            $arrIdProcessosAcompanhamento = array();
            /** @var ProtocoloDTO $protocoloDTO */
            foreach($ret as $protocoloDTO){
                $arrIdProcessosAcompanhamento[] = 'id_proc:' . $protocoloDTO->getDblIdProtocolo();
            }
            $partialfields .= '(' . implode(' OR ', $arrIdProcessosAcompanhamento) . ')';
        }

        $parametros = new stdClass();
        if($pesquisaProtocoloSolrDTO->isSetStrPalavrasChave()){
            $parametros->q = InfraSolrUtil::formatarOperadores($pesquisaProtocoloSolrDTO->getStrPalavrasChave());
        }
        if ($pesquisaProtocoloSolrDTO->isSetStrPalavrasChave() && is_numeric($pesquisaProtocoloSolrDTO->getStrPalavrasChave()) && !$pesquisaProtocoloSolrDTO->isSetStrProtocoloPesquisa()){
            $parametros->q = '(sta_prot:G OR sta_prot:R) AND (prot_pesq:*'. InfraSolrUtil::formatarCaracteresEspeciais(InfraUtil::retirarFormatacao($parametros->q,false)) .'* OR prot_proc:*'.$pesquisaProtocoloSolrDTO->getStrPalavrasChave().'*)';
        }

        if ($parametros->q != '' && $partialfields != '') {
            $parametros->q = '(' . $parametros->q . ') AND ' . $partialfields;
        } else if ($partialfields != '') {
            $parametros->q = $partialfields;
        }

        $parametros->q = utf8_encode($parametros->q);
        
        $start = 0;
        $limit = 10;
        if($pesquisaProtocoloSolrDTO->getNumPaginaAtual()){
            $start = $pesquisaProtocoloSolrDTO->getNumPaginaAtual();
        }
        if($pesquisaProtocoloSolrDTO->getNumMaxRegistrosRetorno()){
            $limit = $pesquisaProtocoloSolrDTO->getNumMaxRegistrosRetorno();
        }
        $parametros->start = $start;
        $parametros->rows = $limit;
        $parametros->sort = 'dta_ger desc, id_prot desc';

        return $parametros;
    }

    /**
     * M�todo que consulta os processos no Solar
     * @param MdWsSeiPesquisaProtocoloSolrDTO $pesquisaProtocoloSolrDTO
     * @return array
     */
    protected function pesquisarProcessosSolarConectado(MdWsSeiPesquisaProtocoloSolrDTO $pesquisaProtocoloSolrDTO)
    {
        try {
            $protocoloRN = new ProtocoloRN();
            $relProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            $unidadeRN = new UnidadeRN();
            $arrIdBusca = array();

            $arrProtocoloDTOPesquisado = array();
            if($pesquisaProtocoloSolrDTO->isSetStrbuscaRapida()) {
                $protocoloDTOPesquisa = new ProtocoloDTO();
                $protocoloDTOPesquisa->setStrProtocoloFormatadoPesquisa($pesquisaProtocoloSolrDTO->getStrbuscaRapida());
                /** Consulta componente SEI para pesquisa r�pida de processo */
                $arrProtocoloDTOPesquisado = $protocoloRN->pesquisarProtocoloFormatado($protocoloDTOPesquisa);

            }
            if (count($arrProtocoloDTOPesquisado)==1){
                $bolAcesso = true;
                $protocoloDTO = $arrProtocoloDTOPesquisado[0];

                $idProcesso = null;
                $idDocumento = null;
                if ($protocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO || $protocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_RESTRITO) {

                    $pesquisaProtocoloDTO = new PesquisaProtocoloDTO();

                    if ($protocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_PROCEDIMENTO) {
                        $pesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
                    } else if ($protocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO) {
                        $pesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS_GERADOS);
                    } else if ($protocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) {
                        $pesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS_RECEBIDOS);
                    } else {
                        $pesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_TODOS);
                    }

                    $pesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
                    $pesquisaProtocoloDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());

                    /** Acessa componente SEI para verifica��o dos tipos de processo ou documento */
                    $arrProtocoloDTO = $protocoloRN->pesquisarRN0967($pesquisaProtocoloDTO);
                    
                    if($arrProtocoloDTO[0]->getStrStaProtocolo() == ProtocoloRN::$TPP_PROCEDIMENTOS){
                        $idProcesso = $protocoloDTO->getDblIdProtocolo();
                    }else{
                        $idDocumento = $protocoloDTO->getDblIdProtocolo();

                        $relProtocoloProtocoloConsulta = new RelProtocoloProtocoloDTO();
                        $relProtocoloProtocoloConsulta->retDblIdProtocolo1();
                        $relProtocoloProtocoloConsulta->setDblIdProtocolo2($protocoloDTO->getDblIdProtocolo());

                        /** Acessa o componente SEI para retornar o processo pai do documento */
                        $protocoloDTOConsulta = $relProtocoloProtocoloRN->consultarRN0841($relProtocoloProtocoloConsulta);
                        $idProcesso = $protocoloDTOConsulta->getDblIdProtocolo1();
                    }

                    if (count($arrProtocoloDTO) == 0) {
                        if ($arrProtocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO) {
                            $bolAcesso = false;
                        } else {
                            $unidadeDTO = new UnidadeDTO();
                            $unidadeDTO->retStrSinProtocolo();
                            $unidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

                            /** Acessa o componente SEI para retornar dados da unidade logada */
                            $unidadeDTO = $unidadeRN->consultarRN0125($unidadeDTO);


                            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
                            $numTipoPesquisaRestrito = $objInfraParametro->getValor('SEI_EXIBIR_ARVORE_RESTRITO_SEM_ACESSO', false);

                            if ($unidadeDTO->getStrSinProtocolo() == 'N' && $numTipoPesquisaRestrito != '1') {
                                throw new InfraException('Unidade atual n�o possui acesso ao ' . ($protocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_PROCEDIMENTO ? 'processo' : 'documento') . ' restrito ' . $protocoloDTO->getStrProtocoloFormatado() . '.');
                                $bolAcesso = false;
                            }
                        }
                    } else {
                        $protocoloDTO = $arrProtocoloDTO[0];

                        if ($protocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO &&
                            $protocoloDTO->getStrSinPublicado() == 'S'
                        ) {
                            //$strLinkVisualizarSigilosoPublicado = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&acao_origem=' . $_GET['acao'] . '&id_documento=' . $objProtocoloDTO->getDblIdProtocolo());
                            $bolAcesso = false;
                        }
                    }
                }
                /** Descontinuando busca r�pida, caso seja reativada tem que ser ajustado o m�todo montaRetornoPesquisaSolr
                if($bolAcesso){
                    return MdWsSeiRest::formataRetornoSucessoREST(
                        null,
                        // Chamada para consulta da indexa��o do Solr
                        $this->montaRetornoPesquisaSolr(
                            array(
                                0 => array(
                                    'idProcesso' => $idProcesso,
                                    'idDocumento' => $idDocumento
                                )
                            )
                        ),
                        1
                    );
                }
                **/
            }else if(count($arrProtocoloDTOPesquisado) > 1){
                $pesquisaProtocoloSolrDTO->setStrProtocoloPesquisa($pesquisaProtocoloSolrDTO->getStrbuscaRapida());
            }

            /** Chama o m�todo para montagem da estrutura da pesquisa no solr */
            $parametros = $this->montaConsultaSolr($pesquisaProtocoloSolrDTO);
            /** Executa consulta no SOLR para retorno de metadados indexados de processos e documentos da busca */
            $urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr', 'Servidor') . '/' . ConfiguracaoSEI::getInstance()->getValor('Solr', 'CoreProtocolos') . '/select?' . http_build_query($parametros) . '&hl=true&hl.snippets=2&hl.fl=content&hl.fragsize=100&hl.maxAnalyzedChars=1048576&hl.alternateField=content&hl.maxAlternateFieldLength=100&fl=id,id_proc,id_doc,id_tipo_proc,id_serie,id_anexo,id_uni_ger,prot_doc,prot_proc,numero,id_usu_ger,dta_ger';

            try {
                $resultados = file_get_contents($urlBusca, false);
            } catch (Exception $e) {
                throw new InfraException('Erro realizando pesquisa no Solar.', $e, urldecode($urlBusca), false);
            }

            if ($resultados == '') {
                throw new InfraException('Nenhum retorno encontrado no resultado da pesquisa do Solar, verificar indexa��o.');
            }

            $xml = simplexml_load_string($resultados);
            $arrRet = $xml->xpath('/response/result/@numFound');
            $registros = $xml->xpath('/response/result/doc');
            $total = (int) array_shift($arrRet);
            $numRegistros = sizeof($registros);
            $arrDadosSolr = array();
            
            for ($i = 0; $i < $numRegistros; $i++) {
                $arrDadosSolr[$i] = array(
                    'id' => InfraSolrUtil::obterTag($registros[$i], 'id', 'str'),
                    'id_proc' => InfraSolrUtil::obterTag($registros[$i], 'id_proc', 'long'),
                    'id_doc' => InfraSolrUtil::obterTag($registros[$i], 'id_doc', 'long'),
                    'id_anexo' => InfraSolrUtil::obterTag($registros[$i], 'id_anexo', 'int'),
                    'id_uni_ger' => InfraSolrUtil::obterTag($registros[$i], 'id_uni_ger', 'int'),
                    'id_usu_ger' => InfraSolrUtil::obterTag($registros[$i], 'id_usu_ger', 'int'),
                    'id_tipo_proc' => InfraSolrUtil::obterTag($registros[$i], 'id_tipo_proc', 'int'),
                    'id_serie' => InfraSolrUtil::obterTag($registros[$i], 'id_serie', 'int'),
                    'numero' => InfraSolrUtil::obterTag($registros[$i], 'numero', 'str'),
                    'prot_doc' => InfraSolrUtil::obterTag($registros[$i], 'prot_doc', 'str'),
                    'prot_proc' => InfraSolrUtil::obterTag($registros[$i], 'prot_proc', 'str'),
                    'dta_ger' => InfraSolrUtil::obterTag($registros[$i], 'dta_ger', 'date'),
                );
            }
            

            return MdWsSeiRest::formataRetornoSucessoREST(null, $this->montaRetornoPesquisaSolr($arrDadosSolr), $total);
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que formata o retorno da busca de protocolo no solr
     * @param $arrDadosSolr
     * @return array
     */
    private function montaRetornoPesquisaSolr($arrDadosSolr){
        $result = array();
        $protocoloRN = new ProtocoloRN();
        $documentoRN = new DocumentoRN();
        $procedimentoRN = new ProcedimentoRN();
        $usuarioRN = new UsuarioRN();
        $anexoRN = new AnexoRN();
        $arrDocHtml = array(
            DocumentoRN::$TD_EDITOR_EDOC,
            DocumentoRN::$TD_FORMULARIO_AUTOMATICO,
            DocumentoRN::$TD_FORMULARIO_GERADO,
            DocumentoRN::$TD_EDITOR_INTERNO
        );

        $arrIdTipoProcedimento = array();
        $arrIdUnidadeGeradora = array();
        $arrIdSerie = array();
        $arrIdUsuarioGerador = array();

        $arrTipoProcedimentoDTO = array();
        $arrserieDTO = array();
        $arrUnidadeDTOGeradora = array();
        $arrUsuarioDTOGerador = array();

        foreach($arrDadosSolr as $dadosBusca) {
            if(!is_null($dadosBusca['id_serie'])){
                $arrIdSerie[$dadosBusca['id_serie']] = $dadosBusca['id_serie'];
            }
            $arrIdTipoProcedimento[$dadosBusca['id_tipo_proc']] = $dadosBusca['id_tipo_proc'];
            $arrIdUsuarioGerador[$dadosBusca['id_usu_ger']] = $dadosBusca['id_usu_ger'];
            $arrIdUnidadeGeradora[$dadosBusca['id_uni_ger']] = $dadosBusca['id_uni_ger'];
        }

        if (count($arrIdTipoProcedimento)) {
            $tipoProcedimentoDTO = new TipoProcedimentoDTO();
            $tipoProcedimentoDTO->setBolExclusaoLogica(false);
            $tipoProcedimentoDTO->retNumIdTipoProcedimento();
            $tipoProcedimentoDTO->retStrNome();
            $tipoProcedimentoDTO->setNumIdTipoProcedimento(array_keys($arrIdTipoProcedimento), InfraDTO::$OPER_IN);

            $tipoProcedimentoRN = new TipoProcedimentoRN();
            /** Acessa o componente SEI para busca dos tipos de procedimento */
            $arrTipoProcedimentoDTO = InfraArray::indexarArrInfraDTO($tipoProcedimentoRN->listarRN0244($tipoProcedimentoDTO), 'IdTipoProcedimento');
        }

        if (count($arrIdSerie)) {
            $serieDTO = new SerieDTO();
            $serieDTO->setBolExclusaoLogica(false);
            $serieDTO->retNumIdSerie();
            $serieDTO->retStrNome();
            $serieDTO->setNumIdSerie(array_keys($arrIdSerie), InfraDTO::$OPER_IN);

            $serieRN = new SerieRN();
            /** Acessa o componente SEI para busca das series **/
            $arrSerieDTO = InfraArray::indexarArrInfraDTO($serieRN->listarRN0646($serieDTO), 'IdSerie');
        }

        if (count($arrIdUnidadeGeradora)) {
            $unidadeDTO = new UnidadeDTO();
            $unidadeDTO->setBolExclusaoLogica(false);
            $unidadeDTO->retNumIdUnidade();
            $unidadeDTO->retStrSigla();
            $unidadeDTO->retStrDescricao();
            $unidadeDTO->setNumIdUnidade(array_keys($arrIdUnidadeGeradora), InfraDTO::$OPER_IN);

            $unidadeRN = new UnidadeRN();
            /** Acessa o componente SEI para busca das unidades geradoras */
            $arrUnidadeDTOGeradora = InfraArray::indexarArrInfraDTO($unidadeRN->listarRN0127($unidadeDTO), 'IdUnidade');
        }

        if (count($arrIdUsuarioGerador)) {
            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->setBolExclusaoLogica(false);
            $usuarioDTO->retNumIdUsuario();
            $usuarioDTO->retStrSigla();
            $usuarioDTO->retStrNome();
            $usuarioDTO->setNumIdUsuario(array_keys($arrIdUsuarioGerador), InfraDTO::$OPER_IN);

            $usuarioRN = new UsuarioRN();
            /** Acessa o componente SEI para busca dos usu�rios geradores */
            $arrUsuarioDTOGerador = InfraArray::indexarArrInfraDTO($usuarioRN->listarRN0490($usuarioDTO), 'IdUsuario');
        }

        foreach($arrDadosSolr as $dadosBusca) {
            $arrDadosResultadoBusca = array(
                'idProcedimento' => $dadosBusca['id_proc'],
                'idTipoProcedimento' => $dadosBusca['id_tipo_proc'],
                'nomeTipoProcedimento' => ($arrTipoProcedimentoDTO[$dadosBusca['id_tipo_proc']] ? $arrTipoProcedimentoDTO[$dadosBusca['id_tipo_proc']]->getStrNome() : '[tipo de processo n�o encontrado]'),
                'siglaUnidadeGeradora' => ($arrUnidadeDTOGeradora[$dadosBusca['id_uni_ger']] ? $arrUnidadeDTOGeradora[$dadosBusca['id_uni_ger']]->getStrSigla() : '[unidade n�o encontrada]'),
                'idUnidadeGeradora' => ($arrUnidadeDTOGeradora[$dadosBusca['id_uni_ger']] ? $arrUnidadeDTOGeradora[$dadosBusca['id_uni_ger']]->getStrDescricao() : '[unidade n�o encontrada]'),
                'protocoloFormatadoProcedimento' => $dadosBusca['prot_proc'],
                'idUsuarioGerador' => $dadosBusca['id_usu_ger'],
                'nomeUsuarioGerador' => ($arrUsuarioDTOGerador[$dadosBusca['id_usu_ger']] ? $arrUsuarioDTOGerador[$dadosBusca['id_usu_ger']]->getStrNome() : '[usu�rio n�o encontrado]'),
                'siglaUsuarioGerador' => ($arrUsuarioDTOGerador[$dadosBusca['id_usu_ger']] ? $arrUsuarioDTOGerador[$dadosBusca['id_usu_ger']]->getStrSigla() : '[usu�rio n�o encontrado]'),
                'dataGeracao' => preg_replace("/(\\d{4})-(\\d{2})-(\\d{2})(.*)/", "$3/$2/$1", $dadosBusca['dta_ger']),
                'documento' => array()
            );

            if(!is_null($dadosBusca['prot_doc'])){
                $documentoDTO = new DocumentoDTO();
                $documentoDTO->setDblIdDocumento($dadosBusca['id_doc']);
                $documentoDTO->retStrStaDocumento();
                /** Chama componente SEI para retorno de dados do documento */
                $documentoDTO = $documentoRN->consultarRN0005($documentoDTO);
                $arrDadosAnexo = null;
                $staDocumento = null;

                if($documentoDTO){
                    $staDocumento = $documentoDTO->getStrStaDocumento();
                }

                if ($dadosBusca['id_anexo']) {
                    $anexoDTOConsulta = new AnexoDTO();
                    $anexoDTOConsulta->retStrNome();
                    $anexoDTOConsulta->retNumTamanho();
                    $anexoDTOConsulta->setNumIdAnexo($dadosBusca['id_anexo']);
                    $anexoDTOConsulta->setStrSinAtivo('S');
                    $anexoDTOConsulta->setNumMaxRegistrosRetorno(1);
                    /** Chama o componente SEI para recuperar o anexo no documento */
                    $resultAnexo = $anexoRN->listarRN0218($anexoDTOConsulta);
                    if ($resultAnexo) {
                        /** @var AnexoDTO $anexoDTO */
                        $anexoDTO = $resultAnexo[0];
                        $mimetype = $anexoDTO->getStrNome();
                        $mimetype = substr($mimetype, strrpos($mimetype, '.') + 1);
                        $nomeAnexo = $anexoDTO->getStrNome();
                        $tamanhoAnexo = $anexoDTO->getNumTamanho();
                        $arrDadosAnexo = array(
                            'nome' => $nomeAnexo,
                            'mimetype' => $mimetype,
                            'tamanho' => $tamanhoAnexo
                        );
                    }
                }
                $arrDadosResultadoBusca['documento'] = array(
                    'idDocumento' => $dadosBusca['id_doc'],
                    'idSerieDocumento' => $dadosBusca['id_serie'],
                    'nomeSerieDocumento' => ($arrSerieDTO[$dadosBusca['id_serie']] ? $arrSerieDTO[$dadosBusca['id_serie']]->getStrNome() : '[tipo de documento n�o encontrado]'),
                    'protocoloFormatadoDocumento' => $dadosBusca['prot_doc'],
                    'numeroDocumento' => $dadosBusca['numero'],
                    'staDocumento' => ($staDocumento ? $staDocumento : ($dadosBusca['id_anexo'] ? DocumentoRN::$TD_EXTERNO : null)),
                    'dtaGeracao' => preg_replace("/(\\d{4})-(\\d{2})-(\\d{2})(.*)/", "$3/$2/$1", $dadosBusca['dta_ger']),
                    'dadosAnexo' => $arrDadosAnexo
                );
            }

            $result[] = $arrDadosResultadoBusca;
        }

        return $result;
    }

    /**
     * Metodo que recebe o procedimento na atual unidade
     * Criado por Adriano Cesar - MPOG
     * @param Objeto DTO contendo a informa��o do procedimento
     * @return sucesso ou erro
     */
    protected function receberProcedimentoControlado(MdWsSeiProcedimentoDTO $dto)
    {
        try {
            // Se o id do procedimento n�o foi passado, gera exce��o
            if (!$dto->getNumIdProcedimento()) {
                throw new InfraException('E obrigat�rio informar o n�mero identificador do procedimento!');
            }

            $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
            $objPesquisaPendenciaDTO->setDblIdProtocolo($dto->getNumIdProcedimento());
            $objPesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objPesquisaPendenciaDTO->setStrSinMontandoArvore('S');
            $objPesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
      
            $objAtividadeRN = new AtividadeRN();
            $arrObjProcedimentoDTO = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);

            $numRegistrosProcedimento = count($arrObjProcedimentoDTO);
      
      
            $objProcedimentoRN = new ProcedimentoRN();

            if ($numRegistrosProcedimento == 1){
         
                $objProcedimentoDTOPar = $arrObjProcedimentoDTO[0];

                //Rotina do core do sistema, que recebe procedimento
                $objProcedimentoRN->receber($objProcedimentoDTOPar);

                return MdWsSeiRest::formataRetornoSucessoREST('Processo recebido com sucesso!');
            }
 
            return MdWsSeiRest::formataRetornoSucessoREST('Processo n�o dispon�vel para recebimento na unidade atual.');


        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Retorna a lista de sugestao de assuntos
     * @param RelTipoProcedimentoAssuntoDTO $relTipoProcedimentoAssuntoDTOParam
     * @return array
     */
    protected function sugestaoAssuntoConectado(RelTipoProcedimentoAssuntoDTO $relTipoProcedimentoAssuntoDTOParam)
    {
        try {
            $result = array();
            $relTipoProcedimentoAssuntoDTOParam->retNumIdAssunto();
            $relTipoProcedimentoAssuntoDTOParam->retStrDescricaoAssunto();
            $relTipoProcedimentoAssuntoDTOParam->retStrCodigoEstruturadoAssunto();
            $relTipoProcedimentoAssuntoDTOParam->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

            if($relTipoProcedimentoAssuntoDTOParam->isSetStrDescricaoAssunto() && $relTipoProcedimentoAssuntoDTOParam->getStrDescricaoAssunto() != ''){
                $relTipoProcedimentoAssuntoDTOParam->setStrDescricaoAssunto(
                    '%'.$relTipoProcedimentoAssuntoDTOParam->getStrDescricaoAssunto().'%',
                    InfraDTO::$OPER_LIKE
                );
            }

            $relTipoProcedimentoAssuntoRN = new RelTipoProcedimentoAssuntoRN();
            /** Consulta no componente SEI a lista de assuntos **/
            $ret = $relTipoProcedimentoAssuntoRN->listarRN0192($relTipoProcedimentoAssuntoDTOParam);

            /** @var RelTipoProcedimentoAssuntoDTO $relTipoProcedimentoAssuntoDTO */
            foreach ($ret as $relTipoProcedimentoAssuntoDTO) {
                $result[] = array(
                    /** Chamando componente do SEI para formata�ao de nome do assunto **/
                    'codigoestruturadoformatado' => AssuntoINT::formatarCodigoDescricaoRI0568(
                        $relTipoProcedimentoAssuntoDTO->getStrCodigoEstruturadoAssunto(),
                        $relTipoProcedimentoAssuntoDTO->getStrDescricaoAssunto()
                    ),
                    'descricao' => $relTipoProcedimentoAssuntoDTO->getStrDescricaoAssunto(),
                    'codigoestruturado' => $relTipoProcedimentoAssuntoDTO->getStrCodigoEstruturadoAssunto(),
                    'id' => $relTipoProcedimentoAssuntoDTO->getNumIdAssunto()
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $relTipoProcedimentoAssuntoDTOParam->getNumTotalRegistros());
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Verifica se o processo est� aberto na unidade logada
     * @param $idProcedimento
     * @return bool
     */
    protected function processoAbertoUnidadeAtualConectado($idProcedimento){
        $objAtividadeRN = new AtividadeRN();
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDistinct(true);
        $objAtividadeDTO->retStrSiglaUnidade();
        $objAtividadeDTO->retStrDescricaoUnidade();

        $objAtividadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);
        $objAtividadeDTO->setDblIdProtocolo($idProcedimento);
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

        return empty($arrObjAtividadeDTO) ? false : true;
    }

}