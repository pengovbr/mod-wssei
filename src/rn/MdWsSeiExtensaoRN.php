<?php
require_once DIR_SEI_WEB . '/SEI.php';


class MdWsSeiExtensaoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Retorna os parametros para upload de arquivo
     * @return array
     */
    protected function retornarParametrosUploadConectado()
    {
        try{
            $infraParametro = new InfraParametro(BancoSEI::getInstance());
            /** Acessa as configurações do sistema para retornar o tamanho máximo para upload de documentos */
            $numTamMbDocExterno = $infraParametro->getValor('SEI_TAM_MB_DOC_EXTERNO');
            if (InfraString::isBolVazia($numTamMbDocExterno) || !is_numeric($numTamMbDocExterno)){
                throw new InfraException('Valor do parâmetro SEI_TAM_MB_DOC_EXTERNO inválido.');
            }

            /**'Acessa as configurações do sistema para retornar se será realizada a validação de extensões */
            $bolValidarExtensaoArq = $infraParametro->getValor('SEI_HABILITAR_VALIDACAO_EXTENSAO_ARQUIVOS');

            $arquivoExtensaoDTO = new ArquivoExtensaoDTO();
            $arquivoExtensaoDTO->retNumTamanhoMaximo();
            $arquivoExtensaoDTO->retStrExtensao();
            $arquivoExtensaoDTO->retStrDescricao();
            $arquivoExtensaoDTO->setOrdStrExtensao(InfraDTO::$TIPO_ORDENACAO_ASC);
            $arquivoExtensaoRN = new ArquivoExtensaoRN();
            /** Acessa o componente SEI para listar as extensões e seus tamanhos máximos permitidos */
            $ret = $arquivoExtensaoRN->listar($arquivoExtensaoDTO);

            $arrExtensoes = array();
            /** @var ArquivoExtensaoDTO $arquivoExtensaoDTO */
            foreach($ret as $arquivoExtensaoDTO){
                $arrExtensoes[] = array(
                    'extensao' => InfraString::transformarCaixaBaixa($arquivoExtensaoDTO->getStrExtensao()),
                    'tamanho' => ($arquivoExtensaoDTO->getNumTamanhoMaximo() ?: $numTamMbDocExterno),
                );
            }

            $result = array(
                'extensoes' => $arrExtensoes,
                'tamanhoDocDefault' => $numTamMbDocExterno,
                'validarExtensoes' => ($bolValidarExtensaoArq == '1' ? true : false),
                'info' => 'Tamanhos em Mb'
            );
            
            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

}