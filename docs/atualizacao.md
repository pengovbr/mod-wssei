# Orientação para Atualização WSSEI e Aplicativo Móvel


### Pré-requisitos

- SEI versão 4.0.x ou superior instalada ou SUPER.GOV.BR versão 4.0.x ou superior instalada;
- Usuário de acesso ao banco de dados do SEI e SIP com permissões para criar novas estruturas no banco de dados

*Atenção!:* Para instalação do módulo compatível com SEI 3.1, necessário utilizar a versão 1.0.4 do WSSEI


### Primeira Etapa: Instalar o módulo de integração no servidor de aplicação do SEI

1. Baixar a última versão do pacote de instalação do sistema (arquivo `mod-wssei-[VERSÃO].zip`) localizado na página de [Releases do projeto MOD-WSSEI](https://github.com/spbgovbr/mod-wssei/releases), seção **Assets**.

2. Fazer backup dos diretórios "sei", "sip" e "infra" do servidor web;

3. Descompactar o pacote de instalação `mod-wssei-[VERSÃO].zip`;

4. Copiar os diretórios descompactados "sei", "sip" e "infra" para os servidores, sobrescrevendo os arquivos existentes;

5. Executar o script para atualização dos recursos do mod-wssei no SIP em linha de comando:

   ```bash
   php -c /etc/php.ini  [DIRETORIO_RAIZ_INSTALAÇÃO]/sip/scripts/mod-wssei/sip_atualizar_versao_modulo_wssei.php
   ```

6. Executar o script para atualização dos recursos do mod-wssei no SEI em linha de comando:

   ```bash
   php -c /etc/php.ini  [DIRETORIO_RAIZ_INSTALAÇÃO]/sei/scripts/mod-wssei/sei_atualizar_versao_modulo_wssei.php
   ``` 

7.  Verificar se o módulo foi carregado por meio do menu Infra/Módulos do SEI

8.  Verificar se o banco de dados foi corretamente atualizado por meio do menu Infra/Parâmetros do SEI (chave VERSAO_MODULO_WSSEI)

9.  Verificar se o agendamento para as notificações foi corretamente criado (tela Infra/Agendamentos):
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