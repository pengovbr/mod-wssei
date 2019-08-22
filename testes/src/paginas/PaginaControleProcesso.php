<?php

class PaginaControleProcesso extends PaginaTeste
{
	public function __construct($test)
    {
        parent::__construct($test);
    }

    protected function obterLinhasProcessos($processosGerados, $processosRecebidos)
    {
        $paineisPesquisa = array();     
        if($processosGerados) $paineisPesquisa[] = 'tblProcessosGerados';
        if($processosRecebidos) $paineisPesquisa[] = 'tblProcessosRecebidos';

        $resultado = array();
        foreach ($paineisPesquisa as $painel) {            
            $resultado = array_merge($resultado, $this->test->byId($painel)->elements($this->test->using('css selector')->value('tr')));
        }

        return $resultado;
    }

    //TODO: Implementar a navegação por páginas (parâmetro $pagina)
    protected function listarProcessos($processosGerados, $processosRecebidos) 
    {   
        $listaProtocolos = array();
        $processosRows = $this->obterLinhasProcessos($processosGerados, $processosRecebidos);
        if(isset($processosRows) && count($processosRows) > 0){
            for ($i=1; $i < count($processosRows); $i++) {
                $listaProtocolos[] = trim($processosRows[$i]->text());
            }
        }

        return $listaProtocolos;
    }

    public function processosGerados()
    {
        return $this->listarProcessos(true, false);
    }

    public function processosRecebidos()
    {
        return $this->listarProcessos(false, true);
    }

    public function contemProcesso($numeroProcesso, $processosGerados = true, $processosRecebidos = true)
    {
        $listaProcessos = $this->listarProcessos($processosGerados, $processosRecebidos);
        return ($listaProcessos != null) ? in_array($numeroProcesso, $listaProcessos) : false;
    }

    public function contemAlertaProcessoRecusado($numeroProcesso)
    {
        $processosRows = $this->obterLinhasProcessos(true, true);
        foreach ($processosRows as $row) {                        
            try{
                if(strpos($row->text(), $numeroProcesso) !== false){
                    foreach ($row->elements($this->test->using('css selector')->value('img')) as $icone) {
                        if(strpos($icone->attribute("src"), 'pen_tramite_recusado.png') !== false)
                            return true;
                    }
                }                
            }
            catch(Exception $e) {                
                return false;
            }
        }

        return false;
    }

    public function abrirProcesso($strProtocolo)
    {
		$this->test->byLinkText($strProtocolo)->click();
    }
}