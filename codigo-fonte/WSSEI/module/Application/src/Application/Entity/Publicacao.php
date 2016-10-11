<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Publicacao
 *
 * @ORM\Table(name="publicacao")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\PublicacaoRepository")
 */
class Publicacao extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_publicacao", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPublicacao;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dth_agendamento", type="datetime")
     */
    private $dthAgendamento;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_motivo", type="string", length=1)
     */
    private $staMotivo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dta_disponibilizacao", type="datetime")
     */
    private $dtaDisponibilizacao;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dta_publicacao_io", type="datetime")
     */
    private $dtaPublicacaoIo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dta_publicacao", type="datetime")
     */
    private $dtaPublicacao;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Documento", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_documento", referencedColumnName="id_documento")
     */
    private $idDocumento;


    /**
     * Set idPublicacao
     *
     * @param integer $idPublicacao
     * @return Publicacao
     */
    public function setIdPublicacao($idPublicacao)
    {
        $this->idPublicacao = $idPublicacao;

        return $this;
    }

    /**
     * Get idPublicacao
     *
     * @return integer 
     */
    public function getIdPublicacao()
    {
        return $this->idPublicacao;
    }

    /**
     * Set dthAgendamento
     *
     * @param \DateTime $dthAgendamento
     * @return Publicacao
     */
    public function setDthAgendamento($dthAgendamento)
    {
        $this->dthAgendamento = $dthAgendamento;

        return $this;
    }

    /**
     * Get dthAgendamento
     *
     * @return \DateTime 
     */
    public function getDthAgendamento()
    {
        return $this->dthAgendamento;
    }

    /**
     * Set staMotivo
     *
     * @param string $staMotivo
     * @return Publicacao
     */
    public function setStaMotivo($staMotivo)
    {
        $this->staMotivo = $staMotivo;

        return $this;
    }

    /**
     * Get staMotivo
     *
     * @return string 
     */
    public function getStaMotivo()
    {
        return $this->staMotivo;
    }

    /**
     * Set dtaDisponibilizacao
     *
     * @param \DateTime $dtaDisponibilizacao
     * @return Publicacao
     */
    public function setDtaDisponibilizacao($dtaDisponibilizacao)
    {
        $this->dtaDisponibilizacao = $dtaDisponibilizacao;

        return $this;
    }

    /**
     * Get dtaDisponibilizacao
     *
     * @return \DateTime 
     */
    public function getDtaDisponibilizacao()
    {
        return $this->dtaDisponibilizacao;
    }

    /**
     * Set dtaPublicacaoIo
     *
     * @param \DateTime $dtaPublicacaoIo
     * @return Publicacao
     */
    public function setDtaPublicacaoIo($dtaPublicacaoIo)
    {
        $this->dtaPublicacaoIo = $dtaPublicacaoIo;

        return $this;
    }

    /**
     * Get dtaPublicacaoIo
     *
     * @return \DateTime 
     */
    public function getDtaPublicacaoIo()
    {
        return $this->dtaPublicacaoIo;
    }

    /**
     * Set dtaPublicacao
     *
     * @param \DateTime $dtaPublicacao
     * @return Publicacao
     */
    public function setDtaPublicacao($dtaPublicacao)
    {
        $this->dtaPublicacao = $dtaPublicacao;

        return $this;
    }

    /**
     * Get dtaPublicacao
     *
     * @return \DateTime 
     */
    public function getDtaPublicacao()
    {
        return $this->dtaPublicacao;
    }

    /**
     * @param int $idDocumento
     */
    public function setIdDocumento($idDocumento)
    {
        $this->idDocumento = $idDocumento;
    }

    /**
     * @return int
     */
    public function getIdDocumento()
    {
        return $this->idDocumento;
    }
}
