<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Protocolo
 *
 * @ORM\Table(name="login")
 * @ORM\Entity()
 */
class Login extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_login", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_contexto", type="integer")
     */
    private $idContexto;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Usuario", cascade={"persist"}, fetch="LAZY", inversedBy="usuario")
     * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
     */
    private $usuario;

    /**
     * @var datetime
     *
     * @ORM\Column(name="dth_login", type="datetime")
     */
    private $date;

    /**
     * @var datetime
     *
     * @ORM\Column(name="id_sistema", type="integer")
     */
    private $idSistema;

    /**
     * @var datetime
     *
     * @ORM\Column(name="hash_agente", type="string")
     */
    private $agente;

    /**
     * Get idLogin
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get idContexto
     *
     * @return integer
     */
    public function getIdContexto()
    {
        return $this->idContexto;
    }

    /**
     * Get idSistema
     *
     * @return integer
     */
    public function getSistema()
    {
        return $this->idSistema;
    }

    /**
     * Get idUsuario
     *
     * @return integer
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Get date
     *
     * @return integer
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get agente
     *
     * @return string
     */
    public function getAgente()
    {
        return $this->agente;
    }
}
