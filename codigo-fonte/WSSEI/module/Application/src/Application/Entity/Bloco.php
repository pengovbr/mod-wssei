<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Bloco
 *
 * @ORM\Table(name="bloco")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\BlocoRepository")
 */
class Bloco extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_bloco", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idBloco;

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
     * @var string
     *
     * @ORM\Column(name="descricao", type="string", length=250)
     */
    private $descricao;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_tipo", type="string", length=1)
     */
    private $staTipo;

    /**
     * @var string
     *
     * @ORM\Column(name="idx_bloco", type="string", length=500)
     */
    private $idxBloco;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_estado", type="string", length=1)
     */
    private $staEstado;

    /**
     * @var RelBlocoProtocolo
     *
     * @ORM\OneToMany(targetEntity="Application\Entity\RelBlocoProtocolo", cascade={"persist"}, fetch="LAZY", mappedBy="idBloco")
     * @ORM\JoinColumn(name="id_bloco", referencedColumnName="id_bloco")
     */
    private $relBlocoProtocolo;

    /**
     * @var RelBlocoProtocolo
     *
     * @ORM\OneToMany(targetEntity="Application\Entity\RelBlocoUnidade", cascade={"persist"}, fetch="LAZY", mappedBy="idBloco")
     * @ORM\JoinColumn(name="id_bloco", referencedColumnName="id_bloco")
     */
    private $relBlocoUnidade;

    /**
     * Set idBloco
     *
     * @param integer $idBloco
     * @return Bloco
     */
    public function setIdBloco($idBloco)
    {
        $this->idBloco = $idBloco;

        return $this;
    }

    /**
     * Get idBloco
     *
     * @return integer 
     */
    public function getIdBloco()
    {
        return $this->idBloco;
    }

    /**
     * Set idUnidade
     *
     * @param integer $idUnidade
     * @return Bloco
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
     * Set idUsuario
     *
     * @param integer $idUsuario
     * @return Bloco
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
     * Set descricao
     *
     * @param string $descricao
     * @return Bloco
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
     * Set staTipo
     *
     * @param string $staTipo
     * @return Bloco
     */
    public function setStaTipo($staTipo)
    {
        $this->staTipo = $staTipo;

        return $this;
    }

    /**
     * Get staTipo
     *
     * @return string 
     */
    public function getStaTipo()
    {
        return $this->staTipo;
    }

    /**
     * Set idxBloco
     *
     * @param string $idxBloco
     * @return Bloco
     */
    public function setIdxBloco($idxBloco)
    {
        $this->idxBloco = $idxBloco;

        return $this;
    }

    /**
     * Get idxBloco
     *
     * @return string 
     */
    public function getIdxBloco()
    {
        return $this->idxBloco;
    }

    /**
     * Set staEstado
     *
     * @param string $staEstado
     * @return Bloco
     */
    public function setStaEstado($staEstado)
    {
        $this->staEstado = $staEstado;

        return $this;
    }

    /**
     * Get staEstado
     *
     * @return string 
     */
    public function getStaEstado()
    {
        return $this->staEstado;
    }

    /**
     * @param \Application\Entity\RelBlocoProtocolo $relBlocoUnidade
     */
    public function setRelBlocoUnidade($relBlocoUnidade)
    {
        $this->relBlocoUnidade = $relBlocoUnidade;
    }

    /**
     * @return \Application\Entity\RelBlocoProtocolo
     */
    public function getRelBlocoUnidade()
    {
        return $this->relBlocoUnidade;
    }

    /**
     * @param \Application\Entity\RelBlocoProtocolo $relBlocoProtocolo
     */
    public function setRelBlocoProtocolo($relBlocoProtocolo)
    {
        $this->relBlocoProtocolo = $relBlocoProtocolo;
    }

    /**
     * @return \Application\Entity\RelBlocoProtocolo
     */
    public function getRelBlocoProtocolo()
    {
        return $this->relBlocoProtocolo;
    }
}
