
# Módulo Rest WSSEI [![PHP_CodeSniffer](https://github.com/pengovbr/mod-wssei/actions/workflows/phpcs.yml/badge.svg)](https://github.com/pengovbr/mod-wssei/actions/workflows/phpcs.yml) [![Testes da API](https://github.com/pengovbr/mod-wssei/actions/workflows/tests.yml/badge.svg)](https://github.com/pengovbr/mod-wssei/actions/workflows/tests.yml)

O módulo **WSSEI** é o responsável por disponibilizar no Sistema Eletrônico de Informações - SEI endpoints REST para o SEI. 


## O REPOSITÓRIO

Este repositório no GitHub é o local oficial onde será mantido todo o desenvolvimento do módulo do webservice SEI - REST. Além do código-fonte, também pode ser encontrado o pacote de distribuição para instalação do módulo, questões ou problema em aberto e planejamento de novas versões.

## Compatibilidade de versões

O módulo é compatível com as seguintes versões do SUPER/SEI:

| Versão SEI/SUPER             | Versão módulo mod-wssei  |
| ---                          | ---                      |
| 4.0.x                        | mod-wssei 2.0.x          |
| 4.1.1                        | mod-wssei 2.2.0          |
| 4.1.2, 4.1.3, 4.1.4, 4.1.5   | mod-wssei 2.2.1          |
| 5.0.0                        | mod-wssei 3.0.0          |

## DOWNLOAD

O download do pacote de instalação/atualização do mod-wssei pode ser encontrado na seção Releases deste projeto no GitHub. 
Acesse o link https://github.com/spbgovbr/mod-wssei/releases

**[DOWNLOAD PACOTE DE INSTALAÇÃO MOD-WSSEI](https://github.com/spbgovbr/mod-wssei/releases)** 


## DOCUMENTAÇÃO

As instruções de instalação e atualização do módulo, assim com o manual de utilização do usuário,  podem ser encontradas na pasta `docs/` bem como para acessar e registrar o APP do SEI.

* **[MANUAL DE INSTALAÇÃO](docs/INSTALACAO.md)**
* **[MANUAL DE ATUALIZAÇÃO](docs/ATUALIZACAO.md)**


### Documentação da API Rest

Clique abaixo para acessar a documentação disponível da API Rest 

[Documentação da API](docs/api.md)

### Testes Escritos para a API

Confira abaixo como rodar os testes funcionais já escritos em seu ambiente de testes do SEI ou em sua máquina local.

[Testes da API](tests/README.md)

## PROJETOS RELACIONADOS

O mod-wssei trata-se de um módulo adicional ao Sistema Eletrônico de Informações (SEI) para adição de novas funcionalidades de endpoints REST para integração com outros sistemas inclusive para o aplicativo SEI. Para a sua utilização, é necessário que a instituição possua o sistema SEI.

Para informações sobre como aderir ao SEI, acesse: 
https://www.gov.br/economia/pt-br/assuntos/processo-eletronico-nacional/conteudo/sistema-eletronico-de-informacoes-sei


## CONTRIBUIÇÃO

Existem diversas formas de colaborar neste projeto:

* Enviar registros de erros ou solicitação de melhorias ([Issues](https://github.com/spbgovbr/mod-wssei/issues))
* Revisar a documentação do projeto e enviar qualquer tipo de contribuição via [Pull Request](https://github.com/spbgovbr/mod-wssei/pulls)
* Ajudar na correção de erros ou melhoria da base de código. Para isto, faça um fork do projeto no GitHub e posteriormente nos envie um [Pull Request](https://github.com/spbgovbr/mod-wssei/pulls)

## DESENVOLVIMENTO

Para iniciar o ambiente na máquina local, com o Docker instalado e configurado o .env onde se encontra o código-fonte do SEI/SUPER:

```bash

make up

```

Faça suas modificações e depois execute os testes na API, entre outros testes:

```bash

export NEWMAN_BASEURL=https://localhost:8000 ; make tests-api

```

## SUPORTE

Em caso de dúvidas ou problemas durante o procedimento de atualização, favor entrar em conta pelos canais de atendimento disponibilizados na Central de Atendimento do Processo Eletrônico Nacional, que conta com uma equipe para avaliar e responder esta questão de forma mais rápida possível.

Para mais informações, contate a equipe responsável por meio dos seguintes canais:
- [Portal de Atendimento (PEN): Canal de Atendimento](https://portaldeservicos.economia.gov.br) - Módulo do Barramento
- Telefone: 0800 978 9005


# Aplicativo SEI e Módulo Rest

### Orientações para Instalação

Clique abaixo para orientações de instalação do Módulo Rest no SEI bem como para acessar e registrar o APP do SEI.

[Instruções de Instalação e Configuração](docs/INSTALACAO.md)

