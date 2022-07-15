# Orientação para Instalação WSSEI e Aplicativo Móvel


### Pré-requisitos

- SEI versão 4.0.x ou superior instalada ou SUPER.GOV.BR versão 4.0.x ou superior instalada;
- Usuário de acesso ao banco de dados do SEI e SIP com permissões para criar novas estruturas no banco de dados

*Atenção!:* Para instalação do módulo compatível com SEI 3.1, necessário utilizar a versão 1.0.4 do WSSEI


### Primeira Etapa: Instalar o módulo de integração no servidor de aplicação do SEI

1. Baixar a última versão do pacote de instalação do sistema (arquivo `mod-wssei-[VERSÃO].zip`) localizado na página de [Releases do projeto MOD-WSSEI](https://github.com/spbgovbr/mod-wssei/releases), seção **Assets**.

2. Fazer backup dos diretórios "sei", "sip" do servidor web;

3. Descompactar o pacote de instalação `mod-wssei-[VERSÃO].zip`;

4. Copiar os diretórios descompactados "sei", "sip" para os servidores, sobrescrevendo os arquivos existentes;
   
5. Adicionar ao arquivo de configuração do sistema (ConfiguracaoSEI.php), na chave Módulos, a referência para a pasta do módulo copiado no passo anterior. Utilizando a chave de identificação MdWsSeiRest.

   O sistema procura pelo módulo a partir da pasta de módulos do SEI.

   Exemplo:
   ```
   'SEI' => ARRAY(
                ( ...)
                'Modulos' => array('MdWsSeiRest' => 'mod-wssei/')
        ),
   ```
6. Copiar o arquivo ConfiguracaoMdWSSEI.exemplo.php (<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI>/sei/config/mod-wssei/ConfiguracaoMdWSSEI.exemplo.php) para ConfiguracaoMdWSSEI.php (<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI>/sei/config/mod-wssei/ConfiguracaoMdWSSEI.php)
7. Alterar o arquivo de configuração do módulo (<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI>/sei/config/mod-wssei/ConfiguracaoMdWSSEI.php), no Array de configurações, a chave com as configurações abaixo (serviço de envio de notificações):

   Exemplo:
   ```bash
   public function getArrConfiguracoes(){
       return array(
           'WSSEI' => array(
               'UrlServicoNotificacao' => '{URL do serviço de notificação}',
               'IdApp' => '{ID do app registrado no serviço de notificação}',
               'ChaveAutorizacao' => '{Chave de autorização do serviço de notificação}',
               'TokenSecret' => '{chave unica com pelo menos 32 chars. Pode usar o comando uuidgen para gerar}'
           ),
   ```

   **Importante:**
   * para ativar as notificações, será necessário informar o endereço/credenciais do serviço push de mensagens
   * pode usar o serviço push disponibilizado pelo Ministério da Economia. Para tanto, abra
chamado na Central de Atendimento do  PEN([https://portaldeservicos.planejamento.gov.br/citsmart/login/login.load](https://www.google.com/url?q=https://portaldeservicos.planejamento.gov.br/citsmart/login/login.load&sa=D&source=hangouts&ust=1576333188310000&usg=AFQjCNFo4ErHNsg7p65YJEJiKLIjdfMM5Q)). **A categoria do chamado é PEN - WSSEI - INSTALAÇÃO.**
   * verifique se o nó do SEI responsável por executar os agendamentos tenha acesso a URL/Porta acima
   * a partir da versão 1.0.4 do módulo, a variável "TokenSecret" é obrigatória. Trata-se de uma chave para criptografar e descriptografar o token. Além de sua presença obrigatória, ela precisa ter no mínimo 32 chars de tamanho. Uma dica é usar o seguinte comando linux para gerar a chave: uuidgen. Basta rodar o comando e copiar o resultado que é um uuid para a variável

8. Executar o script para atualização dos recursos do mod-wssei no SIP em linha de comando:

   ```bash
   php -c /etc/php.ini  [DIRETORIO_RAIZ_INSTALAÇÃO]/sip/scripts/mod-wssei/sip_atualizar_versao_modulo_wssei.php
   ```

9. Executar o script para atualização dos recursos do mod-wssei no SEI em linha de comando:

   ```bash
   php -c /etc/php.ini  [DIRETORIO_RAIZ_INSTALAÇÃO]/sei/scripts/mod-wssei/sei_atualizar_versao_modulo_wssei.php
   ``` 

10. Verificar se o módulo foi carregado por meio do menu Infra/Módulos do SEI

11. Verificar se o banco de dados foi corretamente atualizado por meio do menu Infra/Parâmetros do SEI (chave VERSAO_MODULO_WSSEI)

12. Verificar se o agendamento para as notificações foi corretamente criado (tela Infra/Agendamentos):
   ```bash
   MdWsSeiAgendamentoRN :: notificacaoAtividades
   ```

13. Verificar se o QR Code foi criado na parte inferior do menu lateral esquerdo do SEI. Esse código contém os dados de acesso ao ambiente do órgão


### Segunda Etapa: Instalar o aplicativo no telefone celular

1. No telefone celular, acessar a loja Google Play ou App Store e realizar a instalação do aplicativo do SEI


### Terceira Etapa: Realizar a leitura do QR Code

1. No telefone celular, abrir o aplicativo do SEI

2. Acessar a opção &quot;Trocar órgão&quot; e, em seguida, a opção &quot;Ler Código&quot;

3. Fazer a leitura do QR Code no SEI _web_ do seu órgão com a câmera do telefone celular

4. Informar o usuário e a senha do SEI, e iniciar o uso do aplicativo