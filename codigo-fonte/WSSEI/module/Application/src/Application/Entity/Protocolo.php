<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Protocolo
 *
 * @ORM\Table(name="protocolo")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ProtocoloRepository")
 */
class Protocolo extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_protocolo", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idProtocolo;

    /**
     * @var \Application\Entity\Procedimento
     *
     * @ORM\OneToOne(targetEntity="Application\Entity\Procedimento", cascade={"persist"}, fetch="LAZY", mappedBy="idProcedimento")
     * @ORM\JoinColumn(name="id_protocolo", referencedColumnName="id_procedimento")
     */
    private $procedimento;

    /**
     * @var string
     *
     * @ORM\Column(name="protocolo_formatado", type="string", length=50)
     */
    private $protocoloFormatado;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_protocolo", type="string", length=1)
     */
    private $staProtocolo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dta_geracao", type="datetime")
     */
    private $dtaGeracao;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_estado", type="string", length=1)
     */
    private $staEstado;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_arquivamento", type="string", length=1)
     */
    private $staArquivamento;

    /**
     * @var string
     *
     * @ORM\Column(name="descricao", type="text")
     */
    private $descricao;

    /**
     * @var string
     *
     * @ORM\Column(name="protocolo_formatado_pesquisa", type="string", length=50)
     */
    private $protocoloFormatadoPesquisa;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dth_arquivamento", type="datetime")
     */
    private $dthArquivamento;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_grau_sigilo", type="string", length=1)
     */
    private $staGrauSigilo;

    /**
     * @var string
     *
     * @ORM\Column(name="sta_nivel_acesso_local", type="integer")
     */
    private $staNivelAcesso;

    /**
     * @var integer
     *
     * @ORM\OneToMany(targetEntity="Application\Entity\Atividade", cascade={"persist"}, fetch="LAZY", mappedBy="idProtocolo")
     * @ORM\JoinColumn(name="id_protocolo", referencedColumnName="id_protocolo")
     */
    private $atividades;

    /**
     * @var integer
     *
     * @ORM\OneToMany(targetEntity="Application\Entity\Acompanhamento", cascade={"persist"}, fetch="LAZY", mappedBy="idProtocolo")
     * @ORM\JoinColumn(name="id_protocolo", referencedColumnName="id_protocolo")
     */
    private $acompanhamento;

    
    private $relProtocoloProtocolo;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Unidade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_unidade_geradora", referencedColumnName="id_unidade")
     */
    private $idUnidadeGeradora;


    /**
     * Set idProtocolo
     *
     * @param integer $idProtocolo
     * @return Protocolo
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
     * Set protocoloFormatado
     *
     * @param string $protocoloFormatado
     * @return Protocolo
     */
    public function setProtocoloFormatado($protocoloFormatado)
    {
        $this->protocoloFormatado = $protocoloFormatado;

        return $this;
    }

    /**
     * Get protocoloFormatado
     *
     * @return string 
     */
    public function getProtocoloFormatado()
    {
        return $this->protocoloFormatado;
    }

    /**
     * Set staProtocolo
     *
     * @param string $staProtocolo
     * @return Protocolo
     */
    public function setStaProtocolo($staProtocolo)
    {
        $this->staProtocolo = $staProtocolo;

        return $this;
    }

    /**
     * Get staProtocolo
     *
     * @return string 
     */
    public function getStaProtocolo()
    {
        return $this->staProtocolo;
    }

    /**
     * Set dtaGeracao
     *
     * @param \DateTime $dtaGeracao
     * @return Protocolo
     */
    public function setDtaGeracao($dtaGeracao)
    {
        $this->dtaGeracao = $dtaGeracao;

        return $this;
    }

    /**
     * Get dtaGeracao
     *
     * @return \DateTime 
     */
    public function getDtaGeracao()
    {
        return $this->dtaGeracao;
    }

    /**
     * Set staEstado
     *
     * @param string $staEstado
     * @return Protocolo
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
     * Set staArquivamento
     *
     * @param string $staArquivamento
     * @return Protocolo
     */
    public function setStaArquivamento($staArquivamento)
    {
        $this->staArquivamento = $staArquivamento;

        return $this;
    }

    /**
     * Get staArquivamento
     *
     * @return string 
     */
    public function getStaArquivamento()
    {
        return $this->staArquivamento;
    }

    /**
     * Set descricao
     *
     * @param string $descricao
     * @return Protocolo
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
     * Set protocoloFormatadoPesquisa
     *
     * @param string $protocoloFormatadoPesquisa
     * @return Protocolo
     */
    public function setProtocoloFormatadoPesquisa($protocoloFormatadoPesquisa)
    {
        $this->protocoloFormatadoPesquisa = $protocoloFormatadoPesquisa;

        return $this;
    }

    /**
     * Get protocoloFormatadoPesquisa
     *
     * @return string 
     */
    public function getProtocoloFormatadoPesquisa()
    {
        return $this->protocoloFormatadoPesquisa;
    }

    /**
     * Set dthArquivamento
     *
     * @param \DateTime $dthArquivamento
     * @return Protocolo
     */
    public function setDthArquivamento($dthArquivamento)
    {
        $this->dthArquivamento = $dthArquivamento;

        return $this;
    }

    /**
     * Get dthArquivamento
     *
     * @return \DateTime 
     */
    public function getDthArquivamento()
    {
        return $this->dthArquivamento;
    }

    /**
     * Set staGrauSigilo
     *
     * @param string $staGrauSigilo
     * @return Protocolo
     */
    public function setStaGrauSigilo($staGrauSigilo)
    {
        $this->staGrauSigilo = $staGrauSigilo;

        return $this;
    }

    /**
     * Get staGrauSigilo
     *
     * @return string 
     */
    public function getStaGrauSigilo()
    {
        return $this->staGrauSigilo;
    }

    /**
     * @param int $atividades
     */
    public function setAtividades($atividades)
    {
        $this->atividades = $atividades;
    }

    /**
     * @return int
     */
    public function getAtividades()
    {
        return $this->atividades;
    }

    /**
     * @param string $staNivelAcesso
     */
    public function setStaNivelAcesso($staNivelAcesso)
    {
        $this->staNivelAcesso = $staNivelAcesso;
    }

    /**
     * @return string
     */
    public function getStaNivelAcesso()
    {
        return $this->staNivelAcesso;
    }

    /**
     * @param int $acompanhamento
     */
    public function setAcompanhamento($acompanhamento)
    {
        $this->acompanhamento = $acompanhamento;
    }

    /**
     * @return int
     */
    public function getAcompanhamento()
    {
        return $this->acompanhamento;
    }

    /**
     * @param int $idUnidadeGeradora
     */
    public function setIdUnidadeGeradora($idUnidadeGeradora)
    {
        $this->idUnidadeGeradora = $idUnidadeGeradora;
    }

    /**
     * @return int
     */
    public function getIdUnidadeGeradora()
    {
        return $this->idUnidadeGeradora;
    }

    /**
     * @return \Application\Entity\Procedimento
     */
    public function getProcedimento()
    {
        return $this->procedimento;
    }

    /**
     * @return \Application\Entity\RelProtocoloProtocolo
     */
    public function getRelProtocoloProtocolo()
    {
        return $this->relProtocoloProtocolo;
    }
}
