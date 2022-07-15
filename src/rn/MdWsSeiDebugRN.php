<?
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiDebugRN extends InfraBD {

    public function __construct(InfraIBanco $objInfraIBanco){
        parent::__construct($objInfraIBanco);
    }

    public function debug(InfraDTO $dto){
        $sql = $this->consultar($dto, true);
        $rs = $this->getObjInfraIBanco()->consultarSql($sql);
        echo "<pre>";
        var_dump($rs);
    }

    public function debugAvancado($sql){
        $rs = $this->getObjInfraIBanco()->consultarSql($sql);
        echo "<pre>";
        var_dump($rs);
    }
}