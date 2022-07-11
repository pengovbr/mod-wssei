<?php

require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiEditorRN extends InfraRN
{

    CONST VERSAO_CARIMBO_PUBLICACAO_OBRIGATORIO = "3.0.7";

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Método que verifica o o atributo carimbo publicação é obrigatório na versão atual do SEI
     * @return bool
     */
    public static function versaoCarimboPublicacaoObrigatorio(){
        $numVersaoAtual = intval(str_replace('.', '', SEI_VERSAO));
        $numVersaoCarimboObrigatorio = intval(str_replace('.', '', self::VERSAO_CARIMBO_PUBLICACAO_OBRIGATORIO));
        if($numVersaoAtual >= $numVersaoCarimboObrigatorio){
            return true;
        }

        return false;
    }

}