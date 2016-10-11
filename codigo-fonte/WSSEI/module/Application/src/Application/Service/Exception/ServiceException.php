<?php
namespace Application\Service\Exception;

class ServiceException extends \RuntimeException
{
    private $execptionParams;

    public function __construct($message, $code=406, $execptionParams=null, $previous=null)
    {
        $code =  is_null($code) ? 406 : $code;
        $this->execptionParams = $execptionParams;
        if (is_array($message)) {
            $message = json_encode($message, true);
        }
        parent::__construct($message, $code, $previous);
    }

    public function getExecptionParams()
    {
        return $this->execptionParams;
    }
}
