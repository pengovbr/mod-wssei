<?php
/**
 * Created by IntelliJ IDEA.
 * User: marioeugenio
 * Date: 12/3/15
 * Time: 2:29 PM
 */

namespace Application\Service;


use Doctrine\ORM\EntityManager;

class GrupoAcompanhamento extends AbstractService {


    public function listarGrupoAcompanhamento($unidade, $limit, $offset) {
        $arrayResult =  array();
        $repository = $this->getRepository();

        $result = $repository->listarGrupoAcompanhamento($unidade, $limit, $offset);

        if (count($result['result'])) {
            /** @var \Application\Entity\GrupoAcompanhamento $value */
            foreach($result['result'] as $value) {
                $arrayResult[] =  array(
                    'id' =>  $value->getIdGrupoAcompanhamento(),
                    'nome' =>  $value->getNome()
                );
            }
        }

        return  array('data' => $arrayResult, 'total' => $result['count']);
    }

    public function pesquisarGrupoAcompanhamentoPorID($id) {
        return $this->getRepository()->find($id);
    }

    public function getRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\GrupoAcompanhamento');
    }
} 