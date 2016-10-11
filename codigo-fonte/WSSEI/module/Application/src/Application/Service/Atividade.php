<?php
/**
 * Created by IntelliJ IDEA.
 * User: marioeugenio
 * Date: 12/4/15
 * Time: 11:22 AM
 */

namespace Application\Service;


use Application\Entity\AtributoAndamento;
use Application\Entity\SqAtividade;
use Application\Entity\SqAtributoAndamento;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;

class Atividade extends AbstractService {

    private function adicionarAtributoAndamento($atividade, $valor, $nome)
    {
        $sqAtributo = new SqAtributoAndamento();
        $sqAtributo->setCampo(null);
        $this->getDefaultEntityManager()->persist($sqAtributo);
        $this->getDefaultEntityManager()->flush();

        $atributoAndamento = new AtributoAndamento();
        $atributoAndamento->setIdAtividade($atividade);
        $atributoAndamento->setIdAtributoAndamento($sqAtributo->getId());
        $atributoAndamento->setIdOrigem(0);
        $atributoAndamento->setNome($nome);
        $atributoAndamento->setValor($valor);

        $this->getDefaultEntityManager()->persist($atributoAndamento);
        $this->getDefaultEntityManager()->flush();
    }

    public function criarAtividade(\Application\Entity\Atividade $atividade, $nome = null, $valor = null)
    {
        try {

            if ($atividade instanceof \Application\Entity\Atividade) {

                $sqAtividade = new SqAtividade();
                $sqAtividade->setCampo(null);
                $this->getDefaultEntityManager()->persist($sqAtividade);
                $this->getDefaultEntityManager()->flush();

                $atividade->setSinInicial('N');
                $atividade->setIdAtividade(
                    $sqAtividade->getId()
                );

                $this->_concluirAtividadesAnteriores(
                    $atividade->getIdProtocolo(),
                    $atividade->getIdUsuario(),
                    $atividade->getIdUnidade()
                );

                $this->getDefaultEntityManager()->persist($atividade);
                $this->getDefaultEntityManager()->flush();

                if(($nome != null) && ($valor != null)) {
                    $this->adicionarAtributoAndamento($atividade, $valor, $nome);
                }

                return true;
            }

            return false;

        } catch (Exception $ex) {
            throw $ex;
        }
    }

    private function _concluirAtividadesAnteriores($protocolo, $usuarioConclusao, $unidade)
    {
        $repAtividade = $this->getRepository();
        $atividades = $repAtividade->findBy(array(
            'idProtocolo' => $protocolo,
            'idUnidade' => $unidade,
            'dthConclusao' => null
        ));

        if (count($atividades)) {
            /** @var \Application\Entity\Atividade $atividade */
            foreach($atividades as $atividade) {
                $atividade->setDthConclusao(new \DateTime());
                $atividade->setIdUsuarioConclusao($usuarioConclusao);
                $atividade->setSinInicial('S');

                $this->getDefaultEntityManager()->persist($atividade);
            }

            $this->getDefaultEntityManager()->flush();
        }
    }

    public function getRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Atividade');
    }

    public function getUnidadeRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Unidade');
    }

    public function getSqAtividadeRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\SqAtividade');
    }
}
