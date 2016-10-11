<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * RetornoProgramado
 *
 * @ORM\Table(name="retorno_programado")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\RetornoProgramadoRepository")
 */
class RetornoProgramado extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_retorno_programado", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idRetornoProgramado;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Unidade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_unidade", referencedColumnName="id_unidade")
     */
    private $idUnidade;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Usuario", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
     */
    private $idUsuario;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Atividade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_atividade_envio", referencedColumnName="id_atividade")
     */
    private $idAtividadeEnvio;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Atividade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_atividade_retorno", referencedColumnName="id_atividade")
     */
    private $idAtividadeRetorno;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dta_programada", type="datetime")
     */
    private $dtaProgramada;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dth_alteracao", type="datetime")
     */
    private $dtaAlteracao;

    /**
     * Set idRetornoProgramado
     *
     * @param integer $idRetornoProgramado
     * @return RetornoProgramado
     */
    public function setIdRetornoProgramado($idRetornoProgramado)
    {
        $this->idRetornoProgramado = $idRetornoProgramado;

        return $this;
    }

    /**
     * Get idRetornoProgramado
     *
     * @return integer 
     */
    public function getIdRetornoProgramado()
    {
        return $this->idRetornoProgramado;
    }

    /**
     * Set idAtividadeEnvio
     *
     * @param integer $idAtividadeEnvio
     * @return RetornoProgramado
     */
    public function setIdAtividadeEnvio($idAtividadeEnvio)
    {
        $this->idAtividadeEnvio = $idAtividadeEnvio;

        return $this;
    }

    /**
     * Get idAtividadeEnvio
     *
     * @return integer 
     */
    public function getIdAtividadeEnvio()
    {
        return $this->idAtividadeEnvio;
    }

    /**
     * Set idAtividadeRetorno
     *
     * @param integer $idAtividadeRetorno
     * @return RetornoProgramado
     */
    public function setIdAtividadeRetorno($idAtividadeRetorno)
    {
        $this->idAtividadeRetorno = $idAtividadeRetorno;

        return $this;
    }

    /**
     * Get idAtividadeRetorno
     *
     * @return integer 
     */
    public function getIdAtividadeRetorno()
    {
        return $this->idAtividadeRetorno;
    }

    /**
     * Set dtaProgramada
     *
     * @param \DateTime $dtaProgramada
     * @return RetornoProgramado
     */
    public function setDtaProgramada($dtaProgramada)
    {
        $this->dtaProgramada = $dtaProgramada;

        return $this;
    }

    /**
     * Get dtaProgramada
     *
     * @return \DateTime 
     */
    public function getDtaProgramada()
    {
        return $this->dtaProgramada;
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

    /**
     * @param \DateTime $dtaAlteracao
     */
    public function setDtaAlteracao($dtaAlteracao)
    {
        $this->dtaAlteracao = $dtaAlteracao;
    }

    /**
     * @return \DateTime
     */
    public function getDtaAlteracao()
    {
        return $this->dtaAlteracao;
    }

    /**
     * @param int $idUsuario
     */
    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    /**
     * @return int
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }
}
