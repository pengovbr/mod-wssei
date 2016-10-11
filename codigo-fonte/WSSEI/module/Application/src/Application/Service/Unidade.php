<?php

namespace Application\Service;


use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;

class Unidade extends AbstractService {

    public function listarUnidades($filter, $limit, $offset) {
        $arrayResult =  array();
        $repository = $this->getRepository();
        $result = $repository->pesquisarUnidade($filter, $limit, $offset);

        if (count($result['result'])) {
            /** @var \Application\Entity\Unidade $value */
            foreach($result['result'] as $value) {
                if (trim($value->getSigla()) != '*') {
                    $arrayResult[] =  array(
                        'id' => $value->getIdUnidade(),
                        'sigla' =>  $value->getSigla(),
                        'descricao' => $value->getDescricao()
                    );
                }
            }
        }

        return array('data' => $arrayResult, 'total' => $result['count']);
    }

    public function pesquisarUnidadePorID($id) {
        return $this->getRepository()->find($id);
    }

    public function getRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Unidade');
    }
}