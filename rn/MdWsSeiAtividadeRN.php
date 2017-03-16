<?
require_once dirname(__FILE__).'/../../../SEI.php';

class MdWsSeiAtividadeRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    private function traduzirTemplate(AtividadeDTO $atividadeDTO, ProtocoloDTO $protocoloDTO) {
        $arrParameters = ['DOCUMENTO', 'DOCUMENTOS', 'NIVEL_ACESSO', 'GRAU_SIGILO', 'HIPOTESE_LEGAL', 'DATA_AUTUACAO', 'TIPO_CONFERENCIA',
            'PROCESSO', 'USUARIO', 'USUARIOS', 'UNIDADE', 'BLOCO', 'DATA_HORA', 'USUARIO_ANULACAO', 'INTERESSADO', 'LOCALIZADOR', 'ANEXO',
            'USUARIO_EXTERNO_NOME', 'USUARIO_EXTERNO_SIGLA', 'DATA', 'DESTINATARIO_NOME', 'DESTINATARIO_EMAIL', 'DATA_VALIDADE', 'DIAS_VALIDADE',
            'MOTIVO', 'VEICULO', 'TIPO'];

        $strTemplate = $atividadeDTO->getStrNomeTarefa();

        if ($strTemplate) {
            foreach($arrParameters as $parameter) {
                $parameter = '@' . $parameter . '@';

                $restrito = 'n�o restrito';

                $nome = ($atividadeDTO->getNumIdUsuarioOrigem())? $atividadeDTO->getStrNomeUsuarioOrigem() : null;
                $sigla = ($atividadeDTO->getNumIdUsuarioOrigem())? $atividadeDTO->getStrSiglaUsuarioOrigem() : null;
                $usuarioAnulacao = ($atividadeDTO->getNumIdUsuarioConclusao())? $atividadeDTO->getStrNomeUsuarioConclusao() : null;
                $unidade = $atividadeDTO->getStrSiglaUnidade();
                $dataHora = ' '.$atividadeDTO->getDthAbertura();

                if ($protocoloDTO->getStrStaNivelAcessoLocal() == 1) {
                    $restrito = 'restrito';
                }

                if('@DOCUMENTO@' == $parameter) {
                    $strTemplate = str_replace($parameter, $protocoloDTO->getStrProtocoloFormatado(), $strTemplate);
                } elseif ('@DOCUMENTOS@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@NIVEL_ACESSO@' == $parameter) {
                    $strTemplate = str_replace($parameter, $restrito, $strTemplate);
                } elseif ('@GRAU_SIGILO@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@HIPOTESE_LEGAL@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@DATA_AUTUACAO@' == $parameter) {
                    $strTemplate = str_replace($parameter, $dataHora, $strTemplate);
                } elseif ('@TIPO_CONFERENCIA@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@PROCESSO@' == $parameter) {
                    $strTemplate = str_replace($parameter, $protocoloDTO->getStrProtocoloFormatado(), $strTemplate);
                } elseif ('@USUARIO@' == $parameter) {
                    $strTemplate = str_replace($parameter, $nome, $strTemplate);
                } elseif ('@USUARIOS@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@UNIDADE@' == $parameter) {
                    $strTemplate = str_replace($parameter, $unidade, $strTemplate);
                } elseif ('@BLOCO@' == $parameter) {
                    $blocoRN = new BlocoRN();
                    $blocoDTO = new BlocoDTO();
                    $blocoDTO->setNumIdUnidade($atividadeDTO->getNumIdUnidade());
                    $blocoDTO->setNumMaxRegistrosRetorno(1);
                    $blocoDTO->retStrDescricao();
                    $blocoResult = $blocoRN->listarRN1277($blocoDTO);
                    if (!empty($blocoResult)) {
                        $bloco = $blocoResult[0];
                        $strTemplate = str_replace($parameter, $bloco->getStrDescricao(), $strTemplate);
                    }
                } elseif ('@DATA_HORA@' == $parameter) {
                    $strTemplate = str_replace($parameter, $dataHora, $strTemplate);
                } elseif ('@DATA@' == $parameter) {
                    $strTemplate = str_replace($parameter, $dataHora, $strTemplate);
                } elseif ('@USUARIO_ANULACAO@' == $parameter) {
                    $strTemplate = str_replace($parameter, $usuarioAnulacao, $strTemplate);
                } elseif ('@INTERESSADO@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@LOCALIZADOR@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@ANEXO@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@USUARIO_EXTERNO_NOME@' == $parameter) {
                    $strTemplate = str_replace($parameter, $nome, $strTemplate);
                } elseif ('@USUARIO_EXTERNO_SIGLA@' == $parameter) {
                    $strTemplate = str_replace($parameter, $sigla, $strTemplate);
                } elseif ('@DESTINATARIO_NOME@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@DESTINATARIO_EMAIL@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@DATA_VALIDADE@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@DIAS_VALIDADE@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@MOTIVO@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@VEICULO@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                } elseif ('@TIPO@' == $parameter) {
                    $strTemplate = str_replace($parameter, '', $strTemplate);
                }
            }
        }

        return $strTemplate;
    }

    /**
     * Para efetuar a paginacao e necessario passar dentro do AtividadeDTO os parametros abaixo:
     *      -- setNumPaginaAtual($offset)
     *      -- setNumMaxRegistrosRetorno($limit)
     * @param AtividadeDTO $atividadeDTO
     * @return array
     * @throws InfraException
     */
    protected function listarAtividadesProcessoConectado(AtividadeDTO $atividadeDTO){
        try{
            if(!$atividadeDTO->isSetDblIdProtocolo()){
                throw new InfraException('O protocolo deve ser informado!');
            }
            if(!$atividadeDTO->isSetNumIdUnidade()){
                $atividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            }
            $protocoloRN = new ProtocoloRN();
            $protocoloDTO = new ProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($atividadeDTO->getDblIdProtocolo());
            $protocoloDTO->retStrStaNivelAcessoLocal();
            $protocoloDTO->retStrProtocoloFormatado();
            $resultProtocolo = $protocoloRN->listarRN0668($protocoloDTO);
            $protocoloDTO = $resultProtocolo[0];

            $atividadeDTO->retDblIdProtocolo();
            $atividadeDTO->retNumIdUnidade();
            $atividadeDTO->retDthAbertura();
            $atividadeDTO->retStrNomeTarefa();
            $atividadeDTO->retNumIdAtividade();
            $atividadeDTO->retNumIdUsuarioOrigem();
            $atividadeDTO->retStrSiglaUsuarioOrigem();
            $atividadeDTO->retStrSiglaUnidade();
            $atividadeDTO->retStrNomeUsuarioOrigem();
            $atividadeDTO->retNumIdUsuarioConclusao();
            $atividadeDTO->retStrNomeUsuarioConclusao();
            $atividadeRN = new AtividadeRN();
            $result = $atividadeRN->listarRN0036($atividadeDTO);
            if (!empty($result)) {
                foreach($result as $value) {
                    $dateTime = explode(' ', $value->getDthAbertura());
                    $informacao = $this->traduzirTemplate($value, $protocoloDTO);
                    $arrayResult[] = [
                        "id" => $value->getNumIdAtividade(),
                        "atributos" => [
                            "idProcesso" => $value->getDblIdProtocolo(),
                            "usuario" => ($value->getNumIdUsuarioOrigem())? $value->getStrSiglaUsuarioOrigem() : null,
                            "data" => $dateTime[0],
                            "hora" => $dateTime[1],
                            "unidade" => $value->getStrSiglaUnidade(),
                            "informacao" => $informacao
                        ]
                    ];
                }
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $arrayResult, $atividadeDTO->getNumTotalRegistros());
        }catch (Exception $e){
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }



}