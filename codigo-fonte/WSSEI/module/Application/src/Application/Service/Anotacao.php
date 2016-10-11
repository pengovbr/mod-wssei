<?php
/**
 * Created by IntelliJ IDEA.
 * User: marioeugenio
 * Date: 12/21/15
 * Time: 11:16 AM
 */

namespace Application\Service;

use Application\Entity\SqAnotacao;
use Application\Service\Exception\BaseException;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\Debug;

class Anotacao extends AbstractService {

    const SIM = 'S';
    const NAO = 'N';


    private function _validarAnotacao(\Application\Entity\Anotacao $anotacao)
    {
        if (!$anotacao->getDescricao()) {
            throw new BaseException('Descrição não informada');
        }

        if (!$anotacao->getIdProtocolo()) {
            throw new BaseException('Protocolo não informado');
        }

        if (!$anotacao->getIdUnidade()) {
            throw new BaseException('Unidade não informada');
        }

        if (!$anotacao->getSinPrioridade()) {
            throw new BaseException('Prioridade não informado');
        }
    }

    public function criarAnotacao($post, Unidade $serviceUnidade, Protocolo $serviceProtocolo)
    {
        $protocolo = $serviceProtocolo->pesquisarProtocoloPorID($post['protocolo']);

        $rep = $this->getRepository();
        $result = $rep->findBy(array(
            'idProtocolo' => $protocolo
        ));

        if (count($result)) {
            /** @var \Application\Entity\Anotacao $anotacao */
            $anotacao = $result[0];
        }

        $sqAtividade = new SqAnotacao();
        $sqAtividade->setCampo(null);
        $this->getDefaultEntityManager()->persist($sqAtividade);
        $this->getDefaultEntityManager()->flush();

        if (!($anotacao instanceof \Application\Entity\Anotacao)) {
            $anotacao = new \Application\Entity\Anotacao();
            $anotacao->setIdAnotacao(
                $sqAtividade->getId()
            );
        }

        if (isset($post['descricao'])) {
            $anotacao->setDescricao($post['descricao']);
        }

        if (isset($post['protocolo'])) {
            $anotacao->setIdProtocolo($protocolo);
        }

        if (isset($post['unidade'])) {
            $anotacao->setIdUnidade(
                $serviceUnidade->getRepository()->find($post['unidade'])
            );
        }

        if (isset($post['usuario'])) {
            $anotacao->setIdUsuario($post['usuario']);
        }

        $anotacao->setDthAnotacao(new \DateTime());

        if (isset($post['prioridade'])) {
            $anotacao->setSinPrioridade(
                $post['prioridade']
            );
        }

        $anotacao->setStaAnotacao('U');

        $this->_validarAnotacao($anotacao);

        $this->getDefaultEntityManager()->persist($anotacao);
        $this->getDefaultEntityManager()->flush();
    }

    public function getRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Anotacao');
    }
}