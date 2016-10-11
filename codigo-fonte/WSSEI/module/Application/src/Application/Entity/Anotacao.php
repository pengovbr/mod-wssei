<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Anotacao
 *
 * @ORM\Table(name="anotacao")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\AnotacaoRepository")
 */
class Anotacao extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_anotacao", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idAnotacao;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Protocolo", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_protocolo", referencedColumnName="id_protocolo")
     */
    private $idProtocolo;

    /**
     * @var string
     *
     * @ORM\Column(name="descricao", type="string", length=255)
     */
    private $descricao;

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
     * @ORM\Column(name="id_usuario", type="integer")
     */
    private $idUsuario;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dth_anotacao", type="datetime")
     */
    private $dthAnotacao;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_prioridade", type="string", length=1)
     */
    private $sinPrioridade;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_anotacao", type="string", length=1)
     */
    private $staAnotacao;

    /**
     * Set idAnotacao
     *
     * @param integer $idAnotacao
     * @return Anotacao
     */
    public function setIdAnotacao($idAnotacao)
    {
        $this->idAnotacao = $idAnotacao;

        return $this;
    }

    /**
     * Get idAnotacao
     *
     * @return integer 
     */
    public function getIdAnotacao()
    {
        return $this->idAnotacao;
    }

    /**
     * Set idProtocolo
     *
     * @param integer $idProtocolo
     * @return Anotacao
     */
    public function setIdProtocolo($idProtocolo)
    {
        $this->idProtocolo = $idProtocolo;

        return $this;
    }

    /**
     * Get idProtocolo
     *
     * @return integer 
     */
    public function getIdProtocolo()
    {
        return $this->idProtocolo;
    }

    /**
     * Set descricao
     *
     * @param string $descricao
     * @return Anotacao
     */
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;

        return $this;
    }

    /**
     * Get descricao
     *
     * @return string 
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * Set dthAnotacao
     *
     * @param \DateTime $dthAnotacao
     * @return Anotacao
     */
    public function setDthAnotacao($dthAnotacao)
    {
        $this->dthAnotacao = $dthAnotacao;

        return $this;
    }

    /**
     * Get dthAnotacao
     *
     * @return \DateTime 
     */
    public function getDthAnotacao()
    {
        return $this->dthAnotacao;
    }

    /**
     * Set sinPrioridade
     *
     * @param string $sinPrioridade
     * @return Anotacao
     */
    public function setSinPrioridade($sinPrioridade)
    {
        $this->sinPrioridade = $sinPrioridade;

        return $this;
    }

    /**
     * Get sinPrioridade
     *
     * @return string 
     */
    public function getSinPrioridade()
    {
        return $this->sinPrioridade;
    }

    /**
     * Set staAnotacao
     *
     * @param string $staAnotacao
     * @return Anotacao
     */
    public function setStaAnotacao($staAnotacao)
    {
        $this->staAnotacao = $staAnotacao;

        return $this;
    }

    /**
     * Get staAnotacao
     *
     * @return string 
     */
    public function getStaAnotacao()
    {
        return $this->staAnotacao;
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
