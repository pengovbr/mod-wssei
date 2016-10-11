<?php
/**
 * Created by IntelliJ IDEA.
 * User: marioeugenio
 * Date: 12/18/15
 * Time: 6:36 PM
 */

namespace Application\Service;

use Application\Service\Exception\BaseException;
use Doctrine\ORM\EntityManager;

class RetornoProgramado extends AbstractService {


    private function _validarRetornoProgramado(\Application\Entity\RetornoProgramado $retornoProgramado)
    {
        $now = new \DateTime();

        if (!$retornoProgramado->getDtaProgramada()) {
            throw new BaseException('Data Programada não informada');
        }

        if ($retornoProgramado->getDtaProgramada()->getTimestamp() < $now->getTimestamp()) {
            throw new BaseException('Data Programada não pode estar no passado');
        }

        if (!$retornoProgramado->getIdUsuario()) {
            throw new BaseException('Usuário não informado');
        }

        if (!$retornoProgramado->getIdUnidade()) {
            throw new BaseException('Unidade não informada');
        }

        if (!$retornoProgramado->getIdAtividadeEnvio()) {
            throw new BaseException('Atividade Envio não informada');
        }
    }

    public function agendarRetornoProgramado($post, Usuario $serviceUsuario, Atividade $serviceAtividade, Unidade $serviceUnidade)
    {
        $repoAnotacao = $this->getDefaultEntityManager()->getRepository('Application\Entity\RetornoProgramado');
        $id = $repoAnotacao->maxRetornoProgramado();

        $retornoProgramado = new \Application\Entity\RetornoProgramado();
        $retornoProgramado->setIdRetornoProgramado(($id + 1));

        if (isset($post['usuario'])) {
            $retornoProgramado->setIdUsuario(
                $serviceUsuario->getRepository()->find($post['usuario'])
            );
        }

        if (isset($post['atividadeEnvio'])) {
            $retornoProgramado->setIdAtividadeEnvio(
                $serviceAtividade->getRepository()->find($post['atividadeEnvio'])
            );
        }

        if (isset($post['unidade'])) {
            $retornoProgramado->setIdUnidade(
                $serviceUnidade->getRepository()->find($post['unidade'])
            );
        }

        if (isset($post['dtProgramada'])) {
            $retornoProgramado->setDtaProgramada(
                new \DateTime($post['dtProgramada'])
            );
        }

        $this->_validarRetornoProgramado($retornoProgramado);

        $this->getDefaultEntityManager()->persist($retornoProgramado);
        $this->getDefaultEntityManager()->flush();
    }
}