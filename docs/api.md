# Documentação dos Serviços

## Postman

Aqui estamos disponibilizando uma breve documentação dos serviços disponíveis na API.

Para visualizá-la basta instalar o utilitário  [Postman](https://www.postman.com/) .

Esse utilitário conta com uma versão gratuita, e elenca os serviços da nossa API em chamadas REST. 

Serve tanto para documentar os serviços e parâmetros, quanto irá auxiliar desenvolvedores e integradores de solução a construírem de forma mais ágil as suas soluções particulares.

Dúvidas com o utilitário Postman bem como sua filosofia de uso podem ser sanadas na própria comunidade do utilitário.

## Arquivos Postman

### Download do Projeto Postman

** caminhos relativos no repositório do github: **

- docs/postman/MD-WSSEI.postman_collection.json
- docs/postman/SEI-Nuvem.postman_environment.json

Breve explicação sobre os arquivos, abaixo.

### Postman da API - mod_wssei

docs/postman/MD-WSSEI.postman_collection.json

Projeto Postman elaborado pelos desenvolvedores da API para facilitar o uso por terceiros.

Os serviços estão separados por categorias e em cada categoria existe um ou mais serviços. Para cada serviço temos:
- nome do serviço
- descrição dos serviços
- parâmetros esperados
- tipos dos parâmetros esperados
- url de chamada do serviço
- tipo de chamada do serviço (GET - Post, etc)
- exemplo de chamada
- a ferramenta também mostra/permite, não exaustivamente:
	- exemplos da chamada do serviço em dezenas de linguagens de programação diferentes
	- executar de fato a chamada e observar o retorno em várias formas de saída (html, raw, json, etc)
	- construir o seu workflow pessoal de algum caso de teste: por exemplo, logar no SEI, cadastrar um processo e incluir documento nesta ordem


### Environment para Uso

docs/postman/SEI-Nuvem.postman_environment.json

O arquivo de environment serve para informar os parâmetros referentes ao ambiente. Inicialmente, você irá alterar o campo "baseurl" que indica onde encontra-se o SEI. 

O parâmetro "token" também é reaproveitado em outras chamadas e deve ser preenchido assim que você receber o token após rodar o serviço de autenticação.


## Testes da API

Temos também a disposição um cenário de teste completo tomando por base um SEI zerado iniciando com a base de referência do poder executivo.

Maiores detalhes acesse a área de Teste nesse projeto clicando aqui [Testes da API](../testes/README.md)

---
[Retornar ao Início](../README.md)