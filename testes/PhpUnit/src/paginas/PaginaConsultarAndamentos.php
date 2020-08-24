<?php

class PaginaConsultarAndamentos extends PaginaTeste
{
    public function __construct($test)
    {
        parent::__construct($test);
    }

    public function contemTramite($mensagemTramite) 
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
        //$this->test->assertContains($mensagemTramite, $this->test->byCssSelector('body')->text());
        return strpos($this->test->byCssSelector('body')->text(), $mensagemTramite) !== false;
    }

    public function contemTramiteProcessoEmTramitacao($strUnidadeDestino) 
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
        $mensagemTramite = "Processo em tramitação externa para $strUnidadeDestino";
        return strpos($this->test->byCssSelector('body')->text(), $mensagemTramite) !== false;
    }

    public function contemTramiteProcessoRecebido($strUnidadeDestino) 
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
        $mensagemTramite = "Recebido em $strUnidadeDestino";
        return strpos($this->test->byCssSelector('body')->text(), $mensagemTramite) !== false;
    }

    public function contemTramiteProcessoRejeitado($strUnidadeDestino, $strMotivo) 
    {
        $this->test->frame(null);
        $this->test->frame("ifrVisualizacao");
        $mensagemTramite = "O processo foi recusado pelo orgão $strUnidadeDestino pelo seguinte motivo: $strMotivo";
        return strpos($this->test->byCssSelector('body')->text(), $mensagemTramite) !== false;
    }    
}
