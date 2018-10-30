<?
if($_REQUEST['key'] != 'lalilulelo'){
    return false;
}
require_once __DIR__.'/../../SEI.php';

ini_set('xdebug.var_display_max_depth', 100);
ini_set('xdebug.var_display_max_children', 100);
ini_set('xdebug.var_display_max_data', 2048);
echo '<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>';

$b = new MdWsSeiUsuarioRN();
$token = $b->tokenEncode('teste', 'teste');
echo 'Token: ';
echo $token;
echo '<BR>';
$b->autenticarToken($token);

$arrProcessosVisitados = SessaoSEI::getInstance()->getAtributo('PROCESSOS_VISITADOS_' . SessaoSEI::getInstance()->getStrSiglaUnidadeAtual());
var_dump($arrProcessosVisitados);
exit;

require_once dirname(__FILE__).'/vendor/autoload.php';



class TesteAtividade {

    public function listarAtividadesProcessoConectado(){
        $rn = new MdWsSeiAtividadeRN();
        $dto = new AtividadeDTO();
        $dto->setDblIdProtocolo(1);
        $dto->setNumMaxRegistrosRetorno(10);
        $dto->setNumPaginaAtual(0);

        var_dump($rn->listarAtividades($dto));
    }
    public function lancarAndamentoProcessoControlado(){
        $rn = new MdWsSeiAtividadeRN();
        $dto = $rn->encapsulaLancarAndamentoProcesso(array(
            'protocolo' => 30,
            'descricao' => 'La vamos nós!'
        ));
        var_dump($rn->lancarAndamentoProcesso($dto));
    }

}

class TesteBloco {

    public function listarBlocoConectado(){
        $rn = new MdWsSeiBlocoRN();
        $dto = new BlocoDTO();
        var_dump($rn->listarBloco($dto));
    }

    public function listarDocumentosBlocoConectado(){
        $rn = new MdWsSeiBlocoRN();
        $dto = new BlocoDTO();
        $dto->setNumIdBloco(1);
        var_dump($rn->listarDocumentosBloco($dto));
    }

    public function cadastrarAnotacaoBlocoControlado(){
        $rn = new MdWsSeiBlocoRN();
        $dto = new RelBlocoProtocoloDTO();
        $dto->setNumIdBloco(1);
        $dto->setDblIdProtocolo(4);
        $dto->setStrAnotacao('Teste');
        var_dump($rn->cadastrarAnotacaoBloco($dto));
    }

}

class TesteDocumento {

    public function listarCienciaDocumentoConectado(){
        $rn = new MdWsSeiDocumentoRN();
        $dto = new MdWsSeiProcessoDTO();
        $dto->setStrValor('0000007');
        var_dump($rn->listarCienciaDocumento($dto));
    }

    public function listarAssinaturasDocumentoConectado(){
        $rn = new MdWsSeiDocumentoRN();
        $dto = new DocumentoDTO();
        $dto->setDblIdDocumento(3);
        var_dump($rn->listarAssinaturasDocumento($dto));
    }
    public function darCienciaControlado(){
        $dto = new DocumentoDTO();
        $dto->setDblIdDocumento(18);
        $rn = new MdWsSeiDocumentoRN();
        var_dump($rn->darCiencia($dto));
    }

    public function assinarDocumentoControlado(){
        $dto = new AssinaturaDTO();
        $dto->setStrSenhaUsuario('teste');
        $dto->setStrSiglaUsuario('teste');
        $dto->setNumIdUsuario(100000001);
        $dto->setNumIdContextoUsuario(null);
        $dto->setStrStaFormaAutenticacao(AssinaturaRN::$TA_SENHA);
        $dto->setStrCargoFuncao('Fiscal de Contrato - Administrativo');
        $dto->setNumIdOrgaoUsuario(0);
        $doc1 = new DocumentoDTO();
        $doc1->setDblIdDocumento(19);
        $doc2 = new DocumentoDTO();
        $doc2->setDblIdDocumento(20);
        $documentos = array(
            $doc1,
            $doc2
        );
        $dto->setArrObjDocumentoDTO($documentos);
        $rn = new MdWsSeiDocumentoRN();
        var_dump($rn->assinarDocumento($dto));
    }

    public function apiAssinarDocumentos(){
        $arrDocumentos = array(21, 22);
        $rn = new MdWsSeiDocumentoRN();
        var_dump($rn->apiAssinarDocumentos($arrDocumentos, 0, 'Fiscal de Contrato - Administrativo', 'teste', 'teste', 100000001));
    }

    public function apiAssinarDocumento(){
        $rn = new MdWsSeiDocumentoRN();
        var_dump($rn->apiAssinarDocumento(22, 0, 'Fiscal de Contrato - Administrativo', 'teste', 'teste', 100000001));
    }

    public function downloadAnexoConectado(){
        $rn = new MdWsSeiDocumentoRN();
        $dto = new ProtocoloDTO();
        $dto->setDblIdProtocolo(36);
        var_dump($rn->downloadAnexo($dto));
    }

}


class TesteProcedimento {

    public function listarUnidadesProcessoConectado(){
        $rn = new MdWsSeiProcedimentoRN();
        $dto = new ProtocoloDTO();
        $dto->setDblIdProtocolo(15);
        var_dump($rn->listarUnidadesProcesso($dto));
    }

    public function removerSobrestamentoProcessoControlado(){
        $rn = new MdWsSeiProcedimentoRN();
        $dto = new ProcedimentoDTO();
        $dto->setDblIdProcedimento(15);
        var_dump($rn->removerSobrestamentoProcesso($dto));
    }

    public function listarCienciaProcessoConectado(){
        $rn = new MdWsSeiProcedimentoRN();
        $dto = new ProtocoloDTO();
        $dto->setDblIdProtocolo(15);
        var_dump($rn->listarCienciaProcesso($dto));
    }

    public function listarSobrestamentoProcessoConectado(){
        $rn = new MdWsSeiProcedimentoRN();
        $dto = new AtividadeDTO();
        $dto->setDblIdProtocolo(15);
        var_dump($rn->listarSobrestamentoProcesso($dto));
    }

    public function listarProcessosConectado(){
        $rn = new MdWsSeiProcedimentoRN();
        $dto = new MdWsSeiProtocoloDTO();
        $dto->setNumIdUsuarioAtribuicaoAtividade('100000001');
        $dto->setNumIdUnidadeAtividade('110000001');
        $dto->setStrSinTipoBusca(MdWsSeiProtocoloDTO::SIN_TIPO_BUSCA_M);
        $dto->setNumPaginaAtual(0);
        $dto->setNumMaxRegistrosRetorno(10);

        var_dump($rn->listarProcessos($dto));
    }

    public function pesquisarProcedimentoConectado(){
        $rn = new MdWsSeiProcedimentoRN();
        $dto = new MdWsSeiProtocoloDTO();
        $dto->setNumIdGrupoAcompanhamentoProcedimento(1);
        $dto->setStrProtocoloFormatadoPesquisa('000001');
        $dto->setNumPaginaAtual(0);
        $dto->setNumMaxRegistrosRetorno(10);

        var_dump($rn->pesquisarProcedimento($dto));
    }

    public function darCienciaControlado(){
        $rn = new MdWsSeiProcedimentoRN();
        $dto = new ProcedimentoDTO();
        $dto->setDblIdProcedimento(1);
        var_dump($rn->darCiencia($dto));
    }


    public function enviarProcessoControlado(){
        $rn = new MdWsSeiProcedimentoRN();
        $dto = $rn->encapsulaEnviarProcessoEntradaEnviarProcessoAPI(
            array(
                'numeroProcesso' => '99990.000009/2017-29',
                'unidadesDestino' => '110000002,110000003',
                'sinManterAbertoUnidade' => 'S',
                'sinRemoverAnotacao' => 'S',
                'dataRetornoProgramado' => '21/03/2017'
            )
        );
        var_dump($rn->enviarProcesso($dto));
    }

    public function concluirProcessoControlado(){
        $api = new EntradaConcluirProcessoAPI();
        $api->setProtocoloProcedimento('99990.000009/2017-29');
        $rn = new MdWsSeiProcedimentoRN();
        var_dump($rn->concluirProcesso($api));
    }

    public function listarProcedimentoAcompanhamentoConectado(){
        $dto = new MdWsSeiProtocoloDTO();
        $rn = new MdWsSeiProcedimentoRN();
        $dto->setNumIdUsuarioGeradorAcompanhamento('100000001');
        //$dto->setNumidGrupoAcompanhamentoProcedimento(1);
        $dto->setNumPaginaAtual(0);
        $dto->setNumMaxRegistrosRetorno(10);

        var_dump($rn->listarProcedimentoAcompanhamento($dto));
    }

    //o----- antigos


    public function atribuirProcessoControlado(){
        $api = new EntradaAtribuirProcessoAPI();
        $api->setProtocoloProcedimento('99990000001201762');
        $api->setIdUsuario('100000001');
        $rn = new MdWsSeiProcedimentoRN();
        var_dump($rn->atribuirProcesso($api));
    }
}

class TesteGrupoAcompanhamento {

    public function listarGrupoAcompanhamentoConectado(){
        $dto = new GrupoAcompanhamentoDTO();
        $dto->setNumMaxRegistrosRetorno(10);
        $dto->setNumPaginaAtual(0);
        $dto->setNumIdUnidade('110000001');
        $rn = new MdWsSeiGrupoAcompanhamentoRN();
        var_dump($rn->listarGrupoAcompanhamento($dto));
    }

    public function cadastrarAcompanhamentoControlado(){
        $rn = new MdWsSeiAcompanhamentoRN();
        $dto = $rn->encapsulaAcompanhamento(
            array(
                'protocolo' => 25,
                'unidade' => 110000001,
                'grupo' => 1,
                'usuario' => 100000001,
                'observacao' => 'acompanhar!',
            )
        );
        var_dump($rn->cadastrarAcompanhamento($dto));
    }
}

class TesteUnidade {

    public function pesquisarUnidadeConectado(){
        $rn = new MdWsSeiUnidadeRN();
        $dto = new UnidadeDTO();
        $dto->setStrSigla('teste');
        var_dump($rn->pesquisarUnidade());
    }
}

class TesteRetornoProgramado {

    public function agendarRetornoProgramadoControlado(){
        $post = array(
            'dtProgramada' => '28/09/2017',
            'unidade' => '110000001',
            'usuario' => '100000001',
            'atividadeEnvio' => 1
        );
        $rn = new MdWsSeiRetornoProgramadoRN();
        $dto = $rn->encapsulaRetornoProgramado($post);
        var_dump($rn->agendarRetornoProgramado($dto));
    }
}




//o-----
class TesteOrgao {

    public function listarOrgaoConectado(){
        $orgaoDTO = new OrgaoDTO();
        $orgaoDTO->setNumMaxRegistrosRetorno(10);
        $orgaoDTO->setNumPaginaAtual(0);
        $mdUnidade = new MdWsSeiOrgaoRN();
        var_dump($mdUnidade->listarOrgao($orgaoDTO));
    }
}

class TesteObservacao {

    public function criarObservacaoControlado(){
        $post = array(
            'unidade' => '110000001',
            'descricao' => 'dsadsadas dsa',
            'protocolo' => 1
        );
        $rn = new MdWsSeiObservacaoRN();
        $dto = $rn->encapsulaObservacao($post);
        var_dump($rn->criarObservacao($dto));
    }
}


class TesteAnotacao {

    public function cadastrarAnotacaoControlado(){
        $post = array(
            'unidade' => '110000001',
            'descricao' => 'aaa nov',
            'protocolo' => 1,
            'usuario' => '100000001',
            'prioridade' => 'S'
        );
        $rn = new MdWsSeiAnotacaoRN();
        $dto = $rn->encapsulaAnotacao($post);
        var_dump($rn->cadastrarAnotacao($dto));
    }
}

class TesteUsuario {

    public function listarUsuariosConectado(){
        $rn = new MdWsSeiUsuarioRN();
        $dto = new UnidadeDTO();
        //$dto->setNumIdUnidade(110000001);
        var_dump($rn->listarUsuarios($dto));
    }

    public function autenticarWSDL(){
        $login = 'teste';
        $senha = 'teste';
        for ($i = 0; $i < strlen($senha); $i++) {
            $senha[$i] = ~$senha[$i];
        }
        $pass = base64_encode($senha);
        $soap  = new \SoapClient('http://localhost/sip/controlador_ws.php?servico=wsdl', array('encoding'=>'ISO-8859-1'));
        /*
        $loginData = $soap->autenticar(
            0,
            null,
            $login,
            $pass,
            ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SiglaSistema'),
            ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'SiglaOrgaoSistema')
        );
        */
        //$ret = $soap->validarLogin($loginData->id_login, $loginData->id_sistema, $loginData->id_usuario, $loginData->hash_agente);
        //var_dump($ret);

        $b = new MdWsSeiUsuarioRN();
        $token = $b->tokenEncode('teste', 'teste');
        var_dump($b->autenticarToken($token));

    }
}

class TesteAssinante {

    public function listarAssinanteConectado(){
        $dto = new AssinanteDTO();
        $dto->setNumMaxRegistrosRetorno(3);
        $dto->setNumPaginaAtual(0);
        $dto->setNumIdUnidade('110000001');
        $rn = new MdWsSeiAssinanteRN();
        var_dump($rn->listarAssinante($dto));
    }
}



if($_REQUEST['controller'] && $_REQUEST['action']){
    try{
        $controller = new $_REQUEST['controller'];
        echo 'Resposta teste <b>'.$_REQUEST['controller'].'->'.$_REQUEST['action'].'()</b>:';
        $controller->$_REQUEST['action']();
    }catch (Exception $e){
        var_dump($e);
    }
}