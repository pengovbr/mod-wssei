<?php
/**
 * Created by IntelliJ IDEA.
 * User: marioeugenio
 * Date: 1/29/16
 * Time: 2:51 PM
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Base\Entity\AbstractEntity;

/**
 * RelProtocoloProtocolo
 *
 * @ORM\Table(name="rel_bloco_protocolo")
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\RelBlocoProtocoloRepository")
 */
class RelBlocoProtocolo extends AbstractEntity
{
    /**
     * @var \Application\Entity\Protocolo
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Protocolo", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_protocolo", referencedColumnName="id_protocolo")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idProtocolo;

    /**
     * @var \Application\Entity\Bloco
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Bloco", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_bloco", referencedColumnName="id_bloco")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idBloco;

    /**
     * @var string
     *
     * @ORM\Column(name="anotacao", type="text")
     */
    private $observacao;

    /**
     * @var integer
     *
     * @ORM\Column(name="sequencia", type="integer")
     */
    private $sequencia;

    /**
     * @param \Application\Entity\Bloco $idBloco
     */
    public function setIdBloco($idBloco)
    {
        $this->idBloco = $idBloco;
    }

    /**
     * @return \Application\Entity\Bloco
     */
    public function getIdBloco()
    {
        return $this->idBloco;
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
     * @param string $observacao
     */
    public function setObservacao($observacao)
    {
        $this->observacao = $observacao;
    }

    /**
     * @return string
     */
    public function getObservacao()
    {
        return $this->observacao;
    }

    /**
     * @param int $sequencia
     */
    public function setSequencia($sequencia)
    {
        $this->sequencia = $sequencia;
    }

    /**
     * @return int
     */
    public function getSequencia()
    {
        return $this->sequencia;
    }
} 