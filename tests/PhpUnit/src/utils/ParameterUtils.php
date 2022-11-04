<?php

require_once dirname(__FILE__)."/DatabaseUtils.php";

class ParameterUtils{

	const SEI_HABILITAR_NUMERO_PROCESSO_INFORMADO = "SEI_HABILITAR_NUMERO_PROCESSO_INFORMADO";
	const PARAM_NUMERO_INFORMADO_DESABILITADO = 0;
    const PARAM_NUMERO_INFORMADO_PROTOCOLO = 1;
    const PARAM_NUMERO_INFORMADO_UNIDADES = 2;

	public function getParameter($parameter){
		$result = null;
		$query = "SELECT valor FROM sei.infra_parametro WHERE nome = ?";
		$values = DatabaseUtils::query($query, array($parameter));

		if(isset($values)){
			$result = $values[0]["valor"];
		}

		return $result;
	}

	public function setParameter($parameter, $value){
		$query = "UPDATE sei.infra_parametro SET valor = ? WHERE nome = ?";
		DatabaseUtils::execute($query, array($value, $parameter));
		return $value;
	}
}