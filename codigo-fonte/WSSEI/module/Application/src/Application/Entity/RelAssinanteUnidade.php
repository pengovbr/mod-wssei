<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * RelAssinanteUnidade
 *
 * @ORM\Table(name="rel_assinante_unidade")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\RelAssinanteUnidadeRepository")
 */
class RelAssinanteUnidade extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_unidade", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUnidade;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Assinante", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_assinante", referencedColumnName="id_assinante")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idAssinante;

    /**
     * @param int $idAssinante
     */
    public function setIdAssinante($idAssinante)
    {
        $this->idAssinante = $idAssinante;
    }

    /**
     * @return int
     */
    public function getIdAssinante()
    {
        return $this->idAssinante;
    }

    /**
     * @param int $idUnidade
     */
    public function setIdUnidade($idUnidade)
    {
        $this->idUnidade = $idUnidade;
    }

    /**
     * @return int
     */
    public function getIdUnidade()
    {
        return $this->idUnidade;
    }


}
