<?php

namespace Application\Form;

use Zend\InputFilter;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Form\Element\Submit;

class UploadForm extends Form {

    public function __construct($name = null, $options = array())
    {
        parent::__construct ( $name, $options );
        $this->setName($name);
        $this->addElements ();
        # $this->addInputFilter ();
    }

    public function addElements()
    {
        // File Input
        $file = new Element\File ( 'arquivo' );
        $file->setLabel ( 'Selecione o arquivo' )->setAttribute ( 'id', 'arquivo' );
        $this->add ( $file );

        $submit = new Submit ( 'btnSubmit' );
        $submit->setAttribute ( 'value', 'Enviar' );
        $this->add ( $submit );
    }

    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter ();

        $fileInput = new InputFilter\FileInput ( 'arquivo' );
        $fileInput->setRequired ( true );

        $inputFilter->add ( $fileInput );

        $this->setInputFilter ( $inputFilter );
    }
}
?>
