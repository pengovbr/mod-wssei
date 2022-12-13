

<?
require_once DIR_SEI_WEB . '/SEI.php';

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

            InfraDebug::getInstance()->gravar('REALIZANDO NOTIFICAÇÃO DE ATIVIDADES');

            $notificacaoAtividadeRN = new MdWsSeiNotificacaoAtividadeRN();
            /** Realiza a chamada para retorno de atividades a notificar */
            $arrAtividadeDTO = $notificacaoAtividadeRN->listarNotificacoesParaAgendamento();
            if($arrAtividadeDTO){
                InfraDebug::getInstance()->gravar(count($arrAtividadeDTO).' ATIVIDADES A SEREM NOTIFICADAS.');
                $UrlServicoNotificacao = ConfiguracaoMdWSSEI::getInstance()->getValor('WSSEI', 'UrlServicoNotificacao', false);
                $IdApp = ConfiguracaoMdWSSEI::getInstance()->getValor('WSSEI', 'IdApp', false);
                $ChaveAutorizacao = ConfiguracaoMdWSSEI::getInstance()->getValor('WSSEI', 'ChaveAutorizacao', false);
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
                    InfraDebug::getInstance()->gravar('AS NOTIFICAÇÕES NÃO SERÃO ENVIADAS PORQUE OS PARAMETROS A SEGUIR: '.implode(', ', $arrParamNaoSetados).'.', InfraLog::$ERRO);
                }else{
                    $titulo = 'Atribuição de Processo';
                    /** @var MdWsSeiAtividadeDTO $atividadeDTO */
                    foreach($arrAtividadeDTO as $atividadeDTO) {
                        SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_SEI, null, null, $atividadeDTO->getNumIdUnidade());
                        try{
                            $notificacaoDTO = new MdWsSeiNotificacaoDTO();
                            $notificacaoDTO->setStrTitulo('Atribuição de Processo');
                            $mensagem = 'Olá '.$atividadeDTO->getStrNomeUsuarioAtribuicao().', ';
                            $mensagem .= 'O processo '.$atividadeDTO->getStrProtocoloFormatadoProtocolo().' foi atribuído a você. ';

                            $notificacaoDTO->setStrMensagem($mensagem);
                            $notificacaoDTO->setStrResumo($mensagem);
                            /** Gera identificador único do usuário para notificação **/
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
                            /** Realiza a chamada da classe de notificação **/
                            $notificacaoRN->notificar($notificacaoDTO);
                            $notificacaoAtividadeDTO = new MdWsSeiNotificacaoAtividadeDTO();
                            $notificacaoAtividadeDTO->setNumIdAtividade($atividadeDTO->getNumIdAtividade());
                            $notificacaoAtividadeDTO->setDthNotificacao(InfraData::getStrDataHoraAtual());
                            $notificacaoAtividadeDTO->setStrTitulo($titulo);
                            $notificacaoAtividadeDTO->setStrMensagem($mensagem);
                            /** Realiza o Cadastro da notificação para controle de atividades notificadas **/
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

                    InfraDebug::getInstance()->gravar("$contSucessos NOTIFICAÇÕES ENVIADAS COM SUCESSO.", InfraLog::$INFORMACAO);

                    if(!empty($arrErroNotificacao)){
                        foreach ($arrErroNotificacao as $msgErro => $total) {
                            InfraDebug::getInstance()->gravar("$total NOTIFICAÇÕES NÃO ENVIADAS COM A MENSAGEM DE ERRO: $msgErro", InfraLog::$ERRO);
                        }
                    }
                }
            }else{
                InfraDebug::getInstance()->gravar('NENHUMA ATIVIDADE AGUARDANDO NOTIFICAÇÃO.', InfraLog::$INFORMACAO);
            }
            $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
            InfraDebug::getInstance()->gravar('TEMPO TOTAL DE EXECUCAO: ' . $numSeg . ' s');
            InfraDebug::getInstance()->gravar('FIM');

            LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug(), InfraLog::$INFORMACAO);

        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);

            throw new InfraException('Erro ao executar atualização de um ou mais estados de agendamentos.', $e);
        }
    }

}

?>

