<?php
/**
 * Created by IntelliJ IDEA.
 * User: marioeugenio
 * Date: 11/19/15
 * Time: 2:37 PM
 */

namespace Application\Service;

use Application\Entity\AtributoAndamento;
use Application\Service\Exception\BaseException;
use Doctrine\Common\Util\Debug;
use TheSeer\DirectoryScanner\Exception;

class Documento extends AbstractService {

	private $urlSei;

	/**
	 * @var string
	 */
	private $urlSoapEdoc;

	/**
	 * @var Usuario
	 */
	private $serviceUsuario;

    /** @var  Processo */
    private $serviceProcesso;

	const SIM = 'S';
	const NAO = 'N';

	public function setUrlSoapEdoc($url) {
		$this->urlSoapEdoc = $url;
	}

	public function setServiceUsuario($service) {
		$this->serviceUsuario = $service;
	}

    public function setServiceProcesso($service) {
        $this->serviceProcesso = $service;
    }

	private function checarDocumentoPublicado($documento) {
		$publicado = Documento::NAO;

		if ($documento instanceof Documento) {
			$publicacao = $this->getDefaultEntityManager()->getRepository('Application\Entity\Publicacao');

			$checarPublicado = $publicacao->pesquisarPublicacaoPorDocumento($documento);

			if (count($checarPublicado)) {
				$publicado = Documento::SIM;
			}
		}

		return $publicado;
	}

	public function listarDocumentos($procedimento, $limit, $offset) {
		$arrayResult         = array();
		$documentoRepository = $this->getDefaultEntityManager()->getRepository('Application\Entity\Documento');

		$result = $documentoRepository->listarDocumentos($procedimento, $limit, $offset);

		if (count($result['result'])) {
			$anexo      = $this->getDefaultEntityManager()->getRepository('Application\Entity\Anexo');
			$observacao = $this->getDefaultEntityManager()->getRepository('Application\Entity\Observacao');
			$protocolo  = $this->getDefaultEntityManager()->getRepository('Application\Entity\Protocolo');
			/** @var \Application\Entity\Documento $value */
			foreach ($result['result'] as $value) {
				$restrito = Documento::NAO;
                $sigiloso = Documento::NAO;

				$resultAnexo      = ($anexo->findOneBy(array('idProtocolo'      => $value->getIdDocumento(), 'sinAtivo'      => 'S')));
				$resultObservacao = ($observacao->findOneBy(array('idProtocolo' => $value->getIdDocumento())));
                /** @var Protocolo $resultProtocolo */
				$resultProtocolo  = ($protocolo->findOneBy(array('idProtocolo'  => $value->getIdDocumento())));

				if ($resultProtocolo->getStaNivelAcesso() == 1) {
					$restrito = Documento::SIM;
				}

                if ($resultProtocolo->getStaNivelAcesso() == 2) {
                    $sigiloso = Processo::SIM;
                }

				$mimetype = null;
				if ($resultAnexo) {
					$mimetype = $resultAnexo->getNome();
					$mimetype = substr($mimetype, strrpos($mimetype, '.')+1);
				}

				$arrayResult[] = array(
					"id"                   => $value->getIdDocumento(),
					"atributos"            => array(
						"idProcedimento"      => $value->getIdProcedimento()->getIdProcedimento(),
						"idProtocolo"         => $value->getIdDocumento(),
                        "protocoloFormatado"  => $resultProtocolo->getProtocoloFormatado(),
						"nome"                => $resultAnexo?$resultAnexo->getNome():null,
						"titulo"              => $value->getNumero(),
						"tipo"                => $value->getIdSerie()->getNome(),
						"mimeType"            => ($mimetype)?$mimetype:'html',
						"informacao"          => ($resultObservacao)?substr($resultObservacao->getDescricao(), 0, 250):null,
						"tamanho"             => ($resultAnexo)?$resultAnexo->getTamanho():null,
                        "idUnidade"           => $resultProtocolo->getIdUnidadeGeradora()->getIdUnidade(),
                        "siglaUnidade"        => $resultProtocolo->getIdUnidadeGeradora()->getSigla(),
						"status"              => array(
                            "sinBloqueado"      => $result['restrito'],
							"documentoSigiloso"  => $sigiloso,
							"documentoRestrito"  => $restrito,
							"documentoPublicado" => $this->checarDocumentoPublicado($value),
							"documentoAssinado"  => ($value->getCrcAssinatura())?$this::SIM:$this::NAO,
							"ciencia"            => ($value->getRelProtocoloProtocolo())?$value->getRelProtocoloProtocolo()->getSinCiencia():$this::NAO
						)
					)
				);
			}
		}

		return array('data' => $arrayResult, 'total' => $result['count']);
	}

	private function getMimetype($tipo) {
		$tipo = strtolower($tipo);

		switch ($tipo) {
			case "pdf":
				$ctype = "application/pdf";
				break;
			case "exe":
				$ctype = "application/octet-stream";
				break;
			case "zip":
				$ctype = "application/zip";
				break;
			case "doc":
				$ctype = "application/msword";
				break;
			case "xls":
				$ctype = "application/vnd.ms-excel";
				break;
			case "ppt":
				$ctype = "application/vnd.ms-powerpoint";
				break;
			case "gif":
				$ctype = "image/gif";
				break;
			case "png":
				$ctype = "image/png";
				break;
			case ("jpeg" || "jpg"):
				$ctype = "image/jpg";
				break;
			case "mp3":
				$ctype = "audio/mp3";
				break;
			case ("wav" || "wma"):
				$ctype = "audio/x-wav";
				break;
			case ("mpeg" || "mpg" || "mpe"):
				$ctype = "video/mpeg";
				break;
			case "mov":
				$ctype = "video/quicktime";
				break;
			case "avi":
				$ctype = "video/x-msvideo";
				break;
			case "src":
				$ctype = "plain/text";
				break;
			default:
				$ctype = "application/force-download";
		}

		return $ctype;
	}

	public function downloadAnexo($protocolo) {
		$_em = $this->getDefaultEntityManager();

		$sql  = 'SET TEXTSIZE 2147483647;';
		$conn = $_em->getConnection();
		$stmt = $conn->prepare($sql);
		$stmt->execute();

		$repAnexo     = $_em->getRepository('Application\Entity\Anexo');
		$result       = $repAnexo->pesquisarAnexoPorProtocolo($protocolo);
		$repDocumento = $_em->getRepository('Application\Entity\Documento');

		/** @var \Application\Entity\Documento $documento */
		$documento = $repDocumento->find($protocolo);

		if ($documento) {
			if ($documento->getConteudo()) {
                $html = $documento->getConteudoAssinatura();
                if (!$html) {
                    $html = $documento->getConteudo();
                }

				return array("html" => $html);
			}
		}

		if ($result) {
			/** @var Anexo $anexo */
			$anexo = $result[0];
			$date  = $anexo->getDthInclusao();

			$config = $this->getConfig();
			$urlSei = $config['url_sei'];

			$path = $urlSei.'/'.$date->format('Y/m/d').'/';
			$filename = $path.$anexo->getIdAnexo();

			if ($fd = fopen($filename, 'rb')) {
				$nome = $anexo->getNome();
				$tipo = substr($nome, strrpos($nome, '.') + 1);

				//$ctype = $this->getMimetype($tipo);
				$fsize = filesize($filename);

				header("Pragma: public");
				header("Expires: 0");
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename=\"" . $anexo->getNome() . "\"");
				//header("Content-Transfer-Encoding: binary");
				header("Content-Length: $fsize");

				fpassthru($fd);
				exit;
			}
		}

		throw new BaseException('Arquivo não encontrado');
	}

    public function assinarBloco($arrDodumento, $orgao, $cargo, $login, $senha, $usuario) {
        if (is_array($arrDodumento)) {
            foreach($arrDodumento as $documento) {
                $this->assinar($documento, $orgao, $cargo, $login, $senha, $usuario);
            }

            return true;
        }

        throw new BaseException('Coleção de documentos inválida');
    }

	public function assinar($documento, $orgao, $cargo, $login, $senha, $usuario) {
		$config  = $this->getConfig();
		$urlSoap = $config['soap']['wsdl_mobile'];

		$soap = new \SoapClient($urlSoap);

        $options = array(
            'idDocumento' => (int) $documento,
            'orgao' => (int) $orgao,
            'cargo' => "{$cargo}",
            'idAssinatura' => (int) $usuario,
            'usuario' => "{$login}",
            'senha' => "{$senha}");

		try {
            $return = $soap->__call('assinarDocumento', $options);

            return $return;
		} catch (\SoapFault $ex) {
			throw $ex;
		}
	}

	public function listarCienciaDocumento($protocolo) {
		$repositorio = $this->getAtributoAndamentoRepository();

        $tarefa = $this->getDefaultEntityManager()->getRepository('Application\Entity\Tarefa')->find(83);

        $result = $repositorio->pesquisarAtributoAndamentoPorProtocolo($protocolo, $tarefa);
        $arr = array();

        /** @var AtributoAndamento $atributo */
        foreach($result as $atributo) {
            $atividade = $atributo->getIdAtividade();

            $arr[] = array(
                'data' => $atividade->getDthAbertura()->format('d/m/Y'),
                'unidade' => $atividade->getIdUnidade()->getSigla(),
                'descricao' => $this->serviceProcesso->traduzirTemplate($atividade->getIdTarefa()->getNome(),$atividade),
								'nome' => $atividade->getIdUsuarioOrigem()->getSigla()
            );
        }   

        return $arr;

    }

    public function listarAssinaturasDocumento($documento) {
        $repositorio = $this->getDefaultEntityManager()->getRepository('Application\Entity\Documento');

        $result = $repositorio->find($documento);
        $arrResult = array();

        /** @var Assinatura $assinaturas */
        foreach($result->getAssinaturas() as $assinaturas) {
            $arrResult[] = array(
                'nome' => $assinaturas->getNome(),
                'cargo' => $assinaturas->getTratamento(),
                'unidade' => $assinaturas->getIdUnidade()->getSigla()
            );
        }

        return $arrResult;
    }

	public function cienciaDocumento($protocolo, $unidade, $usuario, $serviceUnidade, $serviceProtocolo, $serviceAtividade) {
		$repDocumento = $this->getDefaultEntityManager()->getRepository('Application\Entity\RelProtocoloProtocolo');
		$repTarefa = $this->getDefaultEntityManager()->getRepository('Application\Entity\Tarefa');

		$repUsuario = $this->getDefaultEntityManager()->getRepository('Application\Entity\Usuario');
		$unidade = $serviceUnidade->getRepository()->find($unidade);
		$usuario = $repUsuario->find($usuario);

		/** @var Protocolo $protocolo */
		$res = $repDocumento->findOneBy(array('idProtocolo2' => $protocolo));
		$prot = $serviceProtocolo->pesquisarProtocoloPorID($res->getIdProtocolo1());

		$atividade = new \Application\Entity\Atividade();
		$atividade->setIdUnidade($unidade);
		$atividade->setIdUsuarioVisualizacao($usuario);
		$atividade->setIdUsuario($usuario);
		$atividade->setIdUsuarioOrigem($usuario);
		$atividade->setIdUnidadeOrigem($unidade);
		$atividade->setDthAbertura(new \DateTime());
		$atividade->setIdTarefa($repTarefa->find(83));
		$atividade->setSinInicial('S');
		$atividade->setIdProtocolo($prot);

		$atividade->setTipoVisualizacao(0);

		$serviceAtividade->criarAtividade($atividade, 'DOCUMENTO', $protocolo);

		/** @var RelProtocoloProtocolo $documento */
		$documento = $repDocumento->findOneBy(array('idProtocolo2' => $protocolo));
		$documento->setSinCiencia(Documento::SIM);

		$this->getDefaultEntityManager()->persist($documento);
		$this->getDefaultEntityManager()->flush();


	}

	public function getAssinaturaRepository() {
		return $this->getDefaultEntityManager()->getRepository('Application\Entity\Assinatura');
	}

    public function getAtributoAndamentoRepository() {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\AtributoAndamento');
    }
}
