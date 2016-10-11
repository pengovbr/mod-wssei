<?php

namespace Application\Service;

use Application\Service\Exception\BaseException;
use Base\Service\AbstractService;

class FuncaoService extends AbstractService
{
    const CXT_SAVE_SUCESSO = 1;
    const CXT_SAVE_FALHA   = 2;

    public function getFuncaoRepository()
    {
        return $this->getEntityManager()->getRepository('Application\Entity\FuncaoEntity');
    }

    public function delete($id)
    {
        $this->getFuncaoRepository()->delete($id);
    }

    public function listar($page = 0)
    {
        if ($page > 0) {
            return $this->getFuncaoRepository()->findWithPaginator($page);
        } else {
            return $this->getFuncaoRepository()->findAll();
        }
    }

    /**
     * Função exemplo de save com Auditoria
     *
     * @context Application\Service\FuncaoService::CXT_SAVE_SUCESSO
     * @logType Auditoria\Service\Utility::TP_LOG_INSERT
     * @param array $data Dados de Funcao para salvar
     * @param integer $id Id da entidade de Funcao
     */
    public function save($data, $id = null)
    {
        $utilityService = $this->getServiceManager()->get('utilityService');
        $input = $utilityService->paramsCompose($this, __FUNCTION__, func_get_args());

        if (null !== $id) {
            $input['logType'] = \Auditoria\Service\Utility::TP_LOG_UPDATE;
        }

        try {
            $this->getEntityManager()->beginTransaction();
            $result = $this->getFuncaoRepository()->save($data, $id);
            $this->getLogger()->info('success message', $input);
            $this->getEntityManager()->commit();

            return $result;
        } catch (BaseException $e) {
            $this->getEntityManager()->rollback();

            $input['context'] = self::CXT_SAVE_FALHA;
            $this->getEntityManager()->beginTransaction();
            $this->getLogger()->crit('fail message', $input);
            $this->getEntityManager()->commit();
        }
    }
}
