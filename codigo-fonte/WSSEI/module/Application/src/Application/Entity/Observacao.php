<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Observacao
 *
 * @ORM\Table(name="observacao")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ObservacaoRepository")
 */
class Observacao extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_observacao", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idObservacao;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="Application\Entity\Protocolo", cascade={"persist"}, fetch="LAZY", mappedBy="idProtocolo")
     * @ORM\JoinColumn(name="id_protocolo", referencedColumnName="id_protocolo")
     */
    private $idProtocolo;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Unidade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_unidade", referencedColumnName="id_unidade")
     */
    private $idUnidade;

    /**
     * @var string
     *
     * @ORM\Column(name="descricao", type="text")
     */
    private $descricao;

    /**
     * Set idObservacao
     *
     * @param integer $idObservacao
     * @return Observacao
     */
    public function setIdObservacao($idObservacao)
    {
        $this->idObservacao = $idObservacao;

        return $this;
    }

    /**
     * Get idObservacao
     *
     * @return integer 
     */
    public function getIdObservacao()
    {
        return $this->idObservacao;
    }

    /**
     * Set idProtocolo
     *
     * @param integer $idProtocolo
     * @return Observacao
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
     * Set idUnidade
     *
     * @param integer $idUnidade
     * @return Observacao
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
     * Set descricao
     *
     * @param string $descricao
     * @return Observacao
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
}
