<?php

namespace Application\Service;

use Application\Entity\Anotacao;
use Application\Entity\Atividade;
use Application\Entity\AtributoAndamento;
use Application\Entity\Repository\AnotacaoRepository;
use Application\Entity\RetornoProgramado;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use Application\Entity\Bloco;
use Application\Entity\Procedimento;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Validator\Date;

class Processo extends AbstractService
{

    private $urlSoapSei;

    const SIM = 'S';
    const NAO = 'N';

    const SISTEMA = 'SEI';
    const ACTION_CONCLUIR = 'CONCLUIR_PROCESSO';
    const ACTION_ATRIBUIR = 'ATRIBUIR_PROCESSO';
    const ACTION_ENVIAR = 'ENVIAR_PROCESSO';
    const ACTION_LISTAR_USUARIO = 'LISTAR_USUARIOS';
    const TAREFA_SOBRESTAR = 20;
    const TAREFA_SOBRESTAR_CANCELAR = 21;

    /** @var  Protocolo */
    private $serviceProtocolo;
    /** @var  Atividade */
    private $serviceAtividade;

    public function setServiceProtocolo($service) {
        $this->serviceProtocolo = $service;
    }

    public function setServiceAtividade($service) {
        $this->serviceAtividade = $service;
    }

    public function setUrlSoapSEI($url) {
        $this->urlSoapSei = $url;
    }

    public function checarProcessoUnidades($protocolo)
    {
        $relProtProtocolo = $this->getDefaultEntityManager()->getRepository('Application\Entity\RelProtocoloProtocolo');
        $return = $relProtProtocolo->pesquisarProtocoloAtivo($protocolo);

        $arrReturn = array();
        foreach ($return as $value) {

            $arrReturn = array(
                'processo' => $value['idProtocolo1'],
                'unidades' => $value['1']
            );
        }

        return ($arrReturn);
    }

    public function pesquisarProcessoUnidades($protocolo)
    {
        $relProtProtocolo = $this->getDefaultEntityManager()->getRepository('Application\Entity\Atividade');
        $return = $relProtProtocolo->pesquisarProtocoloUnidades($protocolo);
        $arrReturn = array();
        foreach ($return as $value) {

            $arrReturn['unidades'][] = $value->getIdUnidade()->getIdUnidade();
        }

        return ($arrReturn);
    }

    public function checarSobrestamento($protocolo, $unidade)
    {
        $relProtProtocolo = $this->getDefaultEntityManager()->getRepository('Application\Entity\Atividade');
        $return = $relProtProtocolo->perquisarAtividadeSobrestamento($protocolo, $unidade);

        return ($return);
    }

    private function checarDocumentoPublicado($listaDocumentos) {
        $documentoPublicado = Processo::NAO;

        if (count($listaDocumentos)) {
            $publicacao = $this->getDefaultEntityManager()->getRepository('Application\Entity\Publicacao');

            foreach ($listaDocumentos as $documento) {
                $checarPublicado = $publicacao->pesquisarPublicacaoPorDocumento($documento);

                if (count($checarPublicado)) {
                    $documentoPublicado = Processo::SIM;
                }
            }
        }

        return $documentoPublicado;
    }

    private function checarAnotacao($protocolo, $unidade) {
        $docAnotacao = Processo::NAO;
        $prioridade = Processo::NAO;
        $checarAnotacao = array();

        if ($protocolo instanceof Protocolo) {
            /** @var AnotacaoRepository $anotacao */
            $anotacao = $this->getDefaultEntityManager()->getRepository('Application\Entity\Anotacao');
            $checarAnotacao = $anotacao->pesquisarAnotacaoPorProtocolo($protocolo, $unidade);

            if (count($checarAnotacao)) {
                /** @var Anotacao $result */
                foreach($checarAnotacao as $result) {
                    $prioridade = $result['sinPrioridade'];
                }

                $docAnotacao = Processo::SIM;
            }
        }

        return array('ANOTACAO' => $docAnotacao, 'PRIORIDADE' => $prioridade, 'ANOTACOES' => $checarAnotacao);
    }

    private function checarRetornoProgramado($protocolo, $unidade)
    {
        $retProgramado = Processo::NAO;
        $expirado = Processo::NAO;
        $data = array();

        $atividade = $this->getDefaultEntityManager()->getRepository('Application\Entity\Atividade');
        $atividades = $atividade->findBy(array('idProtocolo' => $protocolo, 'idUnidade' => $unidade));

        if (count($atividades)) {
            foreach ($atividades as $at) {
                $retorno = $this->getDefaultEntityManager()->getRepository('Application\Entity\RetornoProgramado');
                $result = $retorno->pesquisarRetornoProgramadoEnviado($at);

                if (count($result)) {
                    /** @var RetornoProgramado $retonoProgramado */
                    foreach ($result as $retonoProgramado) {
                        $now = new \Datetime();
                        $expirado = ($retonoProgramado->getDtaProgramada()->getTimestamp() < $now->getTimestamp())? Processo::SIM : Processo::NAO;
                        $retProgramado = Processo::SIM;
                        $data = array(
                            'date' => $retonoProgramado->getDtaProgramada()->format('d/m/Y'),
                            'unidade' => $retonoProgramado->getIdUnidade()->getSigla()
                        );
                        continue;
                    }
                }
                if ($retProgramado == Processo::SIM) {
                    continue;
                }
            }

            return array('retornoProgramado' => $retProgramado, 'expirado' => $expirado, 'data' => $data);
        }

        return  array('retornoProgramado' => $retProgramado, 'expirado' => $expirado, 'data' => $data);
    }

    public function pesquisarProcesso ($nprotocolo, $idProtocolo, $limit, $offset) {
        $arrayResult =  array();
        $protocolo = $this->getDefaultEntityManager()->getRepository('Application\Entity\Procedimento');

        $nprotocolo = intval(preg_replace('/[^0-9]+/', '', $nprotocolo), 10);

        $result = $protocolo->pesquisarProcesso($nprotocolo, $idProtocolo, $limit, $offset);

        if (count($result['result'])) {
            foreach($result['result'] as $value) {
                $atividades = $value->getProtocolo()->getAtividades();
                $novo = Processo::NAO;
                $restrito = Processo::NAO;
                $retornoProgramado = Processo::NAO;
                $retornoProgramadoData = array();
                $expirado = Processo::NAO;
                $usuarioVisualizacao = null;
                $usuarioAtribuido = null;
                $tipoVisualizacao = Processo::NAO;

                if (count($atividades)  > 0) {
                    $atividade = $atividades[(count($atividades) - 1)];

                    if (count($atividades) == 1) {
                        if ($atividade->getIdTarefa()->getIdTarefa() == 1) {
                            $novo = Processo::SIM;
                        }
                    }

                    foreach ($atividades as $atividadeEntity) {
                        $tipoVisualizacao = ($atividadeEntity->getTipoVisualizacao() == 0)? Processo::SIM : Processo::NAO;

                        if ($atividadeEntity->getIdUsuarioConclusao()) {
                            if ($atividadeEntity->getIdUsuarioConclusao()->getId() == $usuario)
                            {
                                $usuarioVisualizacao = Processo::SIM;
                            }
                        }

                        $usuarioAtribuido = ($atividadeEntity->getIdUsuarioAtribuicao())?
                            $atividadeEntity->getIdUsuarioAtribuicao()->getNome() : null;

                    }
                }

                if ($value->getProtocolo()->getStaNivelAcesso() == 1) {
                    $restrito = Processo::SIM;
                }

                $unidade = $value->getProtocolo()->getIdUnidadeGeradora()->getIdUnidade();
                $checkRetornoProgramado = $this->checarRetornoProgramado($value->getProtocolo(), $unidade);

                if ($checkRetornoProgramado['retornoProgramado'] == Processo::SIM) {
                    $retornoProgramado = $checkRetornoProgramado['retornoProgramado'];
                    $expirado = $checkRetornoProgramado['expirado'];
                    $retornoProgramadoData = $checkRetornoProgramado['data'];
                }

                $documentos = $value->getDocumentos();

                $anotacao = $this->checarAnotacao($value->getProtocolo(), $unidade);

                $arrayResult['processo'][] =  array(
                    "id" => $value->getProtocolo()->getIdProtocolo(),
                    "status" => $value->getProtocolo()->getStaProtocolo(),
                    "atributos" =>  array(
                        'idProcedimento' => $value->getIdProcedimento(),
                        'idProtocolo' => $value->getProtocolo()->getIdProtocolo(),
                        'numero' => $value->getProtocolo()->getProtocoloFormatado(),
                        'tipoProcesso' => $value->getIdTipoProcedimento()->getNome(),
                        'descricao' => $value->getProtocolo()->getDescricao(),
                        'usuarioAtribuido' => $usuarioAtribuido,
                        'estado' => $value->getProtocolo()->getStaEstado(),
                        'anotacoes' => $anotacao['ANOTACOES'],
                        'unidade' => array(
                            'idUnidade' => $unidade,
                            'sigla' => $value->getProtocolo()->getIdUnidadeGeradora()->getSigla()
                        ),
                        'status' =>  array(
                            'documentoSigiloso' =>($value->getProtocolo()->getStaGrauSigilo())? Processo::SIM : Processo::NAO,
                            'documentoRestrito' => $restrito,
                            'documentoNovo' => $novo,
                            'documentoPublicado' => $this->checarDocumentoPublicado($documentos),
                            'anotacao' => $anotacao['ANOTACAO'],
                            'anotacaoPrioridade' => $anotacao['PRIORIDADE'],
                            'ciencia' => $value->getSinCiencia(),
                            'retornoProgramado' => $retornoProgramado,
                            'retornoAtrasado' => $expirado,
                            'retornoData' => $retornoProgramadoData,
                            'processoAcessadoUsuario' => $usuarioVisualizacao,
                            'processoAcessadoUnidade' => $tipoVisualizacao,
                        )
                    )
                );
            }
        }

        return  array('data' => $arrayResult, 'total' => $result['count']);
    }

    /**
     * @param $unidade
     * @param $tipo
     * @param $usuario
     * @param $limit
     * @param $offset
     * @return array
     */
    public function listarProcessos ($unidade, $tipo, $usuario, $limit, $offset)
    {
        $arrayResult =  array();
        $protocolo = $this->getDefaultEntityManager()->getRepository('Application\Entity\Procedimento');
        $repoUnidade = $this->getDefaultEntityManager()->getRepository('Application\Entity\Unidade');
        $unidade = $repoUnidade->find($unidade);

        $result = $protocolo->listarProcessos($unidade, $tipo, $usuario, $limit, $offset);

        if (count($result['result'])) {
            foreach($result['result'] as $value) {
                $atividades = $value->getProtocolo()->getAtividades();
                $novo = Processo::NAO;
                $restrito = Processo::NAO;
                $retornoProgramado = Processo::NAO;
                $expirado = Processo::NAO;
                $retornoProgramadoData = array();
                $usuarioVisualizacao = Processo::NAO;
                $usuarioAtribuido = null;

                $tipoVisualizacao = Processo::NAO;
                if (count($atividades)  > 0) {
                    $atividade = $atividades[(count($atividades) - 1)];

                    if (count($atividades) == 1) {
                        if ($atividade->getIdTarefa()->getIdTarefa() == 1) {
                            $novo = Processo::SIM;
                        }
                    }

                    /** @var Atividade $atividadeEntity */
                    foreach ($atividades as $atividadeEntity) {
                        $tipoVisualizacao = ($atividadeEntity->getTipoVisualizacao() == 0)? Processo::SIM : Processo::NAO;

                        if ($atividadeEntity->getIdUsuarioVisualizacao()) {
                            if ($atividadeEntity->getIdUsuarioVisualizacao()->getId() == $usuario)
                            {
                                $usuarioVisualizacao = Processo::SIM;
                            }
                        }

                        $usuarioAtribuido = ($atividadeEntity->getIdUsuarioAtribuicao())?
                            $atividadeEntity->getIdUsuarioAtribuicao()->getNome() : null;
                    }
                }

                if ($value->getProtocolo()->getStaNivelAcesso() == 1) {
                    $restrito = Processo::SIM;
                }

                $documentos = $value->getDocumentos();

                $anotacao = $this->checarAnotacao($value->getProtocolo(), $unidade);

                $checkRetornoProgramado = $this->checarRetornoProgramado($value->getProtocolo(), $unidade);

                if ($checkRetornoProgramado['retornoProgramado'] == Processo::SIM) {
                    $retornoProgramado = $checkRetornoProgramado['retornoProgramado'];
                    $expirado = $checkRetornoProgramado['expirado'];
                    $retornoProgramadoData = $checkRetornoProgramado['data'];
                }

                $arrayResult['processo'][] =  array(
                    "id" => $value->getProtocolo()->getIdProtocolo(),
                    "status" => $value->getProtocolo()->getStaProtocolo(),
                    "atributos" =>  array(
                        'idProcedimento' => $value->getIdProcedimento(),
                        'idProtocolo' => $value->getProtocolo()->getIdProtocolo(),
                        'numero' => $value->getProtocolo()->getProtocoloFormatado(),
                        'tipoProcesso' => $value->getIdTipoProcedimento()->getNome(),
                        'descricao' => $value->getProtocolo()->getDescricao(),
                        'usuarioAtribuido' => $usuarioAtribuido,
                        'estado' => $value->getProtocolo()->getStaEstado(),
                        'unidade' => array(
                            'idUnidade' => $value->getProtocolo()->getIdUnidadeGeradora()->getIdUnidade(),
                            'sigla' => $value->getProtocolo()->getIdUnidadeGeradora()->getSigla()
                        ),
                        'anotacoes' => $anotacao['ANOTACOES'],
                        'status' =>  array(
                            'documentoSigiloso' => ($value->getProtocolo()->getStaGrauSigilo())? Processo::SIM : Processo::NAO,
                            'documentoRestrito' => $restrito,
                            'documentoNovo' => $novo,
                            'documentoPublicado' => $this->checarDocumentoPublicado($documentos),
                            'anotacao' => $anotacao['ANOTACAO'],
                            'anotacaoPrioridade' => $anotacao['PRIORIDADE'],
                            'ciencia' => $value->getSinCiencia(),
                            'retornoProgramado' => $retornoProgramado,
                            'retornoAtrasado' => $expirado,
                            'retornoData' => $retornoProgramadoData,
                            'processoAcessadoUsuario' => $usuarioVisualizacao,
                            'processoAcessadoUnidade' => $tipoVisualizacao,
                        )
                    )
                );
            }
        }

        return  array('data' => $arrayResult, 'total' => $result['count']);
    }

    public function listarCienciaProcesso($protocolo) {
        $repositorio = $this->getDefaultEntityManager()->getRepository('Application\Entity\Atividade');
        $protocolo = $this->getDefaultEntityManager()->getRepository('Application\Entity\Protocolo')->find($protocolo);
        $tarefa = $this->getDefaultEntityManager()->getRepository('Application\Entity\Tarefa')->find(82);

        $result = $repositorio->getAtividadeCiencia($protocolo, $tarefa);
        $arr = array();
        /** @var Atividade $atividade */
        foreach($result as $atividade) {
            $arr[] = array(
                'data' => $atividade->getDthAbertura()->format('d/m/Y'),
                'unidade' => $atividade->getIdUnidade()->getSigla(),
                'descricao' => $this->traduzirTemplate($atividade->getIdTarefa()->getNome(),$atividade),
                'nome' => $atividade->getIdUsuarioOrigem()->getSigla()
            );
        }

        return $arr;
    }

    private function formatarData(\DateTime $date) {
        return  array(
            'date' => $date->format('d/m/Y'),
            'time' => $date->format('H:i')
        );
    }

    public function traduzirTemplate($strTemplate,Atividade $atividade) {

        if ($strTemplate) {
            $repoAtributoAndamento = $this->getDefaultEntityManager()->getRepository('Application\Entity\AtributoAndamento');
            $resAttr = $repoAtributoAndamento->pesquisarAtributoAndamento($atividade);

            if (count($resAttr)) {
                /** @var AtributoAndamento $attr */
                foreach($resAttr as $attr) {
                    $valor = $attr->getValor();

                    if (strripos($valor, '¥')) {
                        $valor = explode('¥', $attr->getValor());
                        $valor = $valor[0];
                    }

                    $strTemplate = str_replace('@' . $attr->getNome() . '@', $valor, $strTemplate);

                    $sigilo = ($atividade->getIdProtocolo()->getStaGrauSigilo())? 'sigiloso': 'não sigiloso';

                    $strTemplate = str_replace('@GRAU_SIGILO@', $sigilo, $strTemplate);
                    $strTemplate = str_replace('@HIPOTESE_LEGAL@', '', $strTemplate);
                }
            }
        }

        return $strTemplate;
    }

    public function listarAtividadeProcesso($procedimento, $unidade, $limit, $offset) {
        $arrayResult =  array();
        $documentoRepository = $this->getDefaultEntityManager()->getRepository('Application\Entity\Atividade');
        $repoUnidade = $this->getDefaultEntityManager()->getRepository('Application\Entity\Unidade');
        $unidade = $repoUnidade->find($unidade);

        $result = $documentoRepository->listarAtividades($procedimento, $unidade, $limit, $offset);

        if (count($result['result'])) {
            foreach($result['result'] as $value) {
                $dateTime = $this->formatarData($value->getDthAbertura());

                if ($value instanceof Atividade) {
                    $informacao = $this->traduzirTemplate($value->getIdTarefa()->getNome(), $value);

                    $arrayResult[] =  array(
                        "id" => $value->getIdAtividade(),
                        "atributos" =>  array(
                            "idProcesso" => $value->getIdProtocolo()->getIdProtocolo(),
                            "usuario" => ($value->getIdUsuarioOrigem())? $value->getIdUsuarioOrigem()->getSigla() : null,
                            "data" => $dateTime['date'],
                            "hora" => $dateTime['time'],
                            "unidade" => $value->getIdUnidade()->getSigla(),
                            "informacao" => $informacao
                        )
                    );
                }
            }
        }

        return  array('data' => $arrayResult, 'total' => $result['count']);
    }

    public function pesquisarProcessoAcompanhamento ($usuario, $grupo, $limit, $offset) {
        $arrayResult =  array();
        $protocolo = $this->getDefaultEntityManager()->getRepository('Application\Entity\Procedimento');
        if ($grupo) {
            $repoGrupo = $this->getDefaultEntityManager()->getRepository('Application\Entity\GrupoAcompanhamento');
            $grupo = $repoGrupo->find($grupo);
        } else {
            return array();
        }

        $result = $protocolo->pesquisarProcessoAcompanhamento($usuario, $grupo, $limit, $offset);

        if (count($result['result'])) {
            foreach($result['result'] as $value) {
                $atividades = $value->getProtocolo()->getAtividades();
                $novo = Processo::NAO;
                $restrito = Processo::NAO;
                $retornoProgramado = Processo::NAO;
                $retornoProgramadoData = array();
                $expirado = Processo::NAO;
                $usuarioVisualizacao = Processo::NAO;
                $usuarioAtribuido = null;

                $tipoVisualizacao = Processo::NAO;
                if (count($atividades)  > 0) {
                    $atividade = $atividades[(count($atividades) - 1)];

                    if (count($atividades) == 1) {
                        if ($atividade->getIdTarefa()->getIdTarefa() == 1) {
                            $novo = Processo::SIM;
                        }
                    }

                    foreach ($atividades as $atividadeEntity) {
                        $tipoVisualizacao = ($atividadeEntity->getTipoVisualizacao() == 0)? Processo::SIM : Processo::NAO;

                        if ($atividadeEntity->getIdUsuarioConclusao()) {
                            if ($atividadeEntity->getIdUsuarioConclusao()->getId() == $usuario)
                            {
                                $usuarioVisualizacao = Processo::SIM;
                            }
                        }

                        $usuarioAtribuido = ($atividadeEntity->getIdUsuarioAtribuicao())?
                            $atividadeEntity->getIdUsuarioAtribuicao()->getNome() : null;

                    }
                }

                if ($value->getProtocolo()->getStaNivelAcesso() == 1) {
                    $restrito = Processo::SIM;
                }

                $documentos = $value->getDocumentos();

                $unidade = $value->getProtocolo()->getIdUnidadeGeradora()->getIdUnidade();
                $checkRetornoProgramado = $this->checarRetornoProgramado($value->getProtocolo(), $unidade);

                $anotacao = $this->checarAnotacao($value->getProtocolo(), $unidade);

                if ($checkRetornoProgramado['retornoProgramado'] == Processo::SIM) {
                    $retornoProgramado = $checkRetornoProgramado['retornoProgramado'];
                    $expirado = $checkRetornoProgramado['expirado'];
                    $retornoProgramadoData = $checkRetornoProgramado['data'];
                }

                $arrayResult['processo'][] =  array(
                    "id" => $value->getProtocolo()->getIdProtocolo(),
                    "status" => $value->getProtocolo()->getStaProtocolo(),
                    "atributos" =>  array(
                        'idProcedimento' => $value->getIdProcedimento(),
                        'idProtocolo' => $value->getProtocolo()->getIdProtocolo(),
                        'numero' => $value->getProtocolo()->getProtocoloFormatado(),
                        'tipoProcesso' => $value->getIdTipoProcedimento()->getNome(),
                        'descricao' => $value->getProtocolo()->getDescricao(),
                        'usuarioAtribuido' => $usuarioAtribuido,
                        'anotacoes' => $anotacao['ANOTACOES'],
                        'estado' => $value->getProtocolo()->getStaEstado(),
                        'unidade' => array(
                            'idUnidade' => $unidadade,
                            'sigla' => $value->getProtocolo()->getIdUnidadeGeradora()->getSigla()
                        ),
                        'status' =>  array(
                            'documentoSigiloso' => ($value->getProtocolo()->getStaGrauSigilo())? Processo::SIM : Processo::NAO,
                            'documentoRestrito' => $restrito,
                            'documentoNovo' => $novo,
                            'documentoPublicado' => $this->checarDocumentoPublicado($documentos),
                            'anotacao' => $anotacao['ANOTACAO'],
                            'anotacaoPrioridade' => $anotacao['PRIORIDADE'],
                            'ciencia' => $value->getSinCiencia(),
                            'retornoProgramado' => $retornoProgramado,
                            'retornoAtrasado' => $expirado,
                            'retornoData' => $retornoProgramadoData,
                            'processoAcessadoUsuario' => $usuarioVisualizacao,
                            'processoAcessadoUnidade' => $tipoVisualizacao,
                        )
                    )
                );
            }
        }

            return  array('data' => $arrayResult, 'total' => $result['count']);
    }

    public function cienciaProcesso($idProcedimento, $unidade, $usuario, $serviceUnidade, $serviceProtocolo, $serviceAtividade)
    {
        $repTarefa = $this->getDefaultEntityManager()->getRepository('Application\Entity\Tarefa');
        $repoProcedimento = $this->getDefaultEntityManager()->getRepository('Application\Entity\Procedimento');

        $repUsuario = $this->getDefaultEntityManager()->getRepository('Application\Entity\Usuario');

        $unidade = $serviceUnidade->getRepository()->find($unidade);
        $usuario = $repUsuario->find($usuario);

        $atividade = new \Application\Entity\Atividade();
        $atividade->setIdUnidade($unidade);
        $atividade->setIdUsuarioVisualizacao($usuario);
        $atividade->setIdUsuario($usuario);
        $atividade->setIdUsuarioOrigem($usuario);
        $atividade->setIdUnidadeOrigem($unidade);
        $atividade->setDthAbertura(new \DateTime());
        $atividade->setIdTarefa($repTarefa->find(82));
        $atividade->setSinInicial('S');
        $atividade->setIdProtocolo(
            $serviceProtocolo->pesquisarProtocoloPorID($idProcedimento)
        );
        $atividade->setTipoVisualizacao(0);

        $serviceAtividade->criarAtividade($atividade);

        /** @var Procedimento $procedimento */
        $procedimento = $repoProcedimento->find($idProcedimento);
        $procedimento->setSinCiencia(Processo::SIM);

        $this->getDefaultEntityManager()->persist($procedimento);
        $this->getDefaultEntityManager()->flush();
    }

    public function concluirProcesso($post)
    {
        $config = $this->getConfig();
        $urlSoap = $config['soap']['wsdl_sei'];

        $idUnidade = (isset($post['unidade']))? $post['unidade'] : null;
        $numeroProcesso = (isset($post['numero']))? $post['numero'] : null;

        $client = new \SoapClient($urlSoap);

        try {
            $return = $client->__call('concluirProcesso',
                array('SiglaSistema' => Processo::SISTEMA,
                    'IdentificacaoServico' => Processo::ACTION_CONCLUIR,
                    'IdUnidade' => $idUnidade,
                    'ProtocoloProcedimento' => $numeroProcesso));

            return $return;

        } catch (\SoapFault $ex) {
            throw $ex;
        }
    }

    public function atribuirProcesso($post)
    {
        $config = $this->getConfig();
        $urlSoap = $config['soap']['wsdl_sei'];

        $idUnidade = (isset($post['unidade']))? $post['unidade'] : null;
        $numeroProcesso = (isset($post['numero']))? $post['numero'] : null;
        $idUsuario = (isset($post['usuario']))? $post['usuario'] : null;
        $sinReabrir = (isset($post['reabrir']))? $post['reabrir'] : null;

        $client = new \SoapClient($urlSoap);

        try {
            $return = $client->__call('atribuirProcesso',
                array('SiglaSistema' => Processo::SISTEMA,
                    'IdentificacaoServico' => Processo::ACTION_ATRIBUIR,
                    'IdUnidade' => "{$idUnidade}",
                    'ProtocoloProcedimento' => "{$numeroProcesso}",
                    'IdUsuario' => "{$idUsuario}",
                    'SinReabrir' => $sinReabrir));

            return $return;

        } catch (\SoapFault $ex) {
            throw $ex;
        }
    }

    public function enviarProcesso($post)
    {
        $config = $this->getConfig();
        $urlSoap = $config['soap']['wsdl_sei'];

        $idUnidade = (isset($post['unidade']))? $post['unidade'] : null;
        $numeroProcesso = (isset($post['numero']))? $post['numero'] : null;
        $unidadeDestino = (isset($post['UnidadesDestino']))? explode(',', $post['UnidadesDestino']): null;
        $sinManterAbertoUnidade = (isset($post['SinManterAbertoUnidade']))? $post['SinManterAbertoUnidade'] : null;
        $sinRemoverAnotacao = (isset($post['SinRemoverAnotacao']))? $post['SinRemoverAnotacao'] : null;
        $sinEnviarEmailNotificacao = 'N';
        $dataRetornoProgramado = (isset($post['DataRetornoProgramado']))? $post['DataRetornoProgramado'] : null;
        $diasRetornoProgramado = (isset($post['DiasRetornoProgramado']))? $post['DiasRetornoProgramado'] : null;
        $sinDiasUteisRetornoProgramado = (isset($post['SinDiasUteisRetornoProgramado']))? $post['SinDiasUteisRetornoProgramado'] : null;

        $client = new \SoapClient($urlSoap);

        try {
            $return = $client->__call('enviarProcesso',
                array(
                    'SiglaSistema' => Processo::SISTEMA,
                    'IdentificacaoServico' => Processo::ACTION_ENVIAR,
                    'IdUnidade' => "{$idUnidade}",
                    'ProtocoloProcedimento' => "{$numeroProcesso}",
                    'UnidadesDestino' => $unidadeDestino,
                    'SinManterAbertoUnidade' => "{$sinManterAbertoUnidade}",
                    'SinRemoverAnotacao' => "{$sinRemoverAnotacao}",
                    'SinEnviarEmailNotificacao' => "{$sinEnviarEmailNotificacao}",
                    'DataRetornoProgramado' => "{$dataRetornoProgramado}",
                    'DiasRetornoProgramado' => "{$diasRetornoProgramado}",
                    'SinDiasUteisRetornoProgramado' => "{$sinDiasUteisRetornoProgramado}"));

            return $return;

        } catch (\SoapFault $ex) {
            throw $ex;
        }
    }

    public function sobrestarProcesso($protocolo, $motivo, $unidade, $usuario)
    {
        $repTarefa = $this->getDefaultEntityManager()->getRepository('Application\Entity\Tarefa');
        $repUnidade = $this->getDefaultEntityManager()->getRepository('Application\Entity\Unidade');
        $repUsuario = $this->getDefaultEntityManager()->getRepository('Application\Entity\Usuario');
        $repDocumento = $this->getDefaultEntityManager()->getRepository('Application\Entity\Documento');
        $unidade = $repUnidade->find($unidade);
        $usuario = $repUsuario->find($usuario);

        /** @var Protocolo $prot */
        $prot = $this->serviceProtocolo->pesquisarProtocoloPorID($protocolo);
        $prot->setStaEstado(1);

        $this->getDefaultEntityManager()->persist($prot);
        $this->getDefaultEntityManager()->flush();

        $atividade = new \Application\Entity\Atividade();
        $atividade->setIdUnidade($unidade);
        $atividade->setIdUsuarioVisualizacao($usuario);
        $atividade->setIdUsuario($usuario);
        $atividade->setIdUsuarioOrigem($usuario);
        $atividade->setIdUnidadeOrigem($unidade);
        $atividade->setDthAbertura(new \DateTime());
        $atividade->setIdTarefa($repTarefa->find(Processo::TAREFA_SOBRESTAR));
        $atividade->setSinInicial('S');
        $atividade->setIdProtocolo($prot);

        $atividade->setTipoVisualizacao(0);

        $this->serviceAtividade->criarAtividade($atividade, 'MOTIVO', $motivo);
    }

    public function cancelarSobrestarProcesso($protocolo, $unidade, $usuario)
    {
        $repTarefa = $this->getDefaultEntityManager()->getRepository('Application\Entity\Tarefa');
        $repUnidade = $this->getDefaultEntityManager()->getRepository('Application\Entity\Unidade');
        $repUsuario = $this->getDefaultEntityManager()->getRepository('Application\Entity\Usuario');
        $repDocumento = $this->getDefaultEntityManager()->getRepository('Application\Entity\Documento');
        $unidade = $repUnidade->find($unidade);
        $usuario = $repUsuario->find($usuario);

        /** @var Protocolo $prot */
        $prot = $this->serviceProtocolo->pesquisarProtocoloPorID($protocolo);
        $prot->setStaEstado(0);

        $this->getDefaultEntityManager()->persist($prot);
        $this->getDefaultEntityManager()->flush();

        $atividade = new \Application\Entity\Atividade();
        $atividade->setIdUnidade($unidade);
        $atividade->setIdUsuarioVisualizacao($usuario);
        $atividade->setIdUsuario($usuario);
        $atividade->setIdUsuarioOrigem($usuario);
        $atividade->setIdUnidadeOrigem($unidade);
        $atividade->setDthAbertura(new \DateTime());
        $atividade->setIdTarefa($repTarefa->find(Processo::TAREFA_SOBRESTAR_CANCELAR));
        $atividade->setSinInicial('S');
        $atividade->setIdProtocolo($prot);

        $atividade->setTipoVisualizacao(0);

        $this->serviceAtividade->criarAtividade($atividade);
    }
}
