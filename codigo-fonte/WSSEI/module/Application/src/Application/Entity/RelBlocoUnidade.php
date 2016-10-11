<?php
/**
 * Created by IntelliJ IDEA.
 * User: marioeugenio
 * Date: 1/29/16
 * Time: 2:51 PM
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * RelBlocoUnidade
 *
 * @ORM\Table(name="rel_bloco_unidade")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\RelBlocoUnidadeRepository")
 */
class RelBlocoUnidade extends AbstractEntity
{
    /**
     * @var \Application\Entity\Unidade
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Unidade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_unidade", referencedColumnName="id_unidade")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUnidade;

    /**
     * @var \Application\Entity\Bloco
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Bloco", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_bloco", referencedColumnName="id_bloco")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idBloco;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_retornado", type="string", length=1)
     */
    private $sinRetornado;

    /**
     * @param \Application\Entity\Bloco $idBloco
     */
    public function setIdBloco($idBloco)
    {
        $this->idBloco = $idBloco;
    }

    /**
     * @return \Application\Entity\Bloco
     */
    public function getIdBloco()
    {
        return $this->idBloco;
    }

    /**
     * @param \Application\Entity\Unidade $idUnidade
     */
    public function setIdUnidade($idUnidade)
    {
        $this->idUnidade = $idUnidade;
    }

    /**
     * @return \Application\Entity\Unidade
     */
    public function getIdUnidade()
    {
        return $this->idUnidade;
    }

    /**
     * @param string $sinRetornado
     */
    public function setSinRetornado($sinRetornado)
    {
        $this->sinRetornado = $sinRetornado;
    }

    /**
     * @return string
     */
    public function getSinRetornado()
    {
        return $this->sinRetornado;
    }
} 