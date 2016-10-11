<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Unidade
 *
 * @ORM\Table(name="dbsip.dbo.unidade")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\UnidadeSIPRepository")
 */
class UnidadeSIP extends AbstractEntity
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_unidade", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idUnidade;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_ativo", type="string", length=1)
     */
    private $sinAtivo;

    /**
     * @var string
     *
     * @ORM\Column(name="sigla", type="string", length=30)
     */
    private $sigla;

    /**
     * @var string
     *
     * @ORM\Column(name="descricao", type="string", length=250)
     */
    private $descricao;


    /**
     * Set idUnidade
     *
     * @param integer $idUnidade
     * @return Unidade
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
     * Set sinAtivo
     *
     * @param string $sinAtivo
     * @return Unidade
     */
    public function setSinAtivo($sinAtivo)
    {
        $this->sinAtivo = $sinAtivo;

        return $this;
    }

    /**
     * Get sinAtivo
     *
     * @return string 
     */
    public function getSinAtivo()
    {
        return $this->sinAtivo;
    }

    /**
     * Set sigla
     *
     * @param string $sigla
     * @return Unidade
     */
    public function setSigla($sigla)
    {
        $this->sigla = $sigla;

        return $this;
    }

    /**
     * Get sigla
     *
     * @return string 
     */
    public function getSigla()
    {
        return $this->sigla;
    }

    /**
     * Set descricao
     *
     * @param string $descricao
     * @return Unidade
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
