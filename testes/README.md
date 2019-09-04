# Testes da API

Na pasta PhpUnit há os testes escritos em php
Na pasta Postman está os testes para serem rodados via aplicação ou Newman

## Postman
Para rodar os testes em Postman:

### Pre-requisitos
- Instale o Postman v7.6 ([https://www.getpostman.com/](https://www.getpostman.com/)) ou superior
- Conhecimento de uso básico/moderado do Postman

### Para Rodar os Testes
- Baixe o projeto deste git (vou chamar de `<projeto>`)
- Vá até a pasta `<projeto>/testes/Postman`
- Importe os arquivos Wssei-Tests.postman_collection.json e SEI.postman_environment.json para o Postman
- Ajuste o Working Dir do Postman para a pasta `<projeto>/testes/Postman` - necessário para reconhecer os arquivos de upload de docs externos 
- Ajuste os valores do seu environment, de acordo com o ambiente que deseja testar
- Execute o teste


