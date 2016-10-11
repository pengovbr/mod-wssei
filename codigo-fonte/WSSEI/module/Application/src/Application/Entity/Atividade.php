<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Atividade
 *
 * @ORM\Table(name="atividade")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\AtividadeRepository")
 */
class Atividade extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_atividade", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idAtividade;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Protocolo", cascade={"persist"}, fetch="LAZY")
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
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Tarefa", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_tarefa", referencedColumnName="id_tarefa")
     */
    private $idTarefa;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dth_abertura", type="datetime")
     */
    private $dthAbertura;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dth_conclusao", type="datetime")
     */
    private $dthConclusao;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Unidade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_unidade_origem", referencedColumnName="id_unidade")
     */
    private $idUnidadeOrigem;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Usuario", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_usuario_conclusao", referencedColumnName="id_usuario")
     */
    private $idUsuarioConclusao;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Usuario", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_usuario_visualizacao", referencedColumnName="id_usuario")
     */
    private $idUsuarioVisualizacao;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_inicial", type="string", length=1)
     */
    private $sinInicial;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dta_prazo", type="datetime")
     */
    private $dtaPrazo;

    /**
     * @var integer
     *
     * @ORM\Column(name="tipo_visualizacao", type="integer")
     */
    private $tipoVisualizacao;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Usuario", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
     */
    private $idUsuario;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Usuario", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_usuario_atribuicao", referencedColumnName="id_usuario")
     */
    private $idUsuarioAtribuicao;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Usuario", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_usuario_origem", referencedColumnName="id_usuario")
     */
    private $idUsuarioOrigem;

    /**
     * @var integer
     *
     * @ORM\OneToMany(targetEntity="Application\Entity\RetornoProgramado", cascade={"persist"}, fetch="LAZY", mappedBy="idAtividadeEnvio")
     * @ORM\JoinColumn(name="id_atividade", referencedColumnName="id_atividade_envio")
     */
    private $retornoProgamado;


    /**
     * Set idAtividade
     *
     * @param integer $idAtividade
     * @return Atividade
     */
    public function setIdAtividade($idAtividade)
    {
        $this->idAtividade = $idAtividade;

        return $this;
    }

    /**
     * Get idAtividade
     *
     * @return integer 
     */
    public function getIdAtividade()
    {
        return $this->idAtividade;
    }

    /**
     * Set idProtocolo
     *
     * @param integer $idProtocolo
     * @return Atividade
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
     * @return Atividade
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
     * Set dthAbertura
     *
     * @param \DateTime $dthAbertura
     * @return Atividade
     */
    public function setDthAbertura($dthAbertura)
    {
        $this->dthAbertura = $dthAbertura;

        return $this;
    }

    /**
     * Get dthAbertura
     *
     * @return \DateTime 
     */
    public function getDthAbertura()
    {
        return $this->dthAbertura;
    }

    /**
     * Set dthConclusao
     *
     * @param \DateTime $dthConclusao
     * @return Atividade
     */
    public function setDthConclusao($dthConclusao)
    {
        $this->dthConclusao = $dthConclusao;

        return $this;
    }

    /**
     * Get dthConclusao
     *
     * @return \DateTime 
     */
    public function getDthConclusao()
    {
        return $this->dthConclusao;
    }

    /**
     * Set idUnidadeOrigem
     *
     * @param integer $idUnidadeOrigem
     * @return Atividade
     */
    public function setIdUnidadeOrigem($idUnidadeOrigem)
    {
        $this->idUnidadeOrigem = $idUnidadeOrigem;

        return $this;
    }

    /**
     * Get idUnidadeOrigem
     *
     * @return integer 
     */
    public function getIdUnidadeOrigem()
    {
        return $this->idUnidadeOrigem;
    }

    /**
     * Set idUsuarioConclusao
     *
     * @param integer $idUsuarioConclusao
     * @return Atividade
     */
    public function setIdUsuarioConclusao($idUsuarioConclusao)
    {
        $this->idUsuarioConclusao = $idUsuarioConclusao;

        return $this;
    }

    /**
     * Get idUsuarioConclusao
     *
     * @return integer 
     */
    public function getIdUsuarioConclusao()
    {
        return $this->idUsuarioConclusao;
    }

    /**
     * Set sinInicial
     *
     * @param string $sinInicial
     * @return Atividade
     */
    public function setSinInicial($sinInicial)
    {
        $this->sinInicial = $sinInicial;

        return $this;
    }

    /**
     * Get sinInicial
     *
     * @return string 
     */
    public function getSinInicial()
    {
        return $this->sinInicial;
    }

    /**
     * Set dtaPrazo
     *
     * @param \DateTime $dtaPrazo
     * @return Atividade
     */
    public function setDtaPrazo($dtaPrazo)
    {
        $this->dtaPrazo = $dtaPrazo;

        return $this;
    }

    /**
     * Get dtaPrazo
     *
     * @return \DateTime 
     */
    public function getDtaPrazo()
    {
        return $this->dtaPrazo;
    }

    /**
     * Set tipoVisualizacao
     *
     * @param integer $tipoVisualizacao
     * @return Atividade
     */
    public function setTipoVisualizacao($tipoVisualizacao)
    {
        $this->tipoVisualizacao = $tipoVisualizacao;

        return $this;
    }

    /**
     * Get tipoVisualizacao
     *
     * @return integer 
     */
    public function getTipoVisualizacao()
    {
        return $this->tipoVisualizacao;
    }

    /**
     * Set idUsuario
     *
     * @param integer $idUsuario
     * @return Atividade
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
     * Set idUsuarioOrigem
     *
     * @param integer $idUsuarioOrigem
     * @return Atividade
     */
    public function setIdUsuarioOrigem($idUsuarioOrigem)
    {
        $this->idUsuarioOrigem = $idUsuarioOrigem;

        return $this;
    }

    /**
     * Get idUsuarioOrigem
     *
     * @return integer 
     */
    public function getIdUsuarioOrigem()
    {
        return $this->idUsuarioOrigem;
    }

    /**
     * @param int $idTarefa
     */
    public function setIdTarefa($idTarefa)
    {
        $this->idTarefa = $idTarefa;
    }

    /**
     * @return int
     */
    public function getIdTarefa()
    {
        return $this->idTarefa;
    }

    /**
     * @param int $idUsuarioVisualizacao
     */
    public function setIdUsuarioVisualizacao($idUsuarioVisualizacao)
    {
        $this->idUsuarioVisualizacao = $idUsuarioVisualizacao;
    }

    /**
     * @return int
     */
    public function getIdUsuarioVisualizacao()
    {
        return $this->idUsuarioVisualizacao;
    }

    /**
     * @param int $idUsuarioAtribuicao
     */
    public function setIdUsuarioAtribuicao($idUsuarioAtribuicao)
    {
        $this->idUsuarioAtribuicao = $idUsuarioAtribuicao;
    }

    /**
     * @return int
     */
    public function getIdUsuarioAtribuicao()
    {
        return $this->idUsuarioAtribuicao;
    }

    /**
     * @param int $retornoProgamado
     */
    public function setRetornoProgamado($retornoProgamado)
    {
        $this->retornoProgamado = $retornoProgamado;
    }

    /**
     * @return int
     */
    public function getRetornoProgamado()
    {
        return $this->retornoProgamado;
    }
}
