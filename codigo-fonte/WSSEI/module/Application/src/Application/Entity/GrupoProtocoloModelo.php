<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Anexo
 *
 * @ORM\Table(name="grupo_protocolo_modelo")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\GrupoProtocoloModeloRepository")
 */
class GrupoProtocoloModelo extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_grupo_protocolo_modelo", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idGrupoProtocoloModelo;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=50)
     */
    private $descricao;

    /**
     * @var \Application\Entity\Unidade
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Unidade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_unidade", referencedColumnName="id_unidade")
     */
    private $idUnidade;

    /**
     * @param string $descricao
     */
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    /**
     * @return string
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * @param int $idGrupoProtocoloModelo
     */
    public function setIdGrupoProtocoloModelo($idGrupoProtocoloModelo)
    {
        $this->idGrupoProtocoloModelo = $idGrupoProtocoloModelo;
    }

    /**
     * @return int
     */
    public function getIdGrupoProtocoloModelo()
    {
        return $this->idGrupoProtocoloModelo;
    }

    /**
     * @param \Application\Entity\Unidade $idUnidade
     */
    public function setIdUnidade($idUnidade)
    {
        $this->idUnidade = $idUnidade;
    }

    /**
     * @return \Application\Entity\Unidade
     */
    public function getIdUnidade()
    {
        return $this->idUnidade;
    }
}
