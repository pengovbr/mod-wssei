<?php

namespace Application\Controller;

use Zend\Http\Headers;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\Console\Console;
use Zend\Validator\File\Size;
use Zend\File\Transfer\Adapter;
use Zend\File\Transfer\Adapter\Http;

abstract class AbstractActionController extends \Zend\Mvc\Controller\AbstractActionController
{
    protected $translator;

    protected function translate($message)
    {
        if (!$this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator->translate($message);
    }

    /**
     * Add a message
     *
     * @param  mixed|array    $message
     * @param  string         $type
     * @return FlashMessenger
     */
    public function addMessage($message, $type = null)
    {
        if (is_array($message)) {
            foreach ($message as $msg) {
                $this->addMessage($msg, $type);
            }
        } else {
            if (!$type) {
                $type = $message[0];
            }
            switch ($type) {
                case 'I':
                    return $this->addInfoMessage($message);
                case 'S':
                    return $this->addSuccessMessage($message);
                case 'M':
                default:
                    return $this->addErrorMessage($message);
            }
        }
    }

    /**
     * Add a message with "error" type
     *
     * @param  string         $message
     * @return FlashMessenger
     */
    public function addErrorMessage($message)
    {
        return $this->flashMessenger()->addErrorMessage($this->translate($message));
    }

    /**
     * Add a message with "success" type
     *
     * @param  string         $message
     * @return FlashMessenger
     */
    public function addSuccessMessage($message)
    {
        return $this->flashMessenger()->addSuccessMessage($this->translate($message));
    }

    /**
     * Add a message with "info" type
     *
     * @param  string         $message
     * @return FlashMessenger
     */
    public function addInfoMessage($message)
    {
        return $this->flashMessenger()->addInfoMessage($this->translate($message));
    }

    /**
     * @todo: verificar necessidade de pos/pre dispatch
     */
    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        $events = $this->getEventManager();
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'preDispatch'), 100);
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'postDispatch'), -100);
    }

    public function preDispatch(MvcEvent $e)
    {
    }

    public function postDispatch(MvcEvent $e)
    {
    }

    /**
     * @todo: resolver escopo do type para a implementação
     *
     * @param unknown $data
     * @param string $message
     * @param string $type
     * @return \Zend\View\Model\JsonModel
     */
    public function sendJson($data, $message = null, $type = 'success')
    {
        $return = $data;
        if (null != $message) {
            $return['message'] = $this->translate($message);
            $return['type']    = $type;
        }

        $response = new Response();

        $response->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', '*');
        $response->getHeaders()->addHeaderLine('Access-Control-Allow-Methods', 'GET, POST');
        $response->getHeaders()->addHeaderLine('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type');
        $response->getHeaders()->addHeaderLine('Cache-Control', 'no-cache');
        $response->getHeaders()->addHeaderLine('Content-Encoding', 'application/json');

        $json = new JsonModel($return);

        return $response->setContent(
            str_replace(array("\r", "\n", "\r\n"), ' ', $json->serialize())
        );
    }

    /**
     *
     * @param string $nomeArquivo Nome do Input [type=file]
     * @param string $destino Caminho de onde o arquivo será salvo.
     * @param array $validacoes Array de Objetos da Class Zend_Validate_File_*
     * @param $renomearArquivo
     */
    public function upload($elementFileForm, $pathToSave, $validations = array(), $renameFile = true)
    {
        $retorno = array(
            'bool' => true,
            'msg' => 'sucesso'
        );

        $request = $this->getRequest();
        if ($request->isPost()) {

            $adapter = new Http();

            # Seta as validações
            if (! empty( $validations)) {

                $File = $this->params()->fromFiles($elementFileForm);

                $adapter->setValidators($validations, $File ['name']);

            }
            # Valida as regras
            if ($adapter->isValid()) {

                # Renomeia o arquivo concatenando (_ + UNIQUE_ID)
                if ($renameFile) {
                    $adapter->addFilter('Rename', array(
                        'randomize' => true,
                        'target' => $pathToSave . '/' . $File ['name']
                    ));
                } else {
                    $adapter->setDestination($pathToSave);
                }

                $adapter->receive($File ['name']);
                $location = $adapter->getFileName($elementFileForm);
                $retorno['pathFileUploaded'] = $location;

            } else { # Retorna os erros de validação encontrados

                $dataError = $adapter->getMessages();
                $retorno ['bool'] = false;
                $retorno ['msg'] = $dataError;

            }
        }

        return $retorno;
    }
}
