<?php

namespace Application\Service;


use Application\Entity\Assinante;
use Doctrine\ORM\EntityManager;

class CargoFuncao extends AbstractService {

    public function listarCargoFuncao($unidade, $limit, $offset) {
        $arrayResult =  array();
        $repository = $this->getDefaultEntityManager()->getRepository('Application\Entity\Assinante');

        $result = $repository->listarCargoFuncao($unidade, $limit, $offset);

        if (count($result['result'])) {
            /** @var Assinante $value */
            foreach($result['result'] as $value) {
                $arrayResult[] =  array(
                    'id' =>  $value['idAssinante'],
                    'nome' =>  $value['cargoFuncao']
                );
            }
        }

        return  array('data' => $arrayResult);
    }
} 