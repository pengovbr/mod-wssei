<?php

class DatabaseUtils
{
	protected static function getInstance()
	{
		$connection = new PDO(DB_SEI_DSN, DB_SEI_USER, DB_SEI_PASSWORD);

		if (!$connection){
  			throw new InfraException('Não foi possível abrir conexão com o banco de dados.');
		}

		return $connection;
	}

	public static function execute($sql, $params){
		$statement = self::getInstance()->prepare($sql);
		return $statement->execute($params);
	}

	public static function query($sql, $params){
		$statement = self::getInstance()->prepare($sql);
		$statement->execute($params);
		return $statement->fetchAll();
	}
}