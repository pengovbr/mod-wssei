<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * Anexo
 *
 * @ORM\Table(name="protocolo_modelo")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\ProtocoloModeloRepository")
 */
class ProtocoloModelo extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_protocolo_modelo", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idProtocoloModelo;

    /**
     * @var \Application\Entity\GrupoProtocoloModelo
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\GrupoProtocoloModelo", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_grupo_protocolo_modelo", referencedColumnName="id_grupo_protocolo_modelo")
     */
    private $idGrupoProtocoloModelo;

    /**
     * @var \Application\Entity\Unidade
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Unidade", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_unidade", referencedColumnName="id_unidade")
     */
    private $idUnidade;

    /**
     * @var \Application\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Usuario", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
     */
    private $idUsuario;

    /**
     * @var \Application\Entity\Protocolo
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Protocolo", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_protocolo", referencedColumnName="id_protocolo")
     */
    private $idProtocolo;

    /**
     * @var string
     *
     * @ORM\Column(name="descricao", type="string", length=250)
     */
    private $descricao;

    /**
     * @var string
     *
     * @ORM\Column(name="dth_geracao", type="datetime")
     */
    private $dthGeracao;

    /**
     * @param \Application\Entity\Usuario $idUsuario
     */
    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    /**
     * @return \Application\Entity\Usuario
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

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
     * @param string $dthGeracao
     */
    public function setDthGeracao($dthGeracao)
    {
        $this->dthGeracao = $dthGeracao;
    }

    /**
     * @return string
     */
    public function getDthGeracao()
    {
        return $this->dthGeracao;
    }

    /**
     * @param \Application\Entity\GrupoProtocoloModelo $idGrupoProtocoloModelo
     */
    public function setIdGrupoProtocoloModelo($idGrupoProtocoloModelo)
    {
        $this->idGrupoProtocoloModelo = $idGrupoProtocoloModelo;
    }

    /**
     * @return \Application\Entity\GrupoProtocoloModelo
     */
    public function getIdGrupoProtocoloModelo()
    {
        return $this->idGrupoProtocoloModelo;
    }

    /**
     * @param \Application\Entity\Protocolo $idProtocolo
     */
    public function setIdProtocolo($idProtocolo)
    {
        $this->idProtocolo = $idProtocolo;
    }

    /**
     * @return \Application\Entity\Protocolo
     */
    public function getIdProtocolo()
    {
        return $this->idProtocolo;
    }

    /**
     * @param int $idProtocoloModelo
     */
    public function setIdProtocoloModelo($idProtocoloModelo)
    {
        $this->idProtocoloModelo = $idProtocoloModelo;
    }

    /**
     * @return int
     */
    public function getIdProtocoloModelo()
    {
        return $this->idProtocoloModelo;
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
