# Template de Software PHP

## Introdução

Este é um software PHP recém-criado.
O objetivo é facilitar a adequação aos padrões de integração contínua e qualidade de software.
Siga as instruções para configuração do software.

## Configuraçoes

### svn:ignore

Diretório raiz:

    *~
    .DS_Store
    ._*
    .~lock.*
    .settings
    .buildpath
    .project
    .idea
    cache.properties
    nbproject
    composer.phar
    vendor

Diretório `build`:

    api
    code-browser
    coverage
    logs
    pdepend
    phpdox

Diretório `config/autoload`:

    desenvolvedor.php

### Arquivos

* `composer.json`, altere o valor de:
    * `name`
    * `description`
    * `require`
* `phpunit.xml`
    * Configure `<testsuites>`
    * Configure `<filter>`
* `phpdox.xml`
    * Configure `<collector>`
* `build.xml`
    * Defina a propriedade `sourcedir`

 ### LAYOUT PADRÃO DO MEC

 * `/login`, Tela de Login para Sistemas Sem SSD

 * `/primeiro-acesso`, Tela para redirecionamento de Login para o SSD

 * `/componentes`, Tela para visualizar todos os componentes disponíveis no sistema

 * `/faq`, Tela para FAQ Padrão do sistema

 *   REFERENCIA PARA TEMPLATE PADRÃO : http://padraosistemas.mec.gov.br

 *   REFERENCIA PARA TEMPLATE DE APLICAÇÕES PHP : http://recomendacoesphp.mec.gov.br
