<?php

class PaginaProcessosTramitadosExternamente extends PaginaTeste
{
    public function __construct($test)
    {
        parent::__construct($test);
    }

    public function contemProcesso($numeroProcesso) 
    {
        return strpos($this->test->byCssSelector('body')->text(), $numeroProcesso) !== false;
    }
}