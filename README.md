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

**1.4.** Necessário habilitar/instalar a extensão PHP &quot;mbstring&quot;. Verificar se todos os requisitos para utilização do SEI 3.0 estão sendo atendidos, entre eles, a versãoo do PHP 5.6.

**1.5.** Verificar se o módulo foi carregado por meio do menu Infra/Módulos do SEI.

**1.6.** Verificar se o QR Code foi criado na parte inferior do menu lateral esquerdo doSEI. Esse código contém os dados de acesso ao ambiente do órgão.



 
**Segundo Passo:** Instalar o aplicativo no telefone celular

**2.1.** No telefone celular, acessar a loja Google Play ou App Store e realizar a instalação do aplicativo do SEI.



**Terceiro Passo:** Realizar a leitura do QR Code

**3.1.** No telefone celular, abrir o aplicativo do SEI.

**3.2.** Acessar a opção &quot;Trocar órgão&quot; e, em seguida, a opção &quot;Ler Código&quot;.

**3.3.** Fazer a leitura do QR Code no SEI _web_ do seu órgão com a câmera do telefone celular.

**3.4.** Informar o usuário e a senha do SEI, e iniciar o uso do aplicativo.