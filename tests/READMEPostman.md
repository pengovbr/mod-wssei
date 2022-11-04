# Testes da API - Postman

Na pasta testes/Postman estão os testes para serem executados via aplicação ou Newman

## Postman
Para rodar os testes em Postman:

### Pre-requisitos
- Instale o Postman v7.6 ([https://www.getpostman.com/](https://www.getpostman.com/)) ou superior
- Conhecimento de uso básico/moderado do Postman

### Para Rodar os Testes
- Baixe o projeto deste git (vou chamar de `<projeto>`)
- Vá até a pasta `<projeto>/tests/Postman`
- Importe os arquivos Wssei-Tests.postman_collection.json e SEI.postman_environment_substituido.json para o Postman
- O arquivo SEI.postman_environment_substituido.json é a cópia do SEI.postman_environment.json trocando a variável $NEWMAN_BASEURL pela url de onde se deseja testar ex.: http://localhost:8000
- Ajuste o Working Dir do Postman para a pasta `<projeto>/tests/Postman` - necessário para reconhecer os arquivos de upload de docs externos
- Ajuste os valores do seu environment, de acordo com o ambiente que deseja testar
- Execute o teste

### Para Rodar os Testes com o NEWMAN

Na pasta `<projeto>` apontando para máquina local:

```bash
export NEWMAN_BASEURL=https://localhost:8000 ; make tests-api
```

---
[Retornar para Testes](README.md)

[Retornar ao Início](../README.md)