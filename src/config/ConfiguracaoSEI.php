<?

class ConfiguracaoSEI extends InfraConfiguracao
{

  private static $instance = null;

  public static function getInstance()
    {
    if (ConfiguracaoSEI::$instance == null) {
        ConfiguracaoSEI::$instance = new ConfiguracaoSEI();
    }
      return ConfiguracaoSEI::$instance;
  }

  public function getArrConfiguracoes()
    {
      return array(
          'SEI' => array(
              'URL' => getenv('HOST_URL') . '/sei',
              'Producao' => false,
              'DigitosDocumento' => 7,
              'PermitirAcessoLocalPdf' => '',
              'NumLoginUsuarioExternoSemCaptcha' => 3,
              'TamSenhaUsuarioExterno' => 8,
              'DebugWebServices' => 0,
              'RepositorioArquivos' => '/var/sei/arquivos',
              'Modulos' => array(
                  'MdWsSeiRest' => 'wssei'
              ),
          ),

          'SessaoSEI' => array(
              'SiglaOrgaoSistema' => 'ABC',
              'SiglaSistema' => 'SEI',
              'PaginaLogin' => getenv('HOST_URL') . '/sip/login.php',
              'SipWsdl' => getenv('HOST_URL') . '/sip/controlador_ws.php?servico=sip',
              'ChaveAcesso' => getenv('SEI_CHAVE_ACESSO'), //ATENÇAO: gerar uma nova chave para o SEI após a instação (ver documento de instação)
              'https' => false,
          ),

          'PaginaSEI' => array(
              'NomeSistema' => 'SUPER',
              'NomeSistemaComplemento' => SEI_VERSAO,
              'LogoMenu' => '',
              'OrgaoTopoJanela' => 'S',
          ),

          'BancoSEI'  => array(
              'Servidor' => getenv('DATABASE_HOST'),
              'Porta' => getenv('DATABASE_PORT'),
              'Banco' => getenv('SEI_DATABASE_NAME'),
              'Usuario' => getenv('SEI_DATABASE_USER'),
              'Senha' => getenv('SEI_DATABASE_PASSWORD'),
              'Tipo' => getenv('DATABASE_TYPE'), //MySql, SqlServer ou Oracle
              'PesquisaCaseInsensitive' => false,
          ),

          //            'BancoAuditoriaSEI'  => array(
          //                'Servidor' => getenv('DATABASE_HOST'),
          //                'Porta' => getenv('DATABASE_PORT'),
          //                'Banco' => getenv('SEI_DATABASE_NAME'),
          //                'Usuario' => getenv('SEI_DATABASE_USER'),
          //                'Senha' => getenv('SEI_DATABASE_PASSWORD'),
          //                'Tipo' => getenv('DATABASE_TYPE'), //MySql, SqlServer ou Oracle
          //                'PesquisaCaseInsensitive' => false,
          //            ),

          'CacheSEI' => array(
              'Servidor' => 'memcached',
              'Porta' => '11211',
              'Timeout' => 1,
              'Tempo' => 3600,
          ),

          'Federacao' => array(
              'Habilitado' => false,
              'NumSegundosAcaoRemota' => 10,  //Tempo máximo que um link de ação do SEI Federação pode ser executado.
              'NumSegundosSincronizacao' => 300,  //Diferença máxima em segundos entre os horários das instalações.
              'NumDiasTentativasReplicacao' => 3,  //Informa por quanto tempo o sistema tentará replicar sinalizações em processos para outras instalações do SEI Federação.
              'ReplicarAcessosOnline' => true,  //Sinaliza se as concessões de acessos para órgãos de outras instalações devem ser replicadas no mesmo instante. Se o valor for false ou se ocorrer um erro então as replicações serão tratadas pelo agendamento de replicações.
              'NumMaxProtocolosConsulta' => 100,  //Número máximo de protocolos do processo que serão retornados quando outra instituição consultar pelo SEI Federa-ção (acima deste valor será realizada paginação).
              'NumMaxAndamentosConsulta' => 100,  //Número máximo de andamentos do processo que serão retornados quando outra instituição consultar pelo SEI Federação (acima deste valor será realizada paginação).
          ),

          'XSS' => array(
              'NivelVerificacao' => 'A',
              'ProtocolosExcecoes' => null,
              'NivelBasico' => array(
                  'ValoresNaoPermitidos' => null,
              ),
              'NivelAvancado' => array(
                  'TagsPermitidas' => null,
                  'TagsAtributosPermitidos' => null,
              ),
          ),

          'Limites' => array(
              //Nível 1 é afeto a Operações em geral
              'Nivel1TempoSeg' => 60,  //Esta chave define o Tempo máximo em segundos para execução do script.
              'Nivel1MemoriaMb' => 256,  //Esta chave define a Quantidade máxima de memória em Megabytes que o script pode utilizar.
              //Nível 2 é afeto a Download de documentos, Estatísticas, Geração de PDF, Migração de Unidade, Indexação Individual e Substituição de contatos
              'Nivel2TempoSeg' => 600,  //Esta chave define o Tempo máximo em segundos para execução do script.
              'Nivel2MemoriaMb' => 2048,  //Esta chave define a Quantidade máxima de memória em Megabytes que o script pode utilizar.
              //Nível 3 é afeto a Scripts, Agendamentos, Indexação Massiva, Critérios de Controle Interno e Web Services
              'Nivel3TempoSeg' => 0,  //Esta chave define o Tempo máximo em segundos para execução do script. Este nível aceita o valor '0' para indicar sem limite de tempo.
              'Nivel3MemoriaMb' => 4096,  //Esta chave define a Quantidade máxima de memória em Megabytes que o script pode utilizar. Este nível aceita o valor '-1' para indicar sem limite de memória.
          ),

          'RH' => array(
              'CargoFuncao' => '',  //Endereço para o serviço de recuperação de Cargos/Funções para assinatura de documentos (opcional).
          ),

          'Solr' => array(
              'Servidor' => 'http://solr:8983/solr',
              'CoreProtocolos' => 'sei-protocolos',
              'CoreBasesConhecimento' => 'sei-bases-conhecimento',
              'CorePublicacoes' => 'sei-publicacoes',
              'TempoCommitProtocolos' => 5,
              'TempoCommitBasesConhecimento' => 60,
              'TempoCommitPublicacoes' => 60,
          ),

          'JODConverter' => array(
              'Servidor' => 'http://jod/converter/service'
          ),

          'InfraMail' => array(
              'Tipo' => '2', //1 = sendmail (neste caso não é necessário configurar os atributos abaixo), 2 = SMTP
              'Servidor' => 'smtp',
              'Porta' => '1025',
              'Codificacao' => '8bit', //8bit, 7bit, binary, base64, quoted-printable
              'Autenticar' => false, //se true então informar Usuario e Senha
              'Usuario' => '',
              'Senha' => '',
              'Seguranca' => '', //TLS, SSL ou vazio
              'MaxDestinatarios' => 25, //numero maximo de destinatarios por mensagem
              'MaxTamAnexosMb' => 15, //tamanho maximo dos anexos em Mb por mensagem
              'Protegido' => '', //campo usado em desenvolvimento, se tiver um email preenchido entao todos os emails enviados terao o destinatario ignorado e substituído por este valor (evita envio incorreto de email)
              /*  Abaixo chave opcional desativada com exemplo de preenchimento
                'Dominios' => array(    // Opcional. Permite especificar o conjunto de atributos acima individualmente para cada domínio de conta remetente. Se não existir um domínio mapeado então utilizará os atributos gerais da chave InfraMail.
                    'abc.jus.br' => array(
                        'Tipo' => '2',
                        'Servidor' => '10.1.3.12',
                        'Porta' => '25',
                        'Codificacao' => '8bit',
                        'Autenticar' => false,
                        'Usuario' => '',
                        'Senha' => '',
                        'Seguranca' => 'TLS',
                        'MaxDestinatarios' => 25,
                        'MaxTamAnexosMb' => 15,
                        'Protegido' => '',
                        ),
                    ),
                  */
          ),
      );
  }
}
