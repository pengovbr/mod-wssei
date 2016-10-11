<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Anexo
 *
 * @ORM\Table(name="assinante")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\AssinanteRepository")
 */
class Assinante extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_assinante", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idAssinante;

    /**
     * @var string
     *
     * @ORM\Column(name="cargo_funcao", type="string", length=100)
     */
    private $cargoFuncao;

    /**
     * @param string $cargoFuncao
     */
    public function setCargoFuncao($cargoFuncao)
    {
        $this->cargoFuncao = $cargoFuncao;
    }

    /**
     * @return string
     */
    public function getCargoFuncao()
    {
        return $this->cargoFuncao;
    }

    /**
     * @param int $idAssinante
     */
    public function setIdAssinante($idAssinante)
    {
        $this->idAssinante = $idAssinante;
    }

    /**
     * @return int
     */
    public function getIdAssinante()
    {
        return $this->idAssinante;
    }
}
