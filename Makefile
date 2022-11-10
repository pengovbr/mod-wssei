.PHONY: all dist clean check-super-path check-module-config check-super-isalive prerequisites-up prerequisites-modulo-instalar install up update config down restart destroy tests-functional-orientations tests-functional-validar echo "Variaveis de ambientes tests-functional-prerequisites tests-functional tests-functional-loop tests-api help

-include .testselenium.env
-include .env
-include .modulo.env

# Parâmetros de configuração
base = mysql

ifndef SEI_HOST
	SEI_HOST=http://localhost:8000
endif

MODULO_NOME = wssei
MODULO_PASTAS_CONFIG = mod-$(MODULO_NOME)
MODULO_PASTA_NOME = $(notdir $(shell pwd))
VERSAO_MODULO := $(shell grep 'const VERSAO_MODULO' ./src/MdWsSeiRest.php | cut -d'"' -f2)
SEI_SCRIPTS_DIR = dist/sei/scripts/$(MODULO_PASTAS_CONFIG)
SEI_CONFIG_DIR = dist/sei/config/$(MODULO_PASTAS_CONFIG)
SIP_SCRIPTS_DIR = dist/sip/scripts/$(MODULO_PASTAS_CONFIG)
SEI_MODULO_DIR = dist/sei/web/modulos/mod-$(MODULO_NOME)

ARQUIVO_CONFIG_SEI=$(SEI_PATH)/sei/config/ConfiguracaoSEI.php
ARQUIVO_ENV_WSSEI=.modulo.env
MODULO_COMPACTADO = mod-$(MODULO_NOME)-$(VERSAO_MODULO).zip

CMD_INSTALACAO_SEI = echo -ne '$(SEI_DATABASE_USER)\n$(SEI_DATABASE_PASSWORD)\n' | php atualizar_versao_sei.php
CMD_INSTALACAO_SIP = echo -ne '$(SIP_DATABASE_USER)\n$(SIP_DATABASE_PASSWORD)\n' | php atualizar_versao_sip.php
CMD_INSTALACAO_RECURSOS_SEI = echo -ne '$(SIP_DATABASE_USER)\n$(SIP_DATABASE_PASSWORD)\n' | php atualizar_recursos_sei.php
CMD_INSTALACAO_SEI_MODULO = echo -ne '$(SEI_DATABASE_USER)\n$(SEI_DATABASE_PASSWORD)\n' | php sei_atualizar_versao_modulo_wssei.php
CMD_INSTALACAO_SIP_MODULO = echo -ne '$(SIP_DATABASE_USER)\n$(SIP_DATABASE_PASSWORD)\n' | php sip_atualizar_versao_modulo_wssei.php

RED=\033[0;31m
NC=\033[0m
YELLOW=\033[1;33m

MENSAGEM_AVISO_MODULO = $(RED)[ATENÇÃO]:$(NC)$(YELLOW) Necessário configurar a chave de configuração do módulo no arquivo de configuração do SEI (ConfiguracaoSEI.php) e prover o modulo na pasta correta $(NC)\n               $(YELLOW)'Modulos' => array('MdWsSeiRest' => 'mod-wssei') $(NC)
MENSAGEM_AVISO_ENV = $(RED)[ATENÇÃO]:$(NC)$(YELLOW) Configurar parâmetros de autenticação do ambiente de testes do módulo de WSSEI no arquivo .modulo.env $(NC)
MENSAGEM_AVISO_FONTES = $(RED)[ATENÇÃO]:$(NC)$(YELLOW) Nao foi possivel localizar o fonte do Super. Verifique o valor SEI_PATH no arquivo .env $(NC)

CMD_CURL_SUPER_LOGIN = curl -s -L $(SEI_HOST)/sei | grep -q "<input.*txtUsuario.*>"

define TESTS_MENSAGEM_ORIENTACAO
Leia o arquivo README relacionado aos testes.
O arquivo encontra-se nesse repositorio na pasta de testes funcionais.

Existem orientacoes para o teste que estao definidas no README que se nao forem
obedecidas, o teste falhará.

Pressione y para continuar [y/n]...
endef
export TESTS_MENSAGEM_ORIENTACAO


all: clean dist


dist: ## Gera o pacote de distribuicao para o Super. Nao esquecer de gerar o Changelog antes, de acordo com a versao
	@mkdir -p $(SEI_SCRIPTS_DIR)
	@mkdir -p $(SEI_CONFIG_DIR)
	@mkdir -p $(SEI_MODULO_DIR)
	@mkdir -p $(SIP_SCRIPTS_DIR)
	@cp -Rf src/* $(SEI_MODULO_DIR)/
	@cp docs/INSTALACAO.md dist/INSTALACAO.md
	@cp docs/ATUALIZACAO.md dist/ATUALIZACAO.md
	@cp docs/changelogs/CHANGELOG-$(VERSAO_MODULO).md dist/NOTAS_VERSAO.md
	@mv $(SEI_MODULO_DIR)/scripts/sei_atualizar_versao_modulo_wssei.php $(SEI_SCRIPTS_DIR)/
	@mv $(SEI_MODULO_DIR)/scripts/sip_atualizar_versao_modulo_wssei.php $(SIP_SCRIPTS_DIR)/
	@mv $(SEI_MODULO_DIR)/config/ConfiguracaoMdWSSEI.php $(SEI_CONFIG_DIR)/ConfiguracaoMdWSSEI.prod.exemplo.php
	@rm -rf $(SEI_MODULO_DIR)/config
	@rm -rf $(SEI_MODULO_DIR)/scripts
	@cd dist/ && zip -r $(MODULO_COMPACTADO) INSTALACAO.md ATUALIZACAO.md NOTAS_VERSAO.md sei/ sip/	
	@rm -rf dist/sei dist/sip dist/INSTALACAO.md dist/ATUALIZACAO.md
	@echo "Construção do pacote de distribuição finalizada com sucesso"


clean: ## Limpa o diretório contendo arquivos temporários de construção do pacote de distribuição
	@rm -rf dist
	@echo "Limpeza do diretório de distribuição do realizada com sucesso"


.env:
	@if [ ! -f ".env" ]; then \
	cp envs/$(base).env .env; \
	echo "Arquivo .env nao existia. Copiado o arquivo default da pasta envs."; \
	echo "Se for o caso, faca as alteracoes nele antes de subir o ambiente."; \
	echo ""; sleep 5; \
	fi


.modulo.env:
	@if [ ! -f ".modulo.env" ]; then \
	cp envs/modulo.env .modulo.env; \
	fi


.testselenium.env:
	@if [ ! -f ".testselenium.env" ]; then \
	cp envs/testselenium.env .testselenium.env ; \
	echo "Arquivo .testselenium.env nao existia. Copiado default da pasta envs."; \
	echo "Se for o caso, faca as alteracoes nele antes de rodar os testes."; \
	echo ""; sleep 5; \
	fi


check-super-path:
	@if [ ! -f $(SEI_PATH)/sei/web/SEI.php ]; then \
	echo "$(MENSAGEM_AVISO_FONTES)\n" ; \
	exit 1 ; \
	fi


check-module-config:
	@docker-compose exec -T httpd bash -c "php /project/utils/verificar_modulo.php" ; ret=$$?; echo "$$ret"; if [ ! $$ret -eq 0 ]; then echo "$(MENSAGEM_AVISO_MODULO)\n"; exit 1; fi


check-super-isalive: ## Target de apoio. Acessa o Super e verifica se esta respondendo a tela de login
	@echo ""
	@echo "$(WARNING)Aguardando inicialização do ambiente de desenvolvimento...$(NC)"
	@for i in `seq 1 20`; do \
	    echo "Tentativa $$i/10";  \
		if $(CMD_CURL_SUPER_LOGIN); then \
				echo 'Página de login carregada!' ; \
				break ; \
		fi; \
		sleep 5; \
	done; \
	if ! $(CMD_CURL_SUPER_LOGIN); then echo '$(ERROR)Ambiente de desenvolvimento não pôde ser carregado corretamente.$(NC)'; exit 1 ; fi;



prerequisites-up: .env .modulo.env check-super-path


prerequisites-modulo-instalar: check-super-path check-module-config check-super-isalive


install: prerequisites-modulo-instalar ## Instala e atualiza as tabelas do módulo na base de dados do sistema
	docker-compose exec -T -w /opt/sei/scripts/$(MODULO_PASTAS_CONFIG) httpd bash -c "$(CMD_INSTALACAO_SEI_MODULO)";
	docker-compose exec -T -w /opt/sip/scripts/$(MODULO_PASTAS_CONFIG) httpd bash -c "$(CMD_INSTALACAO_SIP_MODULO)";
	@echo "==================================================================================================="
	@echo ""
	@echo "Fim da instalação do módulo"


up: prerequisites-up  ## Inicia ambiente de desenvolvimento local (docker) no endereço http://localhost:8000
	docker-compose up -d
	make check-super-isalive

update: ## Atualiza banco de dados através dos scripts de atualização do sistema
	docker-compose run --rm -w /opt/sei/scripts/ httpd bash -c "$(CMD_INSTALACAO_SEI)"; true
	docker-compose run --rm -w /opt/sip/scripts/ httpd bash -c "$(CMD_INSTALACAO_SIP)"; true
	docker-compose run --rm -w /opt/sip/scripts/ httpd bash -c "$(CMD_INSTALACAO_RECURSOS_SEI)"; true


config: ## Configura o ambiente para outro banco de dados (mysql|sqlserver|oracle). Ex: make config base=oracle 
	@cp -f envs/$(base).env .env
	@echo "Ambiente configurado para utilizar a base de dados $(base). (base=[mysql|oracle|sqlserver])"

down:  ## Interrompe execução do ambiente de desenvolvimento local em docker
	docker-compose down


restart: down up ## Reinicia execução do ambiente de desenvolvimento local em docker


destroy:  ## Destrói ambiente de desenvolvimento local, junto com os dados armazenados em banco de dados
	docker-compose down --volumes


# mensagens de orientacao para first time buccaneers
tests-functional-orientations:
ifndef MSGORIENTACAO 
	@( read -p "$$TESTS_MENSAGEM_ORIENTACAO" sure && case "$$sure" in [yY]) true;; *) false;; esac )
endif


# validar os testes antes de rodar
tests-functional-validar: tests-functional-orientations
	@if [ -z "$$SELENIUMTEST_SISTEMA_URL" ] || [ -z "$$SELENIUMTEST_SISTEMA_ORGAO" ]; then \
	    echo "Variaveis de ambientes: SELENIUMTEST_SISTEMA_URL, SELENIUMTEST_SISTEMA_ORGAO, SELENIUMTEST_MODALIDADE nao definidas."; \
			echo "Verifique se o arquivo de configuracao para os testes esta criado (.testselenium.env)"; \
			echo "Existe um modelo desse arquivo na pasta envs."; \
			exit 1; \
	fi


tests-functional-prerequisites: .testselenium.env tests-functional-validar


# roda apenas os testes, o ajuste de data inicial e a criacao do ambiente ja devem ter sido realizados
tests-functional: tests-functional-prerequisites check-super-isalive
	@echo "Vamos iniciar a execucao dos testes..."
	@cd tests/functional && SEI_HOST=$(SEI_HOST) ./testes.sh


# roda desde o ajuste de data inicial e criacao do ambiente e tb os testes
# caso encontre algum erro nos testes executa td novamente em loop
tests-functional-loop: tests-functional-prerequisites
	@echo "Vamos iniciar a execucao completa com loop"
	@cd tests/functional && ./testes-completo-loop.sh

# Executa testes no postman. Necessário a variável NEWMAN_BASEURL apontando
# para ambiente correto exemplo: 
# export NEWMAN_BASEURL=https://sei.economia.gov.br ; make tests-api
tests-api:
	@echo "Substituindo as envs para o Newman"
	@envsubst < tests/Postman/SEI.postman_environment.json > tests/Postman/SEI.postman_environment_substituido.json
	@echo "Vamos iniciar a execução do postman/newman"
	@docker run --network="host" -t -v $(shell pwd)/tests/Postman:/etc/newman dannydainton/htmlextra run Wssei-Tests.postman_collection.json\
	        --environment SEI.postman_environment_substituido.json\
            --working-dir .\
            -r cli,htmlextra

help:
	@echo "Usage: make [target] ... \n"
	@grep -E '^[a-zA-Z_-]+[[:space:]]*:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
