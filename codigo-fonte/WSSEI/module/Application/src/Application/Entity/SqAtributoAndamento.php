<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Anexo
 *
 * @ORM\Table(name="seq_atributo_andamento")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\SqAtributoAndamentoRepository")
 */
class SqAtributoAndamento extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="campo", type="string", length=1)
     */
    private $campo;

    /**
     * @param string $campo
     */
    public function setCampo($campo)
    {
        $this->campo = $campo;
    }

    /**
     * @return string
     */
    public function getCampo()
    {
        return $this->campo;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
