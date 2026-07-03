<?php

/**
 * Utilitario simples de acesso a banco via PDO para apoio aos testes funcionais.
 * As credenciais e DSN sao lidas das variaveis de ambiente do contexto (SEI/SIP).
 */
class DatabaseUtils
{
    private $connection;

    public function __construct($nomeContexto)
    {
        try {
            $dns = getenv($nomeContexto . "_DSN");
            $user = getenv($nomeContexto . "_DATABASE_USER");
            $password = getenv($nomeContexto . "_DATABASE_PASSWORD");
            $this->connection = new PDO($dns, $user, $password);
        } catch (PDOException $e) {
            echo "Erro na conexao: " . $e->getMessage();
        }
    }

    public function execute($sql, $params = array())
    {
        $statement = $this->connection->prepare($sql);
        return $statement->execute($params);
    }

    public function query($sql, $params = array())
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function getBdType()
    {
        return $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}
