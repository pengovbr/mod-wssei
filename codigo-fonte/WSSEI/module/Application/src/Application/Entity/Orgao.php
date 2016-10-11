<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Orgao
 *
 * @ORM\Table(name="orgao")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\OrgaoRepository")
 */
class Orgao extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_orgao", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idOrgao;

    /**
     * @var string
     *
     * @ORM\Column(name="sigla", type="string", length=30)
     */
    private $sigla;

    /**
     * @var string
     *
     * @ORM\Column(name="descricao", type="string", length=100)
     */
    private $descricao;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_ativo", type="string", length=1)
     */
    private $sinAtivo;


    /**
     * Set idOrgao
     *
     * @param integer $idOrgao
     * @return Orgao
     */
    public function setIdOrgao($idOrgao)
    {
        $this->idOrgao = $idOrgao;

        return $this;
    }

    /**
     * Get idOrgao
     *
     * @return integer 
     */
    public function getIdOrgao()
    {
        return $this->idOrgao;
    }

    /**
     * Set sigla
     *
     * @param string $sigla
     * @return Orgao
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
     * @return Orgao
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
     * Set sinAtivo
     *
     * @param string $sinAtivo
     * @return Orgao
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
}
