<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiProcessoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Metodo que traduz o template pelo id da atividade e a string do template
     * @param MdWsSeiProcessoDTO $mdWsSeiProcessoDTO
     * @return string
     */
    protected function traduzirTemplateConectado(MdWsSeiProcessoDTO $mdWsSeiProcessoDTO){
        $strTemplate = $mdWsSeiProcessoDTO->getStrTemplate();
        if ($strTemplate) {
            $atributoAndamentoRN = new AtributoAndamentoRN();
            $atributoAndamentoDTOConsulta = new AtributoAndamentoDTO();
            $atributoAndamentoDTOConsulta->retTodos();
            $atributoAndamentoDTOConsulta->setNumIdAtividade($mdWsSeiProcessoDTO->getNumIdAtividade());
            $ret = $atributoAndamentoRN->listarRN1367($atributoAndamentoDTOConsulta);
            $atividadeDTO = new AtividadeDTO();
            $atividadeDTO->setNumIdAtividade($mdWsSeiProcessoDTO->getNumIdAtividade());
            $atividadeDTO->retDblIdProtocolo();
            $atividadeDTO->retStrNomeTarefa();
            $atividadeRN = new AtividadeRN();
            $atividadeDTO = $atividadeRN->consultarRN0033($atividadeDTO);
            $protocoloDTO = new ProtocoloDTO();
            $protocoloDTO->retStrStaGrauSigilo();
            $protocoloDTO->setDblIdProtocolo($atividadeDTO->getDblIdProtocolo());
            $protocoloRN = new ProtocoloRN();
            $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);

            if ($ret) {
                /** @var AtributoAndamentoDTO $atributoAndamentoDTO */
                foreach($ret as $atributoAndamentoDTO) {
                    $valor = $atributoAndamentoDTO->getStrValor();


                    if (strripos($valor, '¥')) {
                        $valor = str_replace('¥', ' - ', $atributoAndamentoDTO->getStrValor());
                    }

                    $strTemplate = str_replace('@' . $atributoAndamentoDTO->getStrNome() . '@', $valor, $strTemplate);

                    $sigilo = ($protocoloDTO->getStrStaGrauSigilo())? 'sigiloso': 'nao sigiloso';

                    $strTemplate = str_replace('@GRAU_SIGILO@', $sigilo, $strTemplate);
                    $strTemplate = str_replace('@HIPOTESE_LEGAL@', '', $strTemplate);
                }
            }
            //O Core do SEI faz esta limpeza...
            $strTemplate = str_replace(
                array(
                    '@NIVEL_ACESSO@',
                    '@GRAU_SIGILO@',
                    '@TIPO_CONFERENCIA@',
                    '@DATA_AUTUACAO@',
                    '@HIPOTESE_LEGAL@',
                    '@VISUALIZACAO@'
                ),
                '',
                $strTemplate
            );

            $strTemplate = str_replace('¥', ' - ', $strTemplate);
        }

        return $strTemplate;
    }

}