<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Tarefa
 *
 * @ORM\Table(name="tarefa")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\TarefaRepository")
 */
class Tarefa extends AbstractEntity
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tarefa", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idTarefa;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=250)
     */
    private $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_historico_resumido", type="string", length=1)
     */
    private $sinHistoricoResumido;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_historico_completo", type="string", length=1)
     */
    private $sinHistoricoCompleto;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_fechar_andamentos_abertos", type="string", length=1)
     */
    private $sinFecharAndamentosAbertos;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_lancar_andamento_fechado", type="string", length=1)
     */
    private $sinLancarAndamentoFechado;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_permite_processo_fechado", type="string", length=1)
     */
    private $sinPermiteProcessoFechado;


    /**
     * Set idTarefa
     *
     * @param integer $idTarefa
     * @return Tarefa
     */
    public function setIdTarefa($idTarefa)
    {
        $this->idTarefa = $idTarefa;

        return $this;
    }

    /**
     * Get idTarefa
     *
     * @return integer 
     */
    public function getIdTarefa()
    {
        return $this->idTarefa;
    }

    /**
     * Set nome
     *
     * @param string $nome
     * @return Tarefa
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
     * Set sinHistoricoResumido
     *
     * @param string $sinHistoricoResumido
     * @return Tarefa
     */
    public function setSinHistoricoResumido($sinHistoricoResumido)
    {
        $this->sinHistoricoResumido = $sinHistoricoResumido;

        return $this;
    }

    /**
     * Get sinHistoricoResumido
     *
     * @return string 
     */
    public function getSinHistoricoResumido()
    {
        return $this->sinHistoricoResumido;
    }

    /**
     * Set sinHistoricoCompleto
     *
     * @param string $sinHistoricoCompleto
     * @return Tarefa
     */
    public function setSinHistoricoCompleto($sinHistoricoCompleto)
    {
        $this->sinHistoricoCompleto = $sinHistoricoCompleto;

        return $this;
    }

    /**
     * Get sinHistoricoCompleto
     *
     * @return string 
     */
    public function getSinHistoricoCompleto()
    {
        return $this->sinHistoricoCompleto;
    }

    /**
     * Set sinFecharAndamentosAbertos
     *
     * @param string $sinFecharAndamentosAbertos
     * @return Tarefa
     */
    public function setSinFecharAndamentosAbertos($sinFecharAndamentosAbertos)
    {
        $this->sinFecharAndamentosAbertos = $sinFecharAndamentosAbertos;

        return $this;
    }

    /**
     * Get sinFecharAndamentosAbertos
     *
     * @return string 
     */
    public function getSinFecharAndamentosAbertos()
    {
        return $this->sinFecharAndamentosAbertos;
    }

    /**
     * Set sinLancarAndamentoFechado
     *
     * @param string $sinLancarAndamentoFechado
     * @return Tarefa
     */
    public function setSinLancarAndamentoFechado($sinLancarAndamentoFechado)
    {
        $this->sinLancarAndamentoFechado = $sinLancarAndamentoFechado;

        return $this;
    }

    /**
     * Get sinLancarAndamentoFechado
     *
     * @return string 
     */
    public function getSinLancarAndamentoFechado()
    {
        return $this->sinLancarAndamentoFechado;
    }

    /**
     * Set sinPermiteProcessoFechado
     *
     * @param string $sinPermiteProcessoFechado
     * @return Tarefa
     */
    public function setSinPermiteProcessoFechado($sinPermiteProcessoFechado)
    {
        $this->sinPermiteProcessoFechado = $sinPermiteProcessoFechado;

        return $this;
    }

    /**
     * Get sinPermiteProcessoFechado
     *
     * @return string 
     */
    public function getSinPermiteProcessoFechado()
    {
        return $this->sinPermiteProcessoFechado;
    }
}
