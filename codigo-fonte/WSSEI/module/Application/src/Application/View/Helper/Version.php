<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Helper para apresentar a versão do deploy para a aplicação
 */
class Version extends AbstractHelper
{
    public function __invoke()
    {
        return $this;
    }

    public function __toString()
    {
        $urlVersion = APPLICATION_PATH . "/version.txt";
        $version = 'dev';
        if (file_exists($urlVersion)) {
            $version = file_get_contents($urlVersion);
        }

        return $version;
    }
}
