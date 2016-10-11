<?php

namespace Application\Service;

use Application\Entity\SqAcompanhamento;
use Application\Service\Exception\BaseException;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use Application\Entity\Atividade;


class Acompanhamento extends AbstractService {

    const ID_TAREFA = 65;


    private function validarAcompanhamento(\Application\Entity\Acompanhamento $acompanhamento) {
        if (!$acompanhamento->getIdUsuarioGerador()) {
            throw new BaseException('Usuário é obrigatório');
        }

        if (!$acompanhamento->getIdGrupoAcompanhamento()) {
            throw new BaseException('Grupo é obrigatório');
        }

        if (!$acompanhamento->getIdProtocolo()) {
            throw new BaseException('Protocolo é obrigatório');
        }

        if (!$acompanhamento->getIdUnidade()) {
            throw new BaseException('Unidade é obrigatório');
        }

        if (!$acompanhamento->getObservacao()) {
            throw new BaseException('Observação é obrigatório');
        }
    }

    public function criarAcompanhamentoProcesso($acompanhamento)
    {
        if ($acompanhamento instanceof \Application\Entity\Acompanhamento) {
            $acompanhamento->setDthGeracao(new \DateTime());
            $acompanhamento->setTipoVisualizacao(0);

            $sqAcompanhamento = new SqAcompanhamento();
            $sqAcompanhamento->setCampo(null);
            $this->getDefaultEntityManager()->persist($sqAcompanhamento);
            $this->getDefaultEntityManager()->flush();

            $acompanhamento->setIdAcompanhamento($sqAcompanhamento->getId());

            $this->validarAcompanhamento($acompanhamento);

            $this->getDefaultEntityManager()->persist($acompanhamento);
            $this->getDefaultEntityManager()->flush();

            return true;
        }

        return false;
    }
} 