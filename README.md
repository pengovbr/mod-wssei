**Aplicativo SEI - Orientação para Instalação**



**Primeiro Passo:** Instalar o módulo de integração no servidor de aplicação do SEI (a partir da versão 3.0.6)

**1.1.** Baixar a última versão do módulo wssei no endereço: https://softwarepublico.gov.br/gitlab/sei/mod-wssei/tags.

**1.2.** Copiar a pasta contendo o módulo wssei para o diretório de módulos do SEI,localizado em \&lt;caminho/do/projeto\&gt;/sei/web/modulos. Certifique-se de que a pasta contenha os arquivos do módulo está com o nome **wssei**.

**1.3.** Adicionar no arquivo de configuração do sistema (ConfiguracaoSEI.php), na chave Módulos, a referência para a pasta do módulo copiado no passo anterior, utilizando a chave de identificação MdWsSeiRest. O sistema procura pelo módulo a partir da pasta de módulos do SEI. 

Exemplo:

&#39;SEI&#39; =\&gt; ARRAY(

( ...)

&#39;Modulos&#39; =\&gt; array(

&#39;MdWsSeiRest&#39; =\&gt; &#39;wssei/&#39;

)

),

**1.4.** Adicionar no arquivo de configuração do sistema (ConfiguracaoSEI.php), no Array de configurações, a chave com as configurações abaixo para que seja realizada o envio de notificações:

Exemplo:
```bash
public function getArrConfiguracoes(){
    return array(
        'SEI' => array(
            (...)
        ),
        'WSSEI' => array(
            'UrlServicoNotificacao' => '{URL do serviço de notificação}', 
            'IdApp' => '{ID do app registrado no serviço de notificação}', 
            'ChaveAutorizacao' => '{Chave de autorização do serviço de notificação}' 
        ),
    
        (...)
```
**1.5.** Realizar o procedimento de verificação e atualização de scripts de banco de dados conforme os seguintes passos:

**1.5.1.** Mover o arquivo de instalação do módulo no SEI sei_atualizar_versao_modulo_wssei.php para a pasta sei/scripts.
 
**1.5.2.** Executar o script **sei_atualizar_versao_modulo_wssei.php** para inserção de dados no banco do SEI referente ao módulo. 
```bash
php -c /etc/php.ini [DIRETORIO_RAIZ_INSTALAÇÃO]/sei/scripts/sei_atualizar_versao_modulo_wssei.php
```

**1.6.** Necessário habilitar/instalar a extensão PHP &quot;mbstring&quot;. Verificar se todos os requisitos para utilização do SEI 3.0 estão sendo atendidos, entre eles, a versãoo do PHP 5.6.

**1.7.** Verificar se o módulo foi carregado por meio do menu Infra/Módulos do SEI.

**1.8.** Verificar se o QR Code foi criado na parte inferior do menu lateral esquerdo doSEI. Esse código contém os dados de acesso ao ambiente do órgão.



 
**Segundo Passo:** Instalar o aplicativo no telefone celular

**2.1.** No telefone celular, acessar a loja Google Play ou App Store e realizar a instalação do aplicativo do SEI.



**Terceiro Passo:** Realizar a leitura do QR Code

**3.1.** No telefone celular, abrir o aplicativo do SEI.

**3.2.** Acessar a opção &quot;Trocar órgão&quot; e, em seguida, a opção &quot;Ler Código&quot;.

**3.3.** Fazer a leitura do QR Code no SEI _web_ do seu órgão com a câmera do telefone celular.

**3.4.** Informar o usuário e a senha do SEI, e iniciar o uso do aplicativo.