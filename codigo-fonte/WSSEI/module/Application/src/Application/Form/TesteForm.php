<?php

namespace Application\Form;

use Zend\Form\Form;
use Zend\Form\Element\File;
use Zend\Form\Element\Submit;

class TesteForm extends Form
{
    public function __construct()
    {
        parent::__construct('testeForm');

        $this->setAttributes(array(
            'enctype' => 'multipart/form-data'
        ));

        $file = new File('teste');
        $file->setLabel('Arquivo:');
        $this->add($file);

        $submit = new Submit('submit');
        $submit->setValue('Salvar');
        $this->add($submit);

        $submit = new Submit('cancelar');
        $submit->setAttributes(array(
            'type' => 'button',
            'value' => 'Cancelar',
            'onClick' => "document.location.href='index'"
        ));
        $this->add($submit);
    }
}
