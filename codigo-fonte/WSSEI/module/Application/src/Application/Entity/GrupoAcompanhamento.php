<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * GrupoAcompanhamento
 *
 * @ORM\Table(name="grupo_acompanhamento")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\GrupoAcompanhamentoRepository")
 */
class GrupoAcompanhamento extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_grupo_acompanhamento", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idGrupoAcompanhamento;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=50)
     */
    private $nome;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_unidade", type="integer")
     */
    private $idUnidade;


    /**
     * Set idGrupoAcompanhamento
     *
     * @param integer $idGrupoAcompanhamento
     * @return GrupoAcompanhamento
     */
    public function setIdGrupoAcompanhamento($idGrupoAcompanhamento)
    {
        $this->idGrupoAcompanhamento = $idGrupoAcompanhamento;

        return $this;
    }

    /**
     * Get idGrupoAcompanhamento
     *
     * @return integer 
     */
    public function getIdGrupoAcompanhamento()
    {
        return $this->idGrupoAcompanhamento;
    }

    /**
     * Set nome
     *
     * @param string $nome
     * @return GrupoAcompanhamento
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
     * Set idUnidade
     *
     * @param integer $idUnidade
     * @return GrupoAcompanhamento
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
}
