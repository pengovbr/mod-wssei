<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * InfraDadoUsuario
 *
 * @ORM\Table(name="infra_dado_usuario")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\InfraDadoUsuarioRepository")
 */
class InfraDadoUsuario extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUsuario;


    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=50)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="string", length=4000)
     */
    private $valor;

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
