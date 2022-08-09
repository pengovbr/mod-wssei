

<?
require_once dirname(__FILE__) . '/../../../SEI.php';

class MdWsSeiAgendamentoRN extends InfraRN
{

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    protected function notificacaoAtividadesControlado()
    {
        try {
            ini_set('max_execution_time', '0');
            ini_set('memory_limit', '1024M');
            InfraDebug::getInstance()->setBolLigado(true);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);
            InfraDebug::getInstance()->limpar();

            $numSeg = InfraUtil::verificarTempoProcessamento();
            $arrErroNotificacao = [];
            $contSucessos = 0;

            InfraDebug::getInstance()->gravar('REALIZANDO NOTIFICA��O DE ATIVIDADES');

            $notificacaoAtividadeRN = new MdWsSeiNotificacaoAtividadeRN();
            /** Realiza a chamada para retorno de atividades a notificar */
            $arrAtividadeDTO = $notificacaoAtividadeRN->listarNotificacoesParaAgendamento();
            if($arrAtividadeDTO){
                InfraDebug::getInstance()->gravar(count($arrAtividadeDTO).' ATIVIDADES A SEREM NOTIFICADAS.');
                $UrlServicoNotificacao = ConfiguracaoSEI::getInstance()->getValor('WSSEI', 'UrlServicoNotificacao', false);
                $IdApp = ConfiguracaoSEI::getInstance()->getValor('WSSEI', 'IdApp', false);
                $ChaveAutorizacao = ConfiguracaoSEI::getInstance()->getValor('WSSEI', 'ChaveAutorizacao', false);
                if(!$UrlServicoNotificacao || !$IdApp || !$ChaveAutorizacao){
                    $arrParamNaoSetados = array();
                    if(!$UrlServicoNotificacao){
                        $arrParamNaoSetados[] = 'WSSEI:UrlServicoNotificacao';
                    }
                    if(!$IdApp){
                        $arrParamNaoSetados[] = 'WSSEI:IdApp';
                    }
                    if(!$ChaveAutorizacao){
                        $arrParamNaoSetados[] = 'WSSEI:ChaveAutorizacao';
                    }
                    InfraDebug::getInstance()->gravar('AS NOTIFICA��ES N�O SER�O ENVIADAS PORQUE OS PARAMETROS A SEGUIR: '.implode(', ', $arrParamNaoSetados).'.', InfraLog::$ERRO);
                }else{
                    $titulo = 'Atribui��o de Processo';
                    /** @var MdWsSeiAtividadeDTO $atividadeDTO */
                    foreach($arrAtividadeDTO as $atividadeDTO) {
                        SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SEI, null, null, $atividadeDTO->getNumIdUnidade());
                        try{
                            $notificacaoDTO = new MdWsSeiNotificacaoDTO();
                            $notificacaoDTO->setStrTitulo('Atribui��o de Processo');
                            $mensagem = 'Ol� '.$atividadeDTO->getStrNomeUsuarioAtribuicao().', ';
                            $mensagem .= 'O processo '.$atividadeDTO->getStrProtocoloFormatadoProtocolo().' foi atribu�do a voc�. ';

                            $notificacaoDTO->setStrMensagem($mensagem);
                            $notificacaoDTO->setStrResumo($mensagem);
                            /** Gera identificador �nico do usu�rio para notifica��o **/
                            $identificador = MdWsSeiRest::geraIdentificadorUsuario(
                                $atividadeDTO->getStrSiglaUsuarioAtribuicao(),
                                $atividadeDTO->getStrSiglaOrgaoUsuarioAtribuicao()
                            );
                            $notificacaoDTO->setStrIdentificadorUsuario($identificador);
                            $notificacaoDTO->setBolNotificar(true);
                            $notificacaoDTO->setStrUrlServicoNotificacao($UrlServicoNotificacao);
                            $notificacaoDTO->setNumIdApp($IdApp);
                            $notificacaoDTO->setStrChaveAutorizacao($ChaveAutorizacao);
                            $notificacaoDTO->setArrData(array('idProcesso' => $atividadeDTO->getDblIdProtocolo()));

                            $notificacaoRN = new MdWsSeiNotificacaoRN();
                            $mensagemErro = null;
                            /** Realiza a chamada da classe de notifica��o **/
                            $notificacaoRN->notificar($notificacaoDTO);
                            $notificacaoAtividadeDTO = new MdWsSeiNotificacaoAtividadeDTO();
                            $notificacaoAtividadeDTO->setNumIdAtividade($atividadeDTO->getNumIdAtividade());
                            $notificacaoAtividadeDTO->setDthNotificacao(InfraData::getStrDataHoraAtual());
                            $notificacaoAtividadeDTO->setStrTitulo($titulo);
                            $notificacaoAtividadeDTO->setStrMensagem($mensagem);
                            /** Realiza o Cadastro da notifica��o para controle de atividades notificadas **/
                            $notificacaoAtividadeRN->cadastrar($notificacaoAtividadeDTO);
                            $contSucessos = $contSucessos+1;
                        }catch (InfraException $e) {
                            $arrErroNotificacao[$e->getStrDescricao()] = isset($arrErroNotificacao[$e->getStrDescricao()])
                                ? $arrErroNotificacao[$e->getStrDescricao()]+1
                                : 1;
                        }catch (Exception $e) {
                            $arrErroNotificacao[$e->getMessage()] = isset($arrErroNotificacao[$e->getMessage()])
                                ? $arrErroNotificacao[$e->getMessage()]+1
                                : 1;
                        }
                    }

                    InfraDebug::getInstance()->gravar("$contSucessos NOTIFICA��ES ENVIADAS COM SUCESSO.", InfraLog::$INFORMACAO);

                    if(!empty($arrErroNotificacao)){
                        foreach ($arrErroNotificacao as $msgErro => $total) {
                            InfraDebug::getInstance()->gravar("$total NOTIFICA��ES N�O ENVIADAS COM A MENSAGEM DE ERRO: $msgErro", InfraLog::$ERRO);
                        }
                    }
                }
            }else{
                InfraDebug::getInstance()->gravar('NENHUMA ATIVIDADE AGUARDANDO NOTIFICA��O.', InfraLog::$INFORMACAO);
            }
            $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
            InfraDebug::getInstance()->gravar('TEMPO TOTAL DE EXECUCAO: ' . $numSeg . ' s');
            InfraDebug::getInstance()->gravar('FIM');

            LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug(), InfraLog::$INFORMACAO);

        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);

            throw new InfraException('Erro ao executar atualiza��o de um ou mais estados de agendamentos.', $e);
        }
    }

}

?>

