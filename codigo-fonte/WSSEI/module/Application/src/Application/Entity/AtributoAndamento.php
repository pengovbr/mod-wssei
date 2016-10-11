<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Anexo
 *
 * @ORM\Table(name="atributo_andamento")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\AtributoAndamentoRepository")
 */
class AtributoAndamento extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_atributo_andamento", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idAtributoAndamento;

    /**
     * @var \Application\Entity\Atividade
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Atividade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_atividade", referencedColumnName="id_atividade")
     */
    private $idAtividade;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=50)
     */
    private $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="string", length=50)
     */
    private $valor;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_origem", type="integer")
     */
    private $idOrigem;

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
     * @param int $idAtributoAndamento
     */
    public function setIdAtributoAndamento($idAtributoAndamento)
    {
        $this->idAtributoAndamento = $idAtributoAndamento;
    }

    /**
     * @return int
     */
    public function getIdAtributoAndamento()
    {
        return $this->idAtributoAndamento;
    }

    /**
     * @param int $idOrigem
     */
    public function setIdOrigem($idOrigem)
    {
        $this->idOrigem = $idOrigem;
    }

    /**
     * @return int
     */
    public function getIdOrigem()
    {
        return $this->idOrigem;
    }

    /**
     * @param string $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param string $valor
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
    }

    /**
     * @return string
     */
    public function getValor()
    {
        return $this->valor;
    }
}
