<?

class SeiMobileControladorWebServices implements ISeiControladorWebServices{

    public function processar($strServico){

        $strArq = null;

        
        switch ($strServico) {
            case 'mobile':
                $strArq = 'mobile.wsdl';
                break;
        }

        if ($strArq!=null){
           $strArq = dirname(__FILE__).'/ws/'.$strArq;
        }
    
        return $strArq;
    }
}
?>