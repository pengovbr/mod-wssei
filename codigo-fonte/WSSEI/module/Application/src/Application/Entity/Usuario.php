<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Protocolo
 *
 * @ORM\Table(name="usuario")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\UsuarioRepository")
 */
class Usuario extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sigla", type="string")
     */
    private $sigla;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string")
     */
    private $nome;

    /**
     * Get isUsuario
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get nome
     *
     * @return integer
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Get sigla
     *
     * @return integer
     */
    public function getSigla()
    {
        return $this->sigla;
    }
}
