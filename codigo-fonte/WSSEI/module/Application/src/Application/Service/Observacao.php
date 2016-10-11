<?php
/**
 * Created by IntelliJ IDEA.
 * User: marioeugenio
 * Date: 12/19/15
 * Time: 12:55 PM
 */

namespace Application\Service;

use Application\Entity\AtributoAndamento;
use Application\Entity\Protocolo;
use Application\Entity\SqAtributoAndamento;
use Application\Service\Exception\BaseException;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\Debug;

class Observacao extends AbstractService {

    private $serviceAtividade;

    const ID_TAREFA = 65;

    public function setServiceAtividade($service)
    {
        $this->serviceAtividade = $service;
    }

    private function _validarObservacao(Protocolo $observacao)
    {
        if (!$observacao->getIdUnidade()) {
            throw new BaseException('Unidade não informada');
        }

        if (!$observacao->getIdProtocolo()) {
            throw new BaseException('Protocolo não informado');
        }

        if (!$observacao->getDescricao()) {
            throw new BaseException('Descrição não informada');
        }
    }

    public function criarObservacao($post, $serviceUnidade, $serviceProtocolo, $serviceAtividade)
    {
        $repTarefa = $this->getDefaultEntityManager()->getRepository('Application\Entity\Tarefa');
        $repUsuario = $this->getDefaultEntityManager()->getRepository('Application\Entity\Usuario');

        $unidade = $serviceUnidade->getRepository()->find($post['unidade']);
        $usuario = $repUsuario->find($post['usuario']);

        $atividade = new \Application\Entity\Atividade();
        $atividade->setIdUnidade($unidade);
        $atividade->setIdUsuarioVisualizacao($usuario);
        $atividade->setIdUsuario($usuario);
        $atividade->setIdUsuarioOrigem($usuario);
        $atividade->setIdUnidadeOrigem($unidade);
        $atividade->setDthAbertura(new \DateTime());
        $atividade->setIdTarefa($repTarefa->find(Observacao::ID_TAREFA));
        $atividade->setSinInicial('S');
        $atividade->setIdProtocolo(
            $serviceProtocolo->pesquisarProtocoloPorID($post['protocolo'])
        );
        $atividade->setTipoVisualizacao(0);

        $serviceAtividade->criarAtividade($atividade);

        $sqAtributoAndamento = new SqAtributoAndamento();
        $sqAtributoAndamento->setCampo(null);
        $this->getDefaultEntityManager()->persist($sqAtributoAndamento);
        $this->getDefaultEntityManager()->flush();

        $atributoAndamento = new AtributoAndamento();
        $atributoAndamento->setIdAtributoAndamento($sqAtributoAndamento->getId());
        $atributoAndamento->setIdOrigem(0);
        $atributoAndamento->setIdAtividade($atividade);
        $atributoAndamento->setNome('DESCRICAO');
        $atributoAndamento->setValor($post['descricao']);

        $this->getDefaultEntityManager()->persist($atributoAndamento);
        $this->getDefaultEntityManager()->flush();

        return true;
    }

    private function _checarObservacao (\Application\Entity\Observacao $observacao) {
        $result = $this->getRepository()->findOneBy(array(
            'idUnidade' => $observacao->getIdUnidade(),
            'idProtocolo' => $observacao->getIdProtocolo()
        ));

        if ($result instanceof \Application\Entity\Observacao) {
            throw new BaseException('Acompanhamento já realizado');
        }
    }

    /**
     * @return EntityManager
     */
    public function getRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Observacao');
    }
} 