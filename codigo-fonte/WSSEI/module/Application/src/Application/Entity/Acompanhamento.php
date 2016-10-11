<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Acompanhamento
 *
 * @ORM\Table(name="acompanhamento")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\AcompanhamentoRepository")
 */
class Acompanhamento extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_acompanhamento", type="integer")
     */
    private $idAcompanhamento;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Unidade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_unidade", referencedColumnName="id_unidade")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idUnidade;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\GrupoAcompanhamento", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_grupo_acompanhamento", referencedColumnName="id_grupo_acompanhamento")
     */
    private $idGrupoAcompanhamento;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Protocolo", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_protocolo", referencedColumnName="id_protocolo")
     */
    private $idProtocolo;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario_gerador", type="integer")
     */
    private $idUsuarioGerador;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dth_geracao", type="datetime")
     */
    private $dthGeracao;

    /**
     * @var string
     *
     * @ORM\Column(name="observacao", type="string", length=250)
     */
    private $observacao;

    /**
     * @var integer
     *
     * @ORM\Column(name="tipo_visualizacao", type="integer")
     */
    private $tipoVisualizacao;

    /**
     * Set idAcompanhamento
     *
     * @param integer $idAcompanhamento
     * @return Acompanhamento
     */
    public function setIdAcompanhamento($idAcompanhamento)
    {
        $this->idAcompanhamento = $idAcompanhamento;

        return $this;
    }

    /**
     * Get idAcompanhamento
     *
     * @return integer 
     */
    public function getIdAcompanhamento()
    {
        return $this->idAcompanhamento;
    }

    /**
     * Set idUnidade
     *
     * @param integer $idUnidade
     * @return Acompanhamento
     */
    public function setIdUnidade($idUnidade)
    {
        $this->idUnidade = $idUnidade;

        return $this;
    }

    /**
     * Get idUnidade
     *
     * @return integer 
     */
    public function getIdUnidade()
    {
        return $this->idUnidade;
    }

    /**
     * Set idGrupoAcompanhamento
     *
     * @param integer $idGrupoAcompanhamento
     * @return Acompanhamento
     */
    public function setIdGrupoAcompanhamento($idGrupoAcompanhamento)
    {
        $this->idGrupoAcompanhamento = $idGrupoAcompanhamento;

        return $this;
    }

    /**
     * Get idGrupoAcompanhamento
     *
     * @return integer 
     */
    public function getIdGrupoAcompanhamento()
    {
        return $this->idGrupoAcompanhamento;
    }

    /**
     * Set idProtocolo
     *
     * @param integer $idProtocolo
     * @return Acompanhamento
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
     * Set idUsuarioGerador
     *
     * @param integer $idUsuarioGerador
     * @return Acompanhamento
     */
    public function setIdUsuarioGerador($idUsuarioGerador)
    {
        $this->idUsuarioGerador = $idUsuarioGerador;

        return $this;
    }

    /**
     * Get idUsuarioGerador
     *
     * @return integer 
     */
    public function getIdUsuarioGerador()
    {
        return $this->idUsuarioGerador;
    }

    /**
     * Set dthGeracao
     *
     * @param \DateTime $dthGeracao
     * @return Acompanhamento
     */
    public function setDthGeracao($dthGeracao)
    {
        $this->dthGeracao = $dthGeracao;

        return $this;
    }

    /**
     * Get dthGeracao
     *
     * @return \DateTime 
     */
    public function getDthGeracao()
    {
        return $this->dthGeracao;
    }

    /**
     * Set observacao
     *
     * @param string $observacao
     * @return Acompanhamento
     */
    public function setObservacao($observacao)
    {
        $this->observacao = $observacao;

        return $this;
    }

    /**
     * Get observacao
     *
     * @return string 
     */
    public function getObservacao()
    {
        return $this->observacao;
    }

    /**
     * Set tipoVisualizacao
     *
     * @param integer $tipoVisualizacao
     * @return Acompanhamento
     */
    public function setTipoVisualizacao($tipoVisualizacao)
    {
        $this->tipoVisualizacao = $tipoVisualizacao;

        return $this;
    }

    /**
     * Get tipoVisualizacao
     *
     * @return integer 
     */
    public function getTipoVisualizacao()
    {
        return $this->tipoVisualizacao;
    }
}
