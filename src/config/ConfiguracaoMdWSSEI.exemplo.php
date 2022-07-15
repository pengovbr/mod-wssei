<?

/**
 * Arquivo de configuração do Módulo de Serviços Rest para o SEI
 *
 * Seu desenvolvimento seguiu os mesmos padrões de configuração implementado pelo SEI e SIP e este
 * arquivo precisa ser adicionado à pasta de configurações do SEI para seu correto carregamento pelo módulo.
 */

class ConfiguracaoMdWSSEI extends InfraConfiguracao  {

	private static $instance = null;

    /**
     * Obtém instância única (singleton) dos dados de configuração do módulo de integração
     *
     *
     * @return ConfiguracaoMdWSSEI
     */
    public static function getInstance()
    {
		if (ConfiguracaoMdWSSEI::$instance == null) {
			ConfiguracaoMdWSSEI::$instance = new ConfiguracaoMdWSSEI();
		}
		return ConfiguracaoMdWSSEI::$instance;
	}

    /**
     * Definição dos parâmetro de configuração do módulo
     *
     * @return array
     */
    public function getArrConfiguracoes()
    {
        return array(

            'WSSEI' => array(
                // URL do serviço de notificação PUSH
                'UrlServicoNotificacao' => getenv('MOD_WSSEI_URL_SERVICO_NOTIFICACAO'),

                // ID do app registrado no serviço de notificação
                'IdApp' => getenv('MOD_WSSEI_ID_APP'),

                // Chave de autorização do serviço de notificação
                'ChaveAutorizacao' => getenv('MOD_WSSEI_CHAVE_AUTORIZACAO'),

                // Chave unica com pelo menos 32 chars. Pode usar o comando uuidgen para gerar
                'TokenSecret' => getenv('MOD_WSSEI_TOKEN_SECRET')
            ),
        );
    }
}