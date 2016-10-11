<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;


/**
 * CargoFuncao
 *
 * @ORM\Table(name="cargo_funcao")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\CargoFuncaoRepository")
 */
class CargoFuncao extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_unidade", type="integer")
     */
    private $idUnidade;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_cargo_funcao", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idCargoFuncao;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=100)
     */
    private $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="sin_ativo", type="string", length=1)
     */
    private $sinAtivo;


    /**
     * Set idUnidade
     *
     * @param string $idUnidade
     * @return CargoFuncao
     */
    public function setIdUnidade($idUnidade)
    {
        $this->idUnidade = $idUnidade;

        return $this;
    }

    /**
     * Get idUnidade
     *
     * @return string 
     */
    public function getIdUnidade()
    {
        return $this->idUnidade;
    }

    /**
     * Set idCargoFuncao
     *
     * @param integer $idCargoFuncao
     * @return CargoFuncao
     */
    public function setIdCargoFuncao($idCargoFuncao)
    {
        $this->idCargoFuncao = $idCargoFuncao;

        return $this;
    }

    /**
     * Get idCargoFuncao
     *
     * @return integer 
     */
    public function getIdCargoFuncao()
    {
        return $this->idCargoFuncao;
    }

    /**
     * Set nome
     *
     * @param string $nome
     * @return CargoFuncao
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
     * Set sinAtivo
     *
     * @param string $sinAtivo
     * @return CargoFuncao
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
}
