<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Documento
 *
 * @ORM\Table(name="documento")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\DocumentoRepository")
 */
class Documento extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_documento", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idDocumentos;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\RelProtocoloProtocolo", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_documento", referencedColumnName="id_protocolo_2")
     */
    private $relProtocoloProtocolo;

    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=50)
     */
    private $numero;

    /**
     * @var integer
     *
     * @ORM\Column(name="versao_lock", type="integer")
     */
    private $versaoLock;

    /**
     * @var \Application\Entity\Unidade
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Unidade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_unidade_responsavel", referencedColumnName="id_unidade")
     */
    private $idUnidadeResponsavel;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_documento_edoc", type="integer")
     */
    private $idDocumentoEdoc;

    /**
     * @var string
     *
     * @ORM\Column(name="conteudo", type="text")
     */
    private $conteudo;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Serie", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_serie", referencedColumnName="id_serie")
     */
    private $idSerie;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Procedimento", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_procedimento", referencedColumnName="id_procedimento")
     */
    private $idProcedimento;

    /**
     * @var string
     *
     * @ORM\Column(name="conteudo_assinatura", type="text")
     */
    private $conteudoAssinatura;

    /**
     * @var string
     *
     * @ORM\Column(name="crc_assinatura", type="string", length=8)
     */
    private $crcAssinatura;

    /**
     * @var string
     *
     * @ORM\Column(name="qr_code_assinatura", type="text")
     */
    private $qrCodeAssinatura;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_formulario", type="string", length=1)
     */
    private $sinFormulario;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_editor", type="string", length=1)
     */
    private $staEditor;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_bloqueado", type="string", length=1)
     */
    private $sinBloqueado;

    /**
     * @var integer
     *
     * @ORM\OneToMany(targetEntity="Application\Entity\Assinatura", cascade={"persist"}, fetch="LAZY", mappedBy="idDocumento")
     * @ORM\JoinColumn(name="id_documento", referencedColumnName="id_documento")
     */
    private $assinaturas;

    /**
     * Set idDocumento
     *
     * @param integer $idDocumento
     * @return Documento
     */
    public function setIdDocumento($idDocumento)
    {
        $this->idDocumentos = $idDocumento;

        return $this;
    }

    /**
     * Get idDocumento
     *
     * @return integer 
     */
    public function getIdDocumento()
    {
        return $this->idDocumentos;
    }

    /**
     * Set numero
     *
     * @param string $numero
     * @return Documento
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set versaoLock
     *
     * @param integer $versaoLock
     * @return Documento
     */
    public function setVersaoLock($versaoLock)
    {
        $this->versaoLock = $versaoLock;

        return $this;
    }

    /**
     * Get versaoLock
     *
     * @return integer 
     */
    public function getVersaoLock()
    {
        return $this->versaoLock;
    }

    /**
     * Set idUnidadeResponsavel
     *
     * @param \Application\Entity\Unidade $idUnidadeResponsavel
     * @return Documento
     */
    public function setIdUnidadeResponsavel($idUnidadeResponsavel)
    {
        $this->idUnidadeResponsavel = $idUnidadeResponsavel;

        return $this;
    }

    /**
     * Get idUnidadeResponsavel
     *
     * @return \Application\Entity\Unidade
     */
    public function getIdUnidadeResponsavel()
    {
        return $this->idUnidadeResponsavel;
    }

    /**
     * Set idDocumentoEdoc
     *
     * @param integer $idDocumentoEdoc
     * @return Documento
     */
    public function setIdDocumentoEdoc($idDocumentoEdoc)
    {
        $this->idDocumentoEdoc = $idDocumentoEdoc;

        return $this;
    }

    /**
     * Get idDocumentoEdoc
     *
     * @return integer 
     */
    public function getIdDocumentoEdoc()
    {
        return $this->idDocumentoEdoc;
    }

    /**
     * Set conteudo
     *
     * @param string $conteudo
     * @return Documento
     */
    public function setConteudo($conteudo)
    {
        $this->conteudo = $conteudo;

        return $this;
    }

    /**
     * Get conteudo
     *
     * @return string 
     */
    public function getConteudo()
    {
        return $this->conteudo;
    }

    /**
     * Set idSerie
     *
     * @param integer $idSerie
     * @return Documento
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
     * Set idProcedimento
     *
     * @param integer $idProcedimento
     * @return Documento
     */
    public function setIdProcedimento($idProcedimento)
    {
        $this->idProcedimento = $idProcedimento;

        return $this;
    }

    /**
     * Get idProcedimento
     *
     * @return integer 
     */
    public function getIdProcedimento()
    {
        return $this->idProcedimento;
    }

    /**
     * Set conteudoAssinatura
     *
     * @param string $conteudoAssinatura
     * @return Documento
     */
    public function setConteudoAssinatura($conteudoAssinatura)
    {
        $this->conteudoAssinatura = $conteudoAssinatura;

        return $this;
    }

    /**
     * Get conteudoAssinatura
     *
     * @return string 
     */
    public function getConteudoAssinatura()
    {
        return $this->conteudoAssinatura;
    }

    /**
     * Set crcAssinatura
     *
     * @param string $crcAssinatura
     * @return Documento
     */
    public function setCrcAssinatura($crcAssinatura)
    {
        $this->crcAssinatura = $crcAssinatura;

        return $this;
    }

    /**
     * Get crcAssinatura
     *
     * @return string 
     */
    public function getCrcAssinatura()
    {
        return $this->crcAssinatura;
    }

    /**
     * Set qrCodeAssinatura
     *
     * @param string $qrCodeAssinatura
     * @return Documento
     */
    public function setQrCodeAssinatura($qrCodeAssinatura)
    {
        $this->qrCodeAssinatura = $qrCodeAssinatura;

        return $this;
    }

    /**
     * Get qrCodeAssinatura
     *
     * @return string 
     */
    public function getQrCodeAssinatura()
    {
        return $this->qrCodeAssinatura;
    }

    /**
     * Set sinFormulario
     *
     * @param string $sinFormulario
     * @return Documento
     */
    public function setSinFormulario($sinFormulario)
    {
        $this->sinFormulario = $sinFormulario;

        return $this;
    }

    /**
     * Get sinFormulario
     *
     * @return string 
     */
    public function getSinFormulario()
    {
        return $this->sinFormulario;
    }

    /**
     * Set staEditor
     *
     * @param string $staEditor
     * @return Documento
     */
    public function setStaEditor($staEditor)
    {
        $this->staEditor = $staEditor;

        return $this;
    }

    /**
     * Get staEditor
     *
     * @return string 
     */
    public function getStaEditor()
    {
        return $this->staEditor;
    }

    /**
     * Set sinBloqueado
     *
     * @param string $sinBloqueado
     * @return Documento
     */
    public function setSinBloqueado($sinBloqueado)
    {
        $this->sinBloqueado = $sinBloqueado;

        return $this;
    }

    /**
     * Get sinBloqueado
     *
     * @return string 
     */
    public function getSinBloqueado()
    {
        return $this->sinBloqueado;
    }

    /**
     * @param int $relProtocoloProtocolo
     */
    public function setRelProtocoloProtocolo($relProtocoloProtocolo)
    {
        $this->relProtocoloProtocolo = $relProtocoloProtocolo;
    }

    /**
     * @return int
     */
    public function getRelProtocoloProtocolo()
    {
        return $this->relProtocoloProtocolo;
    }

    /**
     * @return int
     */
    public function getAssinaturas()
    {
        return $this->assinaturas;
    }
}
