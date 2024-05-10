## Novidades da versão 2.2.0

Este documento descreve as principais mudanças aplicadas nesta atualização de versão do **SUPER.GOV.BR**.

Para instruções sobre como realizar a atualização do sistema, acesse a seção **[Atualização de Versão](#atualização-de-versão)** no final deste documento. Outras informações sobre procedimentos de **instalação** ou **atualização** de versões anteriores, acesse os seguintes documentos:

* [Instalação](../<VERSAO>/docs/INSTALACAO.md) - Procedimento de instalação do SUPER.GOV.BR
* [Atualização](../<VERSAO>/docs/ATUALIZACAO.md) - Procedimento detalhados para atualização de uma versão anterior compatível com SUPER/SEI 4.0.x

## Compatibilidade de versões

O módulo é compatível com as seguintes versões do SUPER/SEI:

| Versão SEI/SUPER | Versão módulo mod-wssei |
| ---              | ---                     |
| 4.0.x            | mod-wssei 2.0.x         |
| 4.1.1            | mod-wssei 2.2.x         |


### Lista de melhorias e correções de problemas

Todas as atualizações podem incluir itens referentes à segurança, requisito em permanente monitoramento e evolução, motivo pelo qual a atualização com a maior brevidade possível é sempre recomendada.

#### Erro ao obter Acompanhamentos Especiais (#60)

Corrigido erro ao obter Acompanhamentos Especiais. Mensagem de erro observada: "Slim Application Error". 

#### Erro ao obter processos quando algum processo possui mais de um marcador associado  (#64)

Corrigido erro ao obter processos quando algum processo possui mais de um marcador associado. Mensagem do erro observada: "Consulta retornou mais de um registro de ANDAMENTO_MARCADOR."

#### Compatibilidade com a versão do SEI 4.1  (#75)

Realizadas alterações necessárias visando compatibilizar o módulo com a versão 4.1 do SEI.

### Atualização de Versão

#### Pré-requisitos

Versão 4.0.x do **SEI** ou **SUPER.GOV.BR** ou superior instaladas (verificar valor da constante de versão no arquivo sei/web/SEI.php).

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
