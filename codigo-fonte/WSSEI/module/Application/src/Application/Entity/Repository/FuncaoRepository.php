<?php

namespace Application\Entity\Repository;

use Base\Entity\Repository\AbstractEntityRepository;
use Application;

class FuncaoRepository extends AbstractEntityRepository
{
    public function findWithPaginator($page)
    {
        $query = "select t from Application\Entity\FuncaoEntity t ORDER BY t.coFuncao ASC";
        return $this->paginator($query, $page);
    }
}
