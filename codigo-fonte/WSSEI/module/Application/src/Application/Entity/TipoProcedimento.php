<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * TipoProcedimento
 *
 * @ORM\Table(name="tipo_procedimento")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\TipoProcedimentoRepository")
 */
class TipoProcedimento extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_tipo_procedimento", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idTipoProcedimento;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=100)
     */
    private $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="descricao", type="string", length=250)
     */
    private $descricao;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_ativo", type="string", length=1)
     */
    private $sinAtivo;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_interno", type="string", length=1)
     */
    private $sinInterno;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_ouvidoria", type="string", length=1)
     */
    private $sinOuvidoria;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_individual", type="string", length=1)
     */
    private $sinIndividual;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_grau_sigilo_sugestao", type="string", length=1)
     */
    private $staGrauSigiloSugestao;


    /**
     * Set idTipoProcedimento
     *
     * @param integer $idTipoProcedimento
     * @return TipoProcedimento
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
     * Set nome
     *
     * @param string $nome
     * @return TipoProcedimento
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get nome
     *
     * @return string 
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set descricao
     *
     * @param string $descricao
     * @return TipoProcedimento
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
     * @return TipoProcedimento
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
     * Set sinInterno
     *
     * @param string $sinInterno
     * @return TipoProcedimento
     */
    public function setSinInterno($sinInterno)
    {
        $this->sinInterno = $sinInterno;

        return $this;
    }

    /**
     * Get sinInterno
     *
     * @return string 
     */
    public function getSinInterno()
    {
        return $this->sinInterno;
    }

    /**
     * Set sinOuvidoria
     *
     * @param string $sinOuvidoria
     * @return TipoProcedimento
     */
    public function setSinOuvidoria($sinOuvidoria)
    {
        $this->sinOuvidoria = $sinOuvidoria;

        return $this;
    }

    /**
     * Get sinOuvidoria
     *
     * @return string 
     */
    public function getSinOuvidoria()
    {
        return $this->sinOuvidoria;
    }

    /**
     * Set sinIndividual
     *
     * @param string $sinIndividual
     * @return TipoProcedimento
     */
    public function setSinIndividual($sinIndividual)
    {
        $this->sinIndividual = $sinIndividual;

        return $this;
    }

    /**
     * Get sinIndividual
     *
     * @return string 
     */
    public function getSinIndividual()
    {
        return $this->sinIndividual;
    }

    /**
     * Set staGrauSigiloSugestao
     *
     * @param string $staGrauSigiloSugestao
     * @return TipoProcedimento
     */
    public function setStaGrauSigiloSugestao($staGrauSigiloSugestao)
    {
        $this->staGrauSigiloSugestao = $staGrauSigiloSugestao;

        return $this;
    }

    /**
     * Get staGrauSigiloSugestao
     *
     * @return string 
     */
    public function getStaGrauSigiloSugestao()
    {
        return $this->staGrauSigiloSugestao;
    }
}
