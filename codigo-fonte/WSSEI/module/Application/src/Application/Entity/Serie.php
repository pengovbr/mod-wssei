<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Serie
 *
 * @ORM\Table(name="serie")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\SerieRepository")
 */
class Serie extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_serie", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idSerie;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=50)
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
     * @ORM\Column(name="sin_interessado", type="string", length=1)
     */
    private $sinInteressado;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_destinatario", type="string", length=1)
     */
    private $sinDestinatario;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_numeracao", type="string", length=1)
     */
    private $staNumeracao;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_assinatura_publicacao", type="string", length=1)
     */
    private $sinAssinaturaPublicacao;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_aplicabilidade", type="string", length=1)
     */
    private $staAplicabilidade;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_interno", type="string", length=1)
     */
    private $sinInterno;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set idSerie
     *
     * @param integer $idSerie
     * @return Serie
     */
    public function setIdSerie($idSerie)
    {
        $this->idSerie = $idSerie;

        return $this;
    }

    /**
     * Get idSerie
     *
     * @return integer 
     */
    public function getIdSerie()
    {
        return $this->idSerie;
    }

    /**
     * Set nome
     *
     * @param string $nome
     * @return Serie
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
     * @return Serie
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
     * @return Serie
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
     * Set sinInteressado
     *
     * @param string $sinInteressado
     * @return Serie
     */
    public function setSinInteressado($sinInteressado)
    {
        $this->sinInteressado = $sinInteressado;

        return $this;
    }

    /**
     * Get sinInteressado
     *
     * @return string 
     */
    public function getSinInteressado()
    {
        return $this->sinInteressado;
    }

    /**
     * Set sinDestinatario
     *
     * @param string $sinDestinatario
     * @return Serie
     */
    public function setSinDestinatario($sinDestinatario)
    {
        $this->sinDestinatario = $sinDestinatario;

        return $this;
    }

    /**
     * Get sinDestinatario
     *
     * @return string 
     */
    public function getSinDestinatario()
    {
        return $this->sinDestinatario;
    }

    /**
     * Set staNumeracao
     *
     * @param string $staNumeracao
     * @return Serie
     */
    public function setStaNumeracao($staNumeracao)
    {
        $this->staNumeracao = $staNumeracao;

        return $this;
    }

    /**
     * Get staNumeracao
     *
     * @return string 
     */
    public function getStaNumeracao()
    {
        return $this->staNumeracao;
    }

    /**
     * Set sinAssinaturaPublicacao
     *
     * @param string $sinAssinaturaPublicacao
     * @return Serie
     */
    public function setSinAssinaturaPublicacao($sinAssinaturaPublicacao)
    {
        $this->sinAssinaturaPublicacao = $sinAssinaturaPublicacao;

        return $this;
    }

    /**
     * Get sinAssinaturaPublicacao
     *
     * @return string 
     */
    public function getSinAssinaturaPublicacao()
    {
        return $this->sinAssinaturaPublicacao;
    }

    /**
     * Set staAplicabilidade
     *
     * @param string $staAplicabilidade
     * @return Serie
     */
    public function setStaAplicabilidade($staAplicabilidade)
    {
        $this->staAplicabilidade = $staAplicabilidade;

        return $this;
    }

    /**
     * Get staAplicabilidade
     *
     * @return string 
     */
    public function getStaAplicabilidade()
    {
        return $this->staAplicabilidade;
    }

    /**
     * Set sinInterno
     *
     * @param string $sinInterno
     * @return Serie
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
}
