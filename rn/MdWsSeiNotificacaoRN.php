<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiNotificacaoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Método que realiza a notificação de um usuário
     * @param MdWsSeiNotificacaoDTO $notificacaoDTO
     */
    public function notificar(MdWsSeiNotificacaoDTO $notificacaoDTO)
    {
        $requestHeader = array(
            'Authorization: '.$notificacaoDTO->getStrChaveAutorizacao(),
            'Content-Type: application/json',
            'charset: utf-8'
        );
        $ch = curl_init($notificacaoDTO->getStrUrlServicoNotificacao());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            $requestHeader
        );

        $requestBody = array(
            'idApp' => $notificacaoDTO->getNumIdApp(),
            'stSaveMessage' => 0,
            'dsMessage' => $notificacaoDTO->getStrMensagem(),
            'dsResume' => $notificacaoDTO->getStrResumo(),
            'dsTitle' => $notificacaoDTO->getStrTitulo(),
            'dsIdentities' => array($notificacaoDTO->getStrIdentificadorUsuario()),
            'stNotify' => $notificacaoDTO->getBolNotificar() ? 1 : 0,
            'data' => $notificacaoDTO->getArrData()
        );

        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            json_encode(MdWsSeiRest::dataToUtf8($requestBody))
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $response = curl_exec($ch);
        if(!curl_errno($ch)){
            $info = curl_getinfo($ch);
            if($info['http_code'] != 200){
                throw new InfraException(MdWsSeiRest::dataToIso88591($response));
            }
        }else{
            throw new InfraException(MdWsSeiRest::dataToIso88591($response));
        }
    }


}