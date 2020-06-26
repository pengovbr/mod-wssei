# Testes da API

Aqui vamos mostrar de forma rápida um switch de testes completo que criamos para a API. Pode ser usada pelos desenvolvedores/integradores como base ou consulta ao construir suas próprias integrações.

## Roadmap do Teste

### Instalar um SEI do Zero

Faz-se necessário instalar um SEI do zero com a base de dados do poder executivo pré-carregada.
Para isso há a opção de proceder com o projeto [SEI-Vagrant](https://github.com/guilhermeadc/sei-vagrant)

### Instalar o Módulo Rest do SEI

Após o sei estar instalado, proceda com a instalação do módulo Wssei - [https://github.com/spbgovbr/mod-wssei](https://github.com/spbgovbr/mod-wssei)

### Realizar pré-carga do ambiente

O ambiente está no ar com a carga inicial do poder executivo.
O módulo já está instalado.

Agora faz-se necessário criar usuário, definir seu perfil, criar cargos para assinatura e outras configurações necessárias que não conseguimos fazer usando a API.

Para isso vamos usar o SeleniumIDE, que simula um usuário fazendo essas operações para nós.

Proceda rodando o SeleniumIDE para cada arquivo abaixo:
- mod-wssei/testes/SeleniumIDE/010-SEI-ModWssei-SIP.side
- mod-wssei/testes/SeleniumIDE/012-SEI-ModWssei-SIP.side
- mod-wssei/testes/SeleniumIDE/013-SEI-ModWssei-SIP.side
- mod-wssei/testes/SeleniumIDE/015-SEI-ModWssei-SIP.side
- mod-wssei/testes/SeleniumIDE/030-SEI-ModWssei-SEI.side

Obs: será necessário alterar a url para onde aponta o SEI no SeleniumIDE

### Rodar Testes Postman na API

Agora sim, com o ambiente no ar, pré carregado e configurado com os usuários, podemos finalmente chamar os testes da API.

[Rodar Cenários Concebidos](READMEPostman.md)

Não vamos entrar em detalhes, pois o próprio projeto de testes Postman tem essa informação. Ele vai rodar criação de processos, download e upload de docs, testes de assinatura, cargos, acompanhamento, login com users em diferentes unidades, tramitação entre unidades, cargas diversas etc.

---
[Retornar para Testes](README.md)

[Retornar ao Início](../README.md)