<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Anexo
 *
 * @ORM\Table(name="assinatura")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\AssinaturaRepository")
 */
class Assinatura extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_assinatura", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idAssinatura;

    /**
     * @var \Application\Entity\Documento
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Documento", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_documento", referencedColumnName="id_documento")
     */
    private $idDocumento;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario", type="integer")
     */
    private $idUsuario;

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
     * @ORM\Column(name="nome", type="string", length=100)
     */
    private $nome;

    /**
     * @var integer
     *
     * @ORM\Column(name="tratamento", type="string", length=100)
     */
    private $tratamento;

    /**
     * @var integer
     *
     * @ORM\Column(name="cpf", type="integer")
     */
    private $cpf;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_atividade", type="integer")
     */
    private $idAtividade;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_forma_autenticacao", type="string", length=1)
     */
    private $staFormaAutenticacao;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_ativo", type="string", length=1)
     */
    private $sinAtivo;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_serie_certificado", type="string", length=64)
     */
    private $numeroSerieCertificado;

    /**
     * @var text
     *
     * @ORM\Column(name="p7s_base64", type="text")
     */
    private $p7sBase64;

    /**
     * @param int $idAssinatura
     */
    public function setIdAssinatura($idAssinatura)
    {
        $this->idAssinatura = $idAssinatura;
    }

    /**
     * @return int
     */
    public function getIdAssinatura()
    {
        return $this->idAssinatura;
    }

    /**
     * @param \Application\Entity\Documento $idDocumento
     */
    public function setIdDocumento($idDocumento)
    {
        $this->idDocumento = $idDocumento;
    }

    /**
     * @return \Application\Entity\Documento
     */
    public function getIdDocumento()
    {
        return $this->idDocumento;
    }

    /**
     * @param string $idUnidade
     */
    public function setIdUnidade($idUnidade)
    {
        $this->idUnidade = $idUnidade;
    }

    /**
     * @return string
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

    /**
     * @param int $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * @return int
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param int $tratamento
     */
    public function setTratamento($tratamento)
    {
        $this->tratamento = $tratamento;
    }

    /**
     * @return int
     */
    public function getTratamento()
    {
        return $this->tratamento;
    }

    /**
     * @param int $cpf
     */
    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
    }

    /**
     * @return int
     */
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * @param int $idAtividade
     */
    public function setIdAtividade($idAtividade)
    {
        $this->idAtividade = $idAtividade;
    }

    /**
     * @return int
     */
    public function getIdAtividade()
    {
        return $this->idAtividade;
    }

    /**
     * @param string $numeroSerieCertificado
     */
    public function setNumeroSerieCertificado($numeroSerieCertificado)
    {
        $this->numeroSerieCertificado = $numeroSerieCertificado;
    }

    /**
     * @return string
     */
    public function getNumeroSerieCertificado()
    {
        return $this->numeroSerieCertificado;
    }

    /**
     * @param \Application\Entity\text $p7sBase64
     */
    public function setP7sBase64($p7sBase64)
    {
        $this->p7sBase64 = $p7sBase64;
    }

    /**
     * @return \Application\Entity\text
     */
    public function getP7sBase64()
    {
        return $this->p7sBase64;
    }

    /**
     * @param string $sinAtivo
     */
    public function setSinAtivo($sinAtivo)
    {
        $this->sinAtivo = $sinAtivo;
    }

    /**
     * @return string
     */
    public function getSinAtivo()
    {
        return $this->sinAtivo;
    }

    /**
     * @param string $staFormaAutenticacao
     */
    public function setStaFormaAutenticacao($staFormaAutenticacao)
    {
        $this->staFormaAutenticacao = $staFormaAutenticacao;
    }

    /**
     * @return string
     */
    public function getStaFormaAutenticacao()
    {
        return $this->staFormaAutenticacao;
    }
}
