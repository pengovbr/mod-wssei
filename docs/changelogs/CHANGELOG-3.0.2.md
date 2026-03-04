## Novidades da versão 3.0.2

Este documento descreve as principais mudanças aplicadas nesta atualização de versão do **Módulo do WSSEI**.

Para instruções sobre como realizar a atualização do sistema, acesse a seção **[Atualização de Versão](#atualização-de-versão)** no final deste documento. Outras informações sobre procedimentos de **instalação** ou **atualização** de versões anteriores, acesse os seguintes documentos:

* [Instalação](../v3.0.2/docs/INSTALACAO.md) - Procedimento de instalação do módulo
* [Atualização](../v3.0.2/docs/ATUALIZACAO.md) - Procedimento detalhados para atualização de uma versão anterior.

## Compatibilidade de versões

O módulo é compatível com as seguintes versões do SUPER/SEI:

| Versão SEI/SUPER | Versão módulo mod-wssei |
| ---              | ---                     |
| 4.0.x            | mod-wssei 2.0.x         |
| 4.1.1            | mod-wssei 2.2.x         |
| 5.0.0, 5.0.1, 5.0.2, 5.0.3, 5.0.4            | mod-wssei 3.0.x         |	


### Lista de melhorias e correções de problemas

Todas as atualizações podem incluir itens referentes à segurança, requisito em permanente monitoramento e evolução, motivo pelo qual a atualização com a maior brevidade possível é sempre recomendada.

#### Correção de erro ao tentar inicializar (#5c33c9c4d985f131227e89b89379477b9be91a65)
Erro de Constant DIR_SEI_WEB already defined" no PHP 8.2 ao tentar subir o módulo com outro módulo que já definiu a constante.

#### Recuperar Processos relacionados (#93)
GET processo/{protocolo}/relacionamentos pode ser usado para pegar relacionamentos dos processos.

#### Incluir data/hora em GET /documento/listar/assinaturas/{documento} (#92)

### Atualização de Versão

#### Pré-requisitos

Versão 5.0.0 até 5.0.4 do **SEI** instalado (verificar valor da constante de versão no arquivo sei/web/SEI.php).

#### Instruções

1. Baixar a última versão do pacote de instalação do sistema (arquivo `mod-wssei-[VERSÃO].zip`) localizado na página de [Releases do projeto MOD-WSSEI](https://github.com/spbgovbr/mod-wssei/releases), seção **Assets**.

2. Fazer backup dos diretórios "sei", "sip" e "infra" do servidor web;

3. Descompactar o pacote de instalação `mod-wssei-[VERSÃO].zip`;

4. Copiar os diretórios descompactados "sei", "sip" para os servidores, sobrescrevendo os arquivos existentes;

5. Executar o script para atualização dos recursos do mod-wssei no SIP em linha de comando:

```bash
php -c /etc/php.ini  [DIRETORIO_RAIZ_INSTALAÇÃO]/sip/scripts/mod-wssei/sip_atualizar_versao_modulo_wssei.php
```

6. Executar o script para atualização dos recursos do mod-wssei no SEI em linha de comando:

```bash
php -c /etc/php.ini  [DIRETORIO_RAIZ_INSTALAÇÃO]/sei/scripts/mod-wssei/sei_atualizar_versao_modulo_wssei.php
```
