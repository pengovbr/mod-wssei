<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
 *
 * 01/07/2009 - criado por fbv@trf4.gov.br
 *
 *
 */

require_once dirname(__FILE__).'/../SEI.php';

class MobileWS extends InfraWS {

	public function getObjInfraLog() {
		return LogSEI::getInstance();
	}

	public function assinarDocumento($idDocumento, $orgao, $cargo, $idAssinatura, $usuario, $senha) {
		try {

			$this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService', 'Edoc'));

            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->limpar();

            InfraDebug::getInstance()->gravar(__METHOD__);
            InfraDebug::getInstance()->gravar('ID DOCUMENTO: '.$idDocumento);
            InfraDebug::getInstance()->gravar('ORGAO: '.$orgao);
            InfraDebug::getInstance()->gravar('CARGO: '.$cargo);
            InfraDebug::getInstance()->gravar('SIGLA USUARIO: '.$usuario);

            SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_EDOC, SessaoSEI::$UNIDADE_TESTE);

            $objAssinaturaDTO = new AssinaturaDTO();
            $objAssinaturaDTO->setStrStaFormaAutenticacao('S');
            $objAssinaturaDTO->setNumIdOrgaoUsuario($orgao);
            $objAssinaturaDTO->setNumIdContextoUsuario(null);
            $objAssinaturaDTO->setNumIdUsuario($idAssinatura);
            $objAssinaturaDTO->setStrSiglaUsuario($usuario);
            $objAssinaturaDTO->setStrSenhaUsuario($senha);
            $objAssinaturaDTO->setStrCargoFuncao($cargo);

            $arrIdDocumentosOrdenadosBloco = array($idDocumento);

            $objAssinaturaDTO->setArrObjDocumentoDTO(InfraArray::gerarArrInfraDTO('DocumentoDTO','IdDocumento',$arrIdDocumentosOrdenadosBloco));


			InfraDebug::getInstance()->gravar($objAssinaturaDTO->__toString());

			$objDocumentoRN = new DocumentoRN();
			$objDocumentoRN->assinar($objAssinaturaDTO);

			return true;

		} catch (Exception $e) {
			//LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
			$this->processarExcecao($e);
		}
	}

	public function confirmarAssinatura($IdDocumentoEdoc, $Cpf, $SiglaUsuario) {
		try {

			$this->validarAcessoAutorizado(ConfiguracaoSEI::getInstance()->getValor('HostWebService', 'Edoc'));

			InfraDebug::getInstance()->setBolLigado(false);
			InfraDebug::getInstance()->setBolDebugInfra(false);
			InfraDebug::getInstance()->limpar();

			InfraDebug::getInstance()->gravar(__METHOD__);
			InfraDebug::getInstance()->gravar('ID DOCUMENTO EDOC: '.$IdDocumentoEdoc);
			InfraDebug::getInstance()->gravar('CPF: '.$Cpf);
			InfraDebug::getInstance()->gravar('SIGLA USUARIO: '.$SiglaUsuario);

			SessaoSEI::getInstance(false)->simularLogin(SessaoSEI::$USUARIO_EDOC, SessaoSEI::$UNIDADE_TESTE);

			$objAssinaturaDTO = new AssinaturaDTO();
			$objAssinaturaDTO->setDblIdDocumentoEdoc($IdDocumentoEdoc);
			$objAssinaturaDTO->setDblCpf($Cpf);
			$objAssinaturaDTO->setStrSiglaUsuario($SiglaUsuario);

			InfraDebug::getInstance()->gravar($objAssinaturaDTO->__toString());

			$objDocumentoRN = new DocumentoRN();
			$objDocumentoRN->confirmarAssinatura($objAssinaturaDTO);

			//LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());

			return true;

		} catch (Exception $e) {
			$this->processarExcecao($e);
		}
	}
}

$servidorSoap = new SoapServer("mobile.wsdl", array('encoding' => 'ISO-8859-1'));

$servidorSoap->setClass("MobileWS");

//S� processa se acessado via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$servidorSoap->handle();
}
?>