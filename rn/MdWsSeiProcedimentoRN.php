<?php
require_once dirname(__FILE__).'/../../../SEI.php';

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
                throw new InfraException('Protocolo não informado.');
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
            
            //RETORNOS ESPERADOS NOS PARÂMETROS DE SAÍDA
            $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
            $objTipoProcedimentoDTO->retStrNome();
            $objTipoProcedimentoDTO->retStrSinInterno();

            //MÉTODO QUE RETORNA A BUSCA DOS TIPOS DE PROCESSO APLICANDO AS REGRAS DE RESTRIÇÃO POR ÓRGÃO, UNIDADE E OUVIDORIA
            $objTipoProcedimentoRN = new TipoProcedimentoRN();
            $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarTiposUnidade($objTipoProcedimentoDTO); //Lista os tipos de processo
            
            $arrayObjs = array();
            //FILTRA NOME, ID e INTERNO
            if($arrObjTipoProcedimentoDTO){
                foreach ($arrObjTipoProcedimentoDTO as $aux) {
                    if(
                        ($aux->getNumIdTipoProcedimento() == $id     || !$id)    
                            &&
                        (($nome && strpos($aux->getStrNome(), $nome) !== false) || !$nome)
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
                     

                    $arrayRetorno[$i] = array(
                                            "id"                => $aux["id"],
                                            "nome"              => $aux["nome"],
                                            "permiteSigiloso"   => $permiteSigiloso
                                        );
                }
            }

            if($start) $arrayRetorno = array_slice($arrayRetorno, ($start-1));   
            if($limit) $arrayRetorno = array_slice($arrayRetorno, 0,($limit));   
            
            $total = 0;
            $total = count($arrayRetorno);
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno, $total);    
        } catch (Exception $e) {
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

            //DTO QUE REPRESENTA A RELAÇÃO ENTRE OS ASSUNTOS E OS TIPOS DE PROCESSO
            $relTipoProcedimentoAssuntoDTO = new RelTipoProcedimentoAssuntoDTO();
            $relTipoProcedimentoAssuntoDTO->setNumIdTipoProcedimento($id); // FILTRO PELO TIPO DE PROCESSO
            $relTipoProcedimentoAssuntoDTO->retNumIdAssunto(); // ID DO ASSUNTO QUE DEVE SE RETORNADO
            $relTipoProcedimentoAssuntoDTO->retStrCodigoEstruturadoAssunto(); // CÓDIGO DO ASSUNTO QUE DEVE SE RETORNADO
            $relTipoProcedimentoAssuntoDTO->retStrDescricaoAssunto(); // DESCRIÇÃO DO ASSUNTO QUE DEVE SER RETORNADA

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
            
            //CONSULTA QUE LISTA TODOS OS NÍVES DE ACESSOS PERMITIDOS PARA OS TIPO DE PROCESSO
            $nivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
            $nivelAcessoPermitidoDTO->setNumIdTipoProcedimento($id); // FILTRO PELO TIPO DE PROCESSO
            $nivelAcessoPermitidoDTO->retStrStaNivelAcesso(); // ID DO NÍVEL DE ACESSO - ProtocoloRN::$NA_PUBLICO, ProtocoloRN::$NA_RESTRITO ou ProtocoloRN::$NA_SIGILOSO

            // A CONSULTA RETORNARÁ OS NÍVEL DE ACESSO PERMITIDOS PARA O TIPO DE PROCESSO ESPECIFICADO NO DTO. AQUELES QUE NÃO FOREM RETORNADOS NESSA
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
            
            $arrayRetorno["nivelAcessoSugerido"]    = $tipoProcedimentoDTO ? $tipoProcedimentoDTO->getStrStaNivelAcessoSugestao() : false;
            $arrayRetorno["hipoteseLegalSugerida"]  = $tipoProcedimentoDTO ? $tipoProcedimentoDTO->getNumIdHipoteseLegalSugestao() : false;
            $arrayRetorno["grauSigiloSugerido"]     = $tipoProcedimentoDTO ? $tipoProcedimentoDTO->getStrStaGrauSigiloSugestao() : false;
            
            
            //CONSULTA NO PARÂMETRO QUE INFORMA SE A HIPÓTESE LEGAL É OBRIGATÓRIO PARA UM TIPO DE PROCESSO
            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $obrigatoriedadeHipoteseLegal = $objInfraParametro->getValor('SEI_HABILITAR_HIPOTESE_LEGAL');

            //CONSULTA NO PARÂMETRO QUE INFORMA SE UM GRAU DE SIGILO É OBRIGATÓRIO PARA UM TIPO DE PROCESSO
            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $obrigatoriedadeGrauSigilo = $objInfraParametro->getValor('SEI_HABILITAR_GRAU_SIGILO');
            
            $arrayRetorno["obrigatoriedadeHipoteseLegal"]   = $obrigatoriedadeHipoteseLegal;
            $arrayRetorno["obrigatoriedadeGrauSigilo"]      = $obrigatoriedadeGrauSigilo;
                                            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno);    
        } catch (Exception $e) {
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
            
            if($filter)  $assuntoDTO->adicionarCriterio(array('CodigoEstruturado','Descricao'),array(InfraDTO::$OPER_LIKE,InfraDTO::$OPER_LIKE),array('%'.utf8_decode($filter).'%','%'.utf8_decode($filter).'%'),InfraDTO::$OPER_LOGICO_OR);
//                $objInfraAgendamentoTarefaDTO->adicionarCriterio(array('SinAtivo','IdInfraAgendamentoTarefa'),array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),array('S',$strValorItemSelecionado),InfraDTO::$OPER_LOGICO_OR);
//                $assuntoDTO->setStrCodigoEstruturado('%'.$filter.'%',InfraDTO::$OPER_LIKE);

            if($limit)
                $assuntoDTO->setNumMaxRegistrosRetorno($limit);
            if($start)
                $assuntoDTO->setNumPaginaAtual($start);

            $assuntoDTO->retNumIdAssunto();
            $assuntoDTO->retStrCodigoEstruturado();
            $assuntoDTO->retStrDescricao();

            // REALIZA A CHAMADA DA DE ASSUNTOS
            $assuntoRN = new AssuntoRN();
            $arrAssuntoDTO = $assuntoRN->listarRN0247($assuntoDTO);          
            
            $arrayRetorno = array();
            if($arrAssuntoDTO){
                foreach ($arrAssuntoDTO as $obj) {
                    $arrayRetorno[]   = array(
                                            "id"        => $obj->getNumIdAssunto(),
                                            "codigo"    => $obj->getStrCodigoEstruturado(),
                                            "descricao" => $obj->getStrDescricao(),
                                      );
                }
            }
            
//            $arrayRetorno = array();
//            if($start) $arrayRetorno = array_slice($arrayRetorno, ($start-1));   
//            if($limit) $arrayRetorno = array_slice($arrayRetorno, 0,($limit));   
            
            $total = 0;
            $total = count($arrayRetorno);
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayRetorno, $total);    
        } catch (Exception $e) {
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
           
            //Id do processo enviado por parâmetro
            $processo = $id;

            // Recupera os dados do processo inseridos na tabela de protocolo 
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->setDblIdProtocolo($processo);
            $objProtocoloDTO->retStrDescricao(); // Recupera a especificação do processo
            $objProtocoloDTO->retStrStaNivelAcessoLocal(); // Recupera o nível de acesso do processo 
            $objProtocoloDTO->retNumIdHipoteseLegal(); // Recupera o id da hipótese legal
            $objProtocoloDTO->retStrStaGrauSigilo(); // Recupera o grau de sigilo

            $protocoloRN = new ProtocoloRN();
            $objProtocoloDTO = $protocoloRN->consultarRN0186($objProtocoloDTO);

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
            
            //Recupera as observações do process
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
                    $objParticipanteDTO->setNumIdParticipante($interessado['id']);
                    $objParticipanteDTO->retTodos();
                    
                    $participanteRn = new ParticipanteRN();
                    $obj = $participanteRn->consultarRN1008($objParticipanteDTO);
                    $obj->setNumSequencia($i);
                    
                    $arrayInteressados[] = $obj;
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
            $objProcedimentoDTO->setObjProtocoloDTO($objProtocoloDTO);           
            $objProcedimentoDTO->setStrSinGerarPendencia('S');
                        
            
            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoRN->gerarRN0156($objProcedimentoDTO);
            
            
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
            
            return MdWsSeiRest::formataRetornoSucessoREST(null);
        
        } catch (InfraException $e) {
            //die($e->getStrDescricao());
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
    
    
      
     /**
     * Alterar Procedimento
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    protected function alterarProcedimentoConectado(MdWsSeiProcedimentoDTO $procedimentoDTO)
    {
        try {
            
                $processo           = $procedimentoDTO->getNumIdProcedimento();
                $tipoProcesso       = $procedimentoDTO->getNumIdTipoProcedimento();
                $especificacao      = $procedimentoDTO->getStrEspecificacao();
                $arrAssuntos        = $procedimentoDTO->getArrObjAssunto();
                $arrInteressados    = $procedimentoDTO->getArrObjInteressado();
                $observacoes        = $procedimentoDTO->getStrObservacao();
                $nivelAcesso        = $procedimentoDTO->getNumNivelAcesso();
                $hipoteseLegal      = $procedimentoDTO->getNumIdHipoteseLegal();
                $grauSigilo         = $procedimentoDTO->getStrStaGrauSigilo();
                
                // PARÂMETROS DE ENTRADA
//                $tipoProcesso = 100000349;
//                $especificacao = 'descrição alterada';
//                $arrAssuntos = array(array('id' => 2));
//                $arrInteressados = array(array('id' => 100000003));
//                $observacoes = "observação de teste";
//                $nivelAcesso = 1;
//                $hipoteseLegal = 2;
//                $grauSigilo = '';

                // PREENCHE OS ASSUNTOS 
                if($arrAssuntos){
                    foreach ($arrAssuntos as $k => $assunto) {
                        $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
                        $objRelProtocoloAssuntoDTO->setNumIdAssunto($assunto['id']);
                        $objRelProtocoloAssuntoDTO->setNumSequencia($k);
                        $arrayAssuntos[] = $objRelProtocoloAssuntoDTO;
                    }
                }
                // PREENCHE OS INTERESSADOS 
                if($arrInteressados){
                    foreach ($arrInteressados as $k => $interessado) {
                        $objParticipanteDTO = new ParticipanteDTO();
                        $objParticipanteDTO->setNumIdContato($interessado['id']);
                        $objParticipanteDTO->setStrStaParticipacao('I');
                        $objParticipanteDTO->setNumSequencia($k);
                        $arrayParticipantes[] = $objParticipanteDTO;
                    }
                }
                
                // EDITA AS OBSERVAÇÕES 
                $objObservacaoDTO = new ObservacaoDTO();
                $objObservacaoDTO->setStrDescricao($observacoes);

                // EDITA OS DADOS DO PROCESSO 
                $objProtocoloDTO = new ProtocoloDTO();
                $objProtocoloDTO->setDblIdProtocolo($processo);
                $objProtocoloDTO->setStrDescricao($especificacao);
                $objProtocoloDTO->setStrStaNivelAcessoLocal($nivelAcesso);
                $objProtocoloDTO->setNumIdHipoteseLegal($hipoteseLegal);
                $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrayAssuntos);
                $objProtocoloDTO->setArrObjParticipanteDTO($arrayParticipantes);
                $objProtocoloDTO->setArrObjObservacaoDTO(array($objObservacaoDTO));
                $objProtocoloDTO->setStrStaGrauSigilo($grauSigilo);

                $objProcedimentoDTO = new ProcedimentoDTO();
                $objProcedimentoDTO->setDblIdProcedimento($processo);
                $objProcedimentoDTO->setNumIdTipoProcedimento($tipoProcesso);
                $objProcedimentoDTO->setObjProtocoloDTO($objProtocoloDTO);
                $objProcedimentoDTO->setStrSinGerarPendencia('S');

                // REALIZA A ALTERAÇÃO DOS DADOS DO PROCESSO
                $objProcedimentoRN = new ProcedimentoRN();
                $objProcedimentoRN->alterarRN0202($objProcedimentoDTO);
    
            return MdWsSeiRest::formataRetornoSucessoREST(null);
        
        } catch (InfraException $e) {
            //die($e->getStrDescricao());
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }
    
    /**
     * Método que lista o sobrestamento de um processo
     * @param AtividadeDTO $atividadeDTOParam
     * @return array
     */
    protected function listarSobrestamentoProcessoConectado(AtividadeDTO $atividadeDTOParam)
    {
        try {
            if (!$atividadeDTOParam->isSetDblIdProtocolo()) {
                throw new InfraException('Protocolo não informado.');
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
                throw new Exception('Processo não informado!');
            }
            if(!$relProtocoloProtocoloDTO->isSetStrMotivo()){
                throw new Exception('Informe o motivo!');
            }
            $procedimentoRN = new ProcedimentoRN();
            $procedimentoRN->sobrestarRN1014(array($relProtocoloProtocoloDTO));

            return MdWsSeiRest::formataRetornoSucessoREST('Processo sobrestado com sucesso');
        } catch (Exception $e) {
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
                throw new InfraException('Procedimento não informado.');
            }
            $seiRN = new SeiRN();
            $entradaRemoverSobrestamentoProcessoAPI = new EntradaRemoverSobrestamentoProcessoAPI();
            $entradaRemoverSobrestamentoProcessoAPI->setIdProcedimento($procedimentoDTOParam->getDblIdProcedimento());

            $seiRN->removerSobrestamentoProcesso($entradaRemoverSobrestamentoProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Sobrestar cancelado com sucesso.');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que retorna os procedimentos com acompanhamento do usuário
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

            if (is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
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
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que retorna os procedimentos com acompanhamento da unidade
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
            if (is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
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
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que pesquisa todos o procedimentos em todas as unidades
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

            if (!is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            } else {
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
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
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que retorna os procedimentos com acompanhamento com filtro opcional de grupo de acompanhamento e protocolo
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

            if (!is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            } else {
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
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

            if (!is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            } else {
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
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
            } else {
                throw new InfraException('O tipo de busca deve ser (R)ecebidos ou (G)erados');
            }
            $ret = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $pesquisaPendenciaDTO->getNumTotalRegistros());
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }

    }

    /**
     * Metodo que monta o retorno da listagem do processo com base no retorno da consulta
     * @param array $ret
     * @param null $usuarioAtribuicaoAtividade
     * @return array
     */
    private function montaRetornoListagemProcessos(array $ret, $usuarioAtribuicaoAtividade = null)
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

            $arrAtividades = $atividadeRN->listarRN0036($atividadeDTOConsulta);

            if ($arrAtividades) {
                /** @var AtividadeDTO $atividadeDTO */
                $atividadeDTO = $arrAtividades[0];
                $documentoNovo = $atividadeDTO->getNumIdTarefa() == 1 ? 'S' : 'N';
                $usuarioAtribuido = $atividadeDTO->getStrNomeUsuarioAtribuicao();
                $tipoVisualizacao = $atividadeDTO->getNumTipoVisualizacao() == 0 ? 'S' : 'N';
                if ($atividadeDTO->getNumIdUsuarioVisualizacao() == $usuarioAtribuicaoAtividade) {
                    $usuarioVisualizacao = 'S';
                }
            }
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
                $arrProcedimentoDTO = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
                if ($arrProcedimentoDTO) {
                    $procedimentoDTO = $arrProcedimentoDTO[0];
                    $arrAtividadePendenciaDTO = $procedimentoDTO->getArrObjAtividadeDTO();
                }
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
                $retornoProgramadoDTOConsulta->retDblIdProtocoloAtividadeEnvio();
                $retornoProgramadoDTOConsulta->retStrSiglaUnidadeOrigemAtividadeEnvio();
                $retornoProgramadoDTOConsulta->retStrSiglaUnidadeAtividadeEnvio();
                $retornoProgramadoDTOConsulta->retDtaProgramada();
                $retornoProgramadoDTOConsulta->setNumIdUnidadeAtividadeEnvio(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $retornoProgramadoDTOConsulta->setDblIdProtocoloAtividadeEnvio(array_unique(InfraArray::converterArrInfraDTO($arrAtividadePendenciaDTO, 'IdProtocolo')), InfraDTO::$OPER_IN);
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
                            'unidade' => $retornoProgramadoDTO->getStrSiglaUnidadeOrigemAtividadeEnvio()
                        );
                    }
                }
            }
            $documentoRN = new DocumentoRN();
            $documentoDTOConsulta = new DocumentoDTO();
            $documentoDTOConsulta->setDblIdProcedimento($protocoloDTO->getDblIdProtocolo());
            $documentoDTOConsulta->retDblIdDocumento();
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

            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

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

            //ordena descendente pois no envio de processo que já existe na unidade e está atribuído ficará com mais de um andamento em aberto
            //desta forma os andamentos com usuário nulo (envios do processo) serão listados depois
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
            $result['info'] = 'Processo não possui andamentos abertos.';
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
                    $result['info'] = 'Processo aberto com o usuário:';
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
                            $sigla .= ' (atribuído a ' . $atividadeDTO->getStrNomeUsuarioAtribuicao() . ')';
                        }
                        $result['lista'][] = array(
                            'sigla' => $sigla
                        );
                    }
                } else {
                    $result['info'] = 'Processo aberto com os usuários:';
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
     * Metodo que retorna as ciencias nos processos
     * @param ProtocoloDTO $protocoloDTOParam
     * @return array
     */
    protected function listarCienciaProcessoConectado(ProtocoloDTO $protocoloDTOParam)
    {
        try {
            if (!$protocoloDTOParam->isSetDblIdProtocolo()) {
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
            foreach ($ret as $atividadeDTO) {
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

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
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

            return MdWsSeiRest::formataRetornoSucessoREST('Ciência processo realizado com sucesso!');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que conclui o procedimento/processo
     * @param EntradaConcluirProcessoAPI $entradaConcluirProcessoAPI
     * @info ele recebe o número do ProtocoloProcedimentoFormatadoPesquisa da tabela protocolo
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

            return MdWsSeiRest::formataRetornoSucessoREST('Processo concluído com sucesso!');
        } catch (Exception $e) {
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

            return MdWsSeiRest::formataRetornoSucessoREST('Processo atribuído com sucesso!');
        } catch (Exception $e) {
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
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que verifica o acesso a um processo ou documento
     * @param ProtocoloDTO $protocoloDTOParam
     * - Se acesso liberado e chamar autenticação for false, o usuário não pode de jeito nenhum visualizar o processo/documento
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
                throw new Exception('Processo não encontrado!');
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
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Identifica o acesso do usuário em um processo
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
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Método que consulta os processos no Solar
     * @param MdWsSeiPesquisaProtocoloSolrDTO $pesquisaProtocoloSolrDTO
     * @return array
     */
    protected function pesquisarProcessosSolarConectado(MdWsSeiPesquisaProtocoloSolrDTO $pesquisaProtocoloSolrDTO)
    {
        try {
            $partialfields = '';

            if ($pesquisaProtocoloSolrDTO->isSetStrDescricao() && $pesquisaProtocoloSolrDTO->getStrDescricao() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }
                $partialfields .= '(' . SolrUtil::formatarOperadores($pesquisaProtocoloSolrDTO->getStrDescricao(), 'desc') . ')';
            }

            if ($pesquisaProtocoloSolrDTO->isSetStrObservacao() && $pesquisaProtocoloSolrDTO->getStrObservacao() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }
                $partialfields .= '(' . SolrUtil::formatarOperadores($pesquisaProtocoloSolrDTO->getStrObservacao(), 'obs_' . SessaoSEI::getInstance()->getNumIdUnidadeAtual()) . ')';
            }

            //o- verificar lógica do solar
            if ($pesquisaProtocoloSolrDTO->isSetDblIdProcedimento() && $pesquisaProtocoloSolrDTO->getDblIdProcedimento() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }

                $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
                $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
                $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
                $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($pesquisaProtocoloSolrDTO->getDblIdProcedimento());

                $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
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
            $objUnidadeDTOAtual = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

            if ($objUnidadeDTOAtual->getStrSinProtocolo() == 'N') {

                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }

                $partialfields .= '(tipo_aces:P OR id_uni_aces:*;' . SessaoSEI::getInstance()->getNumIdUnidadeAtual() . ';*)';
            }

            if($pesquisaProtocoloSolrDTO->isSetNumIdGrupoAcompanhamentoProcedimento() && $pesquisaProtocoloSolrDTO->getNumIdGrupoAcompanhamentoProcedimento()) {
                $protocoloRN = new ProtocoloRN();
                $mdWsSeiProtocoloDTO = new MdWsSeiProtocoloDTO();
                $mdWsSeiProtocoloDTO->setNumIdGrupoAcompanhamentoProcedimento($pesquisaProtocoloSolrDTO->getNumIdGrupoAcompanhamentoProcedimento());
                $mdWsSeiProtocoloDTO->retDblIdProtocolo();

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
                $parametros->q = SolrUtil::formatarOperadores($pesquisaProtocoloSolrDTO->getStrPalavrasChave());
            }

            if ($parametros->q != '' && $partialfields != '') {
                $parametros->q = '(' . $parametros->q . ') AND ' . $partialfields;
            } else if ($partialfields != '') {
                $parametros->q = $partialfields;
            }

            $parametros->q = utf8_encode($parametros->q);
            $start = 0;
            $limit = 100;
            if($pesquisaProtocoloSolrDTO->getNumPaginaAtual()){
                $start = $pesquisaProtocoloSolrDTO->getNumPaginaAtual();
            }
            if($pesquisaProtocoloSolrDTO->getNumMaxRegistrosRetorno()){
                $limit = $pesquisaProtocoloSolrDTO->getNumMaxRegistrosRetorno();
            }
            $parametros->start = $start;
            $parametros->rows = $limit;
            $parametros->sort = 'dta_ger desc, id_prot desc';

            $urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr', 'Servidor') . '/' . ConfiguracaoSEI::getInstance()->getValor('Solr', 'CoreProtocolos') . '/select?' . http_build_query($parametros) . '&hl=true&hl.snippets=2&hl.fl=content&hl.fragsize=100&hl.maxAnalyzedChars=1048576&hl.alternateField=content&hl.maxAlternateFieldLength=100&fl=id,id_proc,id_doc,id_tipo_proc,id_serie,id_anexo,id_uni_ger,prot_doc,prot_proc,numero,id_usu_ger,dta_ger';

            try {
                $resultados = file_get_contents($urlBusca, false);
            } catch (Exception $e) {
                throw new InfraException('Erro realizando pesquisa no Solar.', $e, urldecode($urlBusca), false);
            }

            if ($resultados == '') {
                throw new InfraException('Nenhum retorno encontrado no resultado da pesquisa do Solar, verificar indexação.');
            }

            $xml = simplexml_load_string($resultados);
            $arrRet = $xml->xpath('/response/result/@numFound');
            $total = array_shift($arrRet)->__toString();
            $arrIdProcessos = array();
            $registros = $xml->xpath('/response/result/doc');
            $numRegistros = sizeof($registros);

            $result = array();
            for ($i = 0; $i < $numRegistros; $i++) {
                $arrIdProcessos[] = SolrUtil::obterTag($registros[$i], 'id_proc', 'long');
            }

            if($arrIdProcessos){
                $protocoloRN = new ProtocoloRN();
                $protocoloDTO = new MdWsSeiProtocoloDTO();
                $protocoloDTO->setDblIdProtocolo($arrIdProcessos, InfraDTO::$OPER_IN);
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
                $arrProtocoloDTO = $protocoloRN->listarRN0668($protocoloDTO);
                $result = $this->montaRetornoListagemProcessos($arrProtocoloDTO, null);
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $total);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }


}