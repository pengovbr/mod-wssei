<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Application\Entity\FuncaoEntity
 *
 * @ORM\Table(name="DBREVALIDACAO.TB_SNR_FUNCAO")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\FuncaoRepository")
 */
class FuncaoEntity extends AbstractEntity
{
    /**
     * @var integer $coFuncao
     *
     * @ORM\Column(name="CO_FUNCAO", type="integer", length=3, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="DBREVALIDACAO.SQ_CO_FUNCAO", initialValue=1, allocationSize=1000)
     */
    private $coFuncao;

    /**
     * @var string $noFuncao
     *
     * @ORM\Column(name="NO_FUNCAO", type="string", length=50, nullable=false)
     */
    private $noFuncao;

    /**
     * @var string $dsFuncao
     *
     * @ORM\Column(name="DS_FUNCAO", type="string", length=100, nullable=false)
     */
    private $dsFuncao;


    /**
     * @return the $coFuncao
     */
    public function getCoFuncao()
    {
        return $this->coFuncao;
    }

    /**
     * @return the $noFuncao
     */
    public function getNoFuncao()
    {
        return $this->noFuncao;
    }

    /**
     * @return the $dsFuncao
     */
    public function getDsFuncao()
    {
        return $this->dsFuncao;
    }

    /**
     * @param number $coFuncao
     */
    public function setCoFuncao($coFuncao)
    {
        $this->coFuncao = $coFuncao;
    }

    /**
     * @param string $noFuncao
     */
    public function setNoFuncao($noFuncao)
    {
        $this->noFuncao = $noFuncao;
    }

    /**
     * @param string $dsFuncao
     */
    public function setDsFuncao($dsFuncao)
    {
        $this->dsFuncao = $dsFuncao;
    }
}
