<?php
/**
 * Created by IntelliJ IDEA.
 * User: marioeugenio
 * Date: 12/21/15
 * Time: 11:16 AM
 */

namespace Application\Service;

use Application\Entity\Anexo;
use Application\Entity\Assinatura;
use Application\Entity\Documento;
use Application\Entity\Procedimento;
use Application\Entity\Protocolo;
use Application\Entity\RelBlocoProtocolo;
use Application\Entity\RelBlocoUnidade;
use Application\Entity\Unidade;
use Application\Service\Exception\BaseException;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityRepository;
use TheSeer\DirectoryScanner\Exception;

class Bloco extends AbstractService {

    const ACTION_DISPONIBILIZAR_BLOCO = 'DISPONIBILIZAR_BLOCO';
    const ACTION_CONSULTAR_BLOCO = 'CONSULTAR_BLOCO';

    public function consultarBloco($unidade, $limit, $offset)
    {
        $repository = $this->getRepository();
        $result = $repository->pesquisarBloco($unidade, $offset, $limit);

        $arrResult = array();

        if (count($result['result'])) {
            /** @var \Application\Entity\Bloco $bloco */
            foreach($result['result'] as $bloco) {
                $unidades = $bloco->getRelBlocoUnidade();
                $arrUnidades = array();

                if (count($unidades)) {
                    /** @var Unidade $unidade */
                    foreach($unidades as $unidade)
                    {
                        $arrUnidades[] = array(
                            'idUnidade' => $unidade->getIdUnidade()->getIdUnidade(),
                            'unidade' => $unidade->getIdUnidade()->getSigla()
                        );
                    }
                }

                $arrResult['bloco'][] = array(
                    'id' => $bloco->getIdBloco(),
                    'atributos' => array(
                        'idBloco' => $bloco->getIdBloco(),
                        'idUnidade' => $bloco->getIdUnidade()->getIdUnidade(),
                        'siglaUnidade' => $bloco->getIdUnidade()->getSigla(),
                        'estado' => $bloco->getStaEstado(),
                        'descricao' => $bloco->getDescricao(),
                        'unidades' => $arrUnidades
                    )
                );
            }
        }

        return  array('data' => $arrResult, 'total' => $result['count']);
    }

    private function _validaAnotacao(RelBlocoProtocolo $blocoProtocolo)
    {
        if (!$blocoProtocolo->getIdBloco()) {
            throw new BaseException('Bloco não informado');
        }

        if (!$blocoProtocolo->getIdProtocolo()) {
            throw new BaseException('Protocolo não informado');
        }

        if (!$blocoProtocolo->getObservacao()) {
            throw new BaseException('Anotação não informada');
        }
    }

    public function retornarBloco($post)
    {
        try {
            $idUnidade = (isset($post['unidade']))? $post['unidade'] : null;
            $idBloco = (isset($post['bloco']))? $post['bloco'] : null;

            $unidade = $this->getUnidadeRepository()->find($idUnidade);
            $bloco = $this->getRepository()->find($idBloco);

            $repBlocoUnidade = $this->getBlocoUnidadeRepository();
            /** @var RelBlocoUnidade $blocoProtocolo */
            $blocoUnidade = $repBlocoUnidade->findOneBy(
                array('idUnidade' => $unidade, 'idBloco' => $bloco)
            );

            if ($blocoUnidade instanceof RelBlocoUnidade) {
                $blocoUnidade->setSinRetornado('S');

                $this->getDefaultEntityManager()->persist($blocoUnidade);
                $this->getDefaultEntityManager()->flush();

                $this->_retornoBloco($bloco);

                return true;
            }

            throw new BaseException('Bloco não pode ser retornado, pois a Unidade Geradora e Destinatária são iguais');

        } catch (BaseException $ex) {
            throw $ex;
        }
    }

    private function _retornoBloco(\Application\Entity\Bloco $bloco)
    {
        $repBlocoUnidade = $this->getBlocoUnidadeRepository();
        /** @var RelBlocoUnidade $blocoProtocolo */
        $relblocoUnidade = $repBlocoUnidade->findBy(array('idBloco' => $bloco));

        $retorno = true;
        /** @var RelBlocoUnidade $blocoUnidade */
        foreach($relblocoUnidade as $blocoUnidade) {
            if ($blocoUnidade->getSinRetornado() != 'S') {
                $retorno = false;
            }
        }

        if ($retorno) {
            $bloco->setStaEstado('R');

            $this->getDefaultEntityManager()->persist($bloco);
            $this->getDefaultEntityManager()->flush();
        }
    }

    public function cadastrarAnotacao($post)
    {
        try {
            $idProtocolo = (isset($post['protocolo']))? $post['protocolo'] : null;
            $idBloco = (isset($post['bloco']))? $post['bloco'] : null;
            $anotacao = (isset($post['anotacao']))? $post['anotacao'] : null;

            $protocolo = $this->getProtocoloRepository()->find($idProtocolo);
            $bloco = $this->getRepository()->find($idBloco);

                $repBlocoProtocolo = $this->getBlocoProtocoloRepository();
            /** @var RelBlocoProtocolo $blocoProtocolo */
            $blocoProtocolo = $repBlocoProtocolo->findOneBy(
                array('idProtocolo' => $protocolo, 'idBloco' => $bloco)
            );

            if ($blocoProtocolo instanceof RelBlocoProtocolo) {
                $blocoProtocolo->setObservacao($anotacao);

                $this->_validaAnotacao($blocoProtocolo);

                $this->getDefaultEntityManager()->persist($blocoProtocolo);
                $this->getDefaultEntityManager()->flush();

                return true;
            }

            throw new BaseException('Documento não encontrado no bloco informado');

        } catch (BaseException $ex) {
            throw $ex;
        }
    }

    public function listarDocumentosBloco($bloco)
    {
        $repoProtocolo = $this->getProtocoloRepository();
        $repoBloco = $this->getRepository();
        $arrDocumento = array();

        $relblocos = $repoBloco->pesquisarProtocoloBloco($bloco);

        if (count($relblocos)) {
            /** @var RelBlocoProtocolo $relbloco */
            foreach($relblocos as $relbloco)
            {
                $repProtocoloProtocolo = $this->getProtocoloProtocoloRepository();
                $processoRelacionado = $repProtocoloProtocolo->pesquisarProtocoloAlternativo(
                    $relbloco->getIdProtocolo()->getIdProtocolo()
                );

                if (count($processoRelacionado) > 0) {
                    $anexo = $this->getDefaultEntityManager()->getRepository('Application\Entity\Anexo');

                    foreach($processoRelacionado as $processoRel) {
                        /** @var Protocolo $protocoloRel */
                        $protocoloRel = $repoProtocolo->find($processoRel->getIdProtocolo1());
                        $arrAssinatura = array();

                        /** @var Documento $documentos */
                        foreach($protocoloRel->getProcedimento()->getDocumentos() as $documentos) {
                            if ($documentos->getIdDocumento() == $relbloco->getIdProtocolo()->getIdProtocolo()) {
                                /** @var Assinatura $assinaturas */
                                foreach($documentos->getAssinaturas() as $assinaturas) {
                                    $arrAssinatura[] = array(
                                        'nome' => $assinaturas->getNome(),
                                        'cargo' => $assinaturas->getTratamento(),
                                    );
                                }
                            }

                        }

                        $resultAnexo = ($anexo->findOneBy(array(
                            'idProtocolo' => $relbloco->getIdProtocolo()->getIdProtocolo(),
                            'sinAtivo'      => 'S'
                        )));

                        $mimetype = null;
                        if ($resultAnexo) {
                            $mimetype = $resultAnexo->getNome();
                            $mimetype = substr($mimetype, strrpos($mimetype, '.')+1);
                        }

                        $arrDocumento['documento'][] = array(
                            'id' => $relbloco->getIdProtocolo()->getIdProtocolo(),
                            'atributos' => array(
                                'idDocumento' => $relbloco->getIdProtocolo()->getIdProtocolo(),
                                "mimeType" => ($mimetype)?$mimetype:'html',
                                'data' => $relbloco->getIdProtocolo()->getDtaGeracao()->format('d/m/Y'),
                                'numero' => $relbloco->getIdProtocolo()->getProtocoloFormatado(),
                                'numeroProcesso' => $protocoloRel->getProtocoloFormatado(),
                                'tipo' => $protocoloRel->getProcedimento()->getIdTipoProcedimento()->getNome(),
                                'assinaturas' => $arrAssinatura
                            ),
                            'anotacao' => $relbloco->getObservacao()
                        );
                    }
                }
            }

            return array('data' => $arrDocumento, 'total' => count($processoRelacionado));
        }

        return array('data' => null, 'total' => 0);
    }

    public function disponibilizarBloco($post)
    {
        $config = $this->getConfig();
        $urlSoap = $config['soap']['wsdl_sei'];

        $idUnidade = (isset($post['unidade']))? $post['unidade'] : null;
        $idBloco = (isset($post['bloco']))? $post['bloco'] : null;

        $client = new \SoapClient($urlSoap);

        try {
            $return = $client->__call('disponibilizarBloco',
                array(
                    'SiglaSistema' => Processo::SISTEMA,
                    'IdentificacaoServico' => Bloco::ACTION_DISPONIBILIZAR_BLOCO,
                    'IdUnidade' => "{$idUnidade}",
                    'IdBloco' => "{$idBloco}"));

            return $return;

        } catch (\SoapFault $ex) {
            throw $ex;
        }
    }

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Bloco');
    }

    /**
     * @return EntityRepository
     */
    public function getAssinaturaRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Assinatura');
    }

    /**
     * @return EntityRepository
     */
    public function getBlocoProtocoloRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\RelBlocoProtocolo');
    }

    /**
     * @return EntityRepository
     */
    public function getBlocoUnidadeRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\RelBlocoUnidade');
    }

    /**
     * @return EntityRepository
     */
    public function getProtocoloProtocoloRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\RelProtocoloProtocolo');
    }

    /**
     * @return EntityRepository
     */
    public function getProtocoloRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Protocolo');
    }

    /**
     * @return EntityRepository
     */
    public function getUnidadeRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Unidade');
    }

    /**
     * @return EntityRepository
     */
    public function getProcedimentoRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Procedimento');
    }
}