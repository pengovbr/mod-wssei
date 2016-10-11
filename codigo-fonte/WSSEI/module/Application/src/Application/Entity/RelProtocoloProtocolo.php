<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * RelProtocoloProtocolo
 *
 * @ORM\Table(name="rel_protocolo_protocolo")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\RelProtocoloProtocoloRepository")
 */
class RelProtocoloProtocolo extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_protocolo_1", type="integer")
     */
    private $idProtocolo1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_protocolo_2", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idProtocolo2;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Usuario", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
     */
    private $idUsuario;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_associacao", type="string", length=1)
     */
    private $staAssociacao;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dth_associacao", type="datetime")
     */
    private $dthAssociacao;

    /**
     * @ORM\Column(name="id_unidade", type="integer")
     */
    private $idUnidade;

    /**
     * @var integer
     *
     * @ORM\Column(name="sequencia", type="integer")
     */
    private $sequencia;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_ciencia", type="string", length=1)
     */
    private $sinCiencia;

    /**
     * Set idProtocolo1
     *
     * @param integer $idProtocolo1
     * @return RelProtocoloProtocolo
     */
    public function setIdProtocolo1($idProtocolo1)
    {
        $this->idProtocolo1 = $idProtocolo1;

        return $this;
    }

    /**
     * Get idProtocolo1
     *
     * @return integer 
     */
    public function getIdProtocolo1()
    {
        return $this->idProtocolo1;
    }

    /**
     * Set idProtocolo2
     *
     * @param integer $idProtocolo2
     * @return RelProtocoloProtocolo
     */
    public function setIdProtocolo2($idProtocolo2)
    {
        $this->idProtocolo2 = $idProtocolo2;

        return $this;
    }

    /**
     * Get idProtocolo2
     *
     * @return integer 
     */
    public function getIdProtocolo2()
    {
        return $this->idProtocolo2;
    }

    /**
     * Set idUsuario
     *
     * @param integer $idUsuario
     * @return RelProtocoloProtocolo
     */
    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;

        return $this;
    }

    /**
     * Get idUsuario
     *
     * @return integer 
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    /**
     * Set staAssociacao
     *
     * @param string $staAssociacao
     * @return RelProtocoloProtocolo
     */
    public function setStaAssociacao($staAssociacao)
    {
        $this->staAssociacao = $staAssociacao;

        return $this;
    }

    /**
     * Get staAssociacao
     *
     * @return string 
     */
    public function getStaAssociacao()
    {
        return $this->staAssociacao;
    }

    /**
     * Set dthAssociacao
     *
     * @param \DateTime $dthAssociacao
     * @return RelProtocoloProtocolo
     */
    public function setDthAssociacao($dthAssociacao)
    {
        $this->dthAssociacao = $dthAssociacao;

        return $this;
    }

    /**
     * Get dthAssociacao
     *
     * @return \DateTime 
     */
    public function getDthAssociacao()
    {
        return $this->dthAssociacao;
    }

    /**
     * Set sequencia
     *
     * @param integer $sequencia
     * @return RelProtocoloProtocolo
     */
    public function setSequencia($sequencia)
    {
        $this->sequencia = $sequencia;

        return $this;
    }

    /**
     * Get sequencia
     *
     * @return integer 
     */
    public function getSequencia()
    {
        return $this->sequencia;
    }

    /**
     * Set sinCiencia
     *
     * @param string $sinCiencia
     * @return RelProtocoloProtocolo
     */
    public function setSinCiencia($sinCiencia)
    {
        $this->sinCiencia = $sinCiencia;

        return $this;
    }

    /**
     * Get sinCiencia
     *
     * @return string 
     */
    public function getSinCiencia()
    {
        return $this->sinCiencia;
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
