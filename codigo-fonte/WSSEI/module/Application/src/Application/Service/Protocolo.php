<?php

namespace Application\Service;


use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use TheSeer\DirectoryScanner\Exception;

class Protocolo extends AbstractService {

    public function pesquisarProtocoloPorID($id) {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Protocolo')->find((int) $id);
    }
} 