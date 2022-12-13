<?
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiAnexoRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    /**
     * Método que processo o upload de arquivos do slim
     * @param \Slim\Http\UploadedFile $uploadedFile
     * @param string $strDirUpload
     * @param bool $bolArquivoTemporarioIdentificado
     * @return AnexoDTO
     * @throws Exception
     */
    public static function processarUploadSlim(\Slim\Http\UploadedFile $uploadedFile, $strDirUpload = DIR_SEI_TEMP, $bolArquivoTemporarioIdentificado = false)
    {
        $strNomeArquivo = str_replace(chr(0), '', $uploadedFile->getClientFilename());
        $arrStrNome = explode('.', $strNomeArquivo);
        if (count($arrStrNome)<2) {
            throw new Exception('Nome do arquivo não possui extensão.');
        } else {
            if (in_array(str_replace(' ', '', InfraString::transformarCaixaBaixa($arrStrNome[count($arrStrNome) - 1])), array('php', 'php3', 'php4', 'phtml', 'sh', 'cgi'))) {
                throw new Exception('Extensão de arquivo nao permitida.');
            } else {
                if ($uploadedFile->getError() != UPLOAD_ERR_OK) {
                    switch ($uploadedFile->getError()) {
                        case UPLOAD_ERR_INI_SIZE:
                            throw new Exception($strNomeArquivo.' excedeu o limite de '. ini_get('upload_max_filesize') . 'b permitido pelo servidor.');
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            throw new Exception('Apenas uma parte do arquivo foi transferida.');
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new Exception('Arquivo não foi transferido.');
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            throw new Exception('Diretório temporário para transferência não encontrado.');
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            throw new Exception('Erro gravando dados no servidor.');
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            throw new Exception('Transferência interrompida.');
                            break;
                        default:
                            throw new Exception('Erro desconhecido tranferindo arquivo [' . $uploadedFile->getError() . '].');
                            break;
                    }
                } else {
                    $anexoDTO = new AnexoDTO();
                    $bolConteudoPermitido = true;
                    if (function_exists('finfo_open')) {
                        $bolConteudoPermitido = InfraUtil::verificarConteudoPermitidoArquivo($uploadedFile->file);
                    }

                    if (!$bolConteudoPermitido) {
                        throw new Exception('Tipo de arquivo não permitido.');
                    } else {
                        $objSessao = SessaoSEI::getInstance();

                        if ($objSessao !== null) {
                            $anexoDTO->setNumIdUsuario($objSessao->getNumIdUsuario());
                            $anexoDTO->setNumIdUnidade($objSessao->getNumIdUnidadeAtual());
                            $strUsuario = $objSessao->getStrSiglaUsuario();
                        } else {
                            $strUsuario = 'anonimo';
                        }

                        $numTimestamp = time();

                        if ($bolArquivoTemporarioIdentificado) {
                            $strNomeArquivoUpload = InfraUtil::montarNomeArquivoUpload($strUsuario, $numTimestamp, $strNomeArquivo);
                        } else {
                            $strNomeArquivoUpload = md5($strUsuario . mt_rand() . $numTimestamp . mt_rand() . $strNomeArquivo . uniqid(mt_rand(), true));
                        }

                        if (file_exists($strDirUpload . '/' . $strNomeArquivoUpload)) {
                            throw new Exception('Arquivo "' . $strNomeArquivoUpload . '" já existe no diretório de upload.');
                        } else {
                            if (!move_uploaded_file($uploadedFile->file, $strDirUpload . '/' . $strNomeArquivoUpload)) {
                                throw new Exception('Erro movendo arquivo para o diretório de upload.');
                            } else {
                                $anexoDTO->setNumIdAnexo($strNomeArquivoUpload);
                                $anexoDTO->setStrNome($strNomeArquivo);
                                $anexoDTO->setNumTamanho($uploadedFile->getSize());
                                $anexoDTO->setDthInclusao(InfraData::getStrDataHoraAtual());

                                if (!chmod($strDirUpload . '/' . $strNomeArquivoUpload, 0660)) {
                                    throw new Exception('Erro alterando permissões do arquivo no diretório de upload.');
                                }
                            }
                        }

                        return $anexoDTO;
                    }
                }
            }
        }

    }


}