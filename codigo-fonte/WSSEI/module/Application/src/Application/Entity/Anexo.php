<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Anexo
 *
 * @ORM\Table(name="anexo")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\AnexoRepository")
 */
class Anexo extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_anexo", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idAnexo;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=255)
     */
    private $nome;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_protocolo", type="integer")
     */
    private $idProtocolo;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_ativo", type="string", length=1)
     */
    private $sinAtivo;

    /**
     * @var integer
     *
     * @ORM\Column(name="tamanho", type="integer")
     */
    private $tamanho;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dth_inclusao", type="datetime")
     */
    private $dthInclusao;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario", type="integer")
     */
    private $idUsuario;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=32)
     */
    private $hash;


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
     * Set idAnexo
     *
     * @param integer $idAnexo
     * @return Anexo
     */
    public function setIdAnexo($idAnexo)
    {
        $this->idAnexo = $idAnexo;

        return $this;
    }

    /**
     * Get idAnexo
     *
     * @return integer 
     */
    public function getIdAnexo()
    {
        return $this->idAnexo;
    }

    /**
     * Set nome
     *
     * @param string $nome
     * @return Anexo
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
     * @param int $idProtocolo
     */
    public function setIdProtocolo($idProtocolo)
    {
        $this->idProtocolo = $idProtocolo;
    }

    /**
     * @return int
     */
    public function getIdProtocolo()
    {
        return $this->idProtocolo;
    }

    /**
     * Set sinAtivo
     *
     * @param string $sinAtivo
     * @return Anexo
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
     * Set tamanho
     *
     * @param integer $tamanho
     * @return Anexo
     */
    public function setTamanho($tamanho)
    {
        $this->tamanho = $tamanho;

        return $this;
    }

    /**
     * Get tamanho
     *
     * @return integer 
     */
    public function getTamanho()
    {
        return $this->tamanho;
    }

    /**
     * Set dthInclusao
     *
     * @param \DateTime $dthInclusao
     * @return Anexo
     */
    public function setDthInclusao($dthInclusao)
    {
        $this->dthInclusao = $dthInclusao;

        return $this;
    }

    /**
     * Get dthInclusao
     *
     * @return \DateTime 
     */
    public function getDthInclusao()
    {
        return $this->dthInclusao;
    }

    /**
     * Set idUsuario
     *
     * @param integer $idUsuario
     * @return Anexo
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
     * Set hash
     *
     * @param string $hash
     * @return Anexo
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }
}
