<?php

namespace Application\Service;


use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;

class Orgao extends AbstractService {

    public function listarOrgao($limit, $offset) {
        $arrayResult =  array();
        $repository = $this->getDefaultEntityManager()->getRepository('Application\Entity\Orgao');

        $result = $repository->listarOrgao($limit, $offset);

        if (count($result['result'])) {
            /** @var \Application\Entity\Orgao $value */
            foreach($result['result'] as $value) {
                $arrayResult[] =  array(
                    'id' =>  $value->getIdOrgao(),
                    'sigla' =>  $value->getSigla(),
                    'descricao' =>  $value->getDescricao()
                );
            }
        }

        return  array('data' => $arrayResult, 'total' => $result['count']);
    }
} 