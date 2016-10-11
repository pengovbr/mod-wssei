<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Procedimento
 *
 * @ORM\Table(name="procedimento")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ProcedimentoRepository")
 */
class Procedimento extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_procedimento", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idProcedimento;

    /**
     * @var \Application\Entity\Protocolo
     *
     * @ORM\OneToOne(targetEntity="Application\Entity\Protocolo", cascade={"persist"}, fetch="LAZY", mappedBy="idProtocolo")
     * @ORM\JoinColumn(name="id_procedimento", referencedColumnName="id_protocolo")
     */
    private $protocolo;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\TipoProcedimento", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_tipo_procedimento", referencedColumnName="id_tipo_procedimento")
     */
    private $idTipoProcedimento;

    /**
     * @var integer
     *
     * @ORM\OneToMany(targetEntity="Application\Entity\Documento", cascade={"persist"}, fetch="LAZY", mappedBy="idProcedimento")
     * @ORM\JoinColumn(name="id_procedimento", referencedColumnName="id_procedimento")
     */
    private $documentos;

    /**
     * @var integer
     *
     * @ORM\Column(name="versao_lock", type="integer")
     */
    private $versaoLock;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_ouvidoria", type="string", length=1)
     */
    private $staOuvidoria;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_ciencia", type="string", length=1)
     */
    private $sinCiencia;

    /**
     * Set idProcedimento
     *
     * @param integer $idProcedimento
     * @return Procedimento
     */
    public function setIdProcedimento($idProcedimento)
    {
        $this->idProcedimento = $idProcedimento;

        return $this;
    }

    /**
     * Get idProcedimento
     *
     * @return integer 
     */
    public function getIdProcedimento()
    {
        return $this->idProcedimento;
    }

    /**
     * Set idTipoProcedimento
     *
     * @param integer $idTipoProcedimento
     * @return Procedimento
     */
    public function setIdTipoProcedimento($idTipoProcedimento)
    {
        $this->idTipoProcedimento = $idTipoProcedimento;

        return $this;
    }

    /**
     * Get idTipoProcedimento
     *
     * @return integer 
     */
    public function getIdTipoProcedimento()
    {
        return $this->idTipoProcedimento;
    }

    /**
     * Set versaoLock
     *
     * @param integer $versaoLock
     * @return Procedimento
     */
    public function setVersaoLock($versaoLock)
    {
        $this->versaoLock = $versaoLock;

        return $this;
    }

    /**
     * Get versaoLock
     *
     * @return integer 
     */
    public function getVersaoLock()
    {
        return $this->versaoLock;
    }

    /**
     * Set staOuvidoria
     *
     * @param string $staOuvidoria
     * @return Procedimento
     */
    public function setStaOuvidoria($staOuvidoria)
    {
        $this->staOuvidoria = $staOuvidoria;

        return $this;
    }

    /**
     * Get staOuvidoria
     *
     * @return string 
     */
    public function getStaOuvidoria()
    {
        return $this->staOuvidoria;
    }

    /**
     * Set sinCiencia
     *
     * @param string $sinCiencia
     * @return Procedimento
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
     * @param int $protocolo
     */
    public function setProtocolo($protocolo)
    {
        $this->protocolo = $protocolo;
    }

    /**
     * @return \Application\Entity\Protocolo
     */
    public function getProtocolo()
    {
        return $this->protocolo;
    }

    /**
     * @return int
     */
    public function getDocumentos()
    {
        return $this->documentos;
    }

}
