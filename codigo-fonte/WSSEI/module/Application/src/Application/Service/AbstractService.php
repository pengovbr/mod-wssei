<?php

namespace Application\Service;

use Application\Service\Exception\BaseException;
use Application\Service\Exception\DataNotFoundException;
use Application\Service\Exception\ServiceException;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Debug\Debug;
use Zend\Mime\Message;
use Zend\Mime\Part;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator;
use Zend\Validator\EmailAddress;

abstract class AbstractService
{

    const ERROR_MSG = 'E';
    const ALERT_MSG = 'A';

    protected $translator;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected $entity;

    protected $identity;

    protected $saveData = array();

    protected $saveObject = array();

    protected $errorMessages = array();

    protected $alertMessages = array();

    protected $config = array();

    public static $srvManager;

    const STATUS_UPDATE = 'Update';

    const STATUS_INSERT = 'Insert';

    /**
     * Traduz uma mensagem segundo arquivo de tradução PT_BR.php
     *
     * @param $message
     * @param null $params
     * @return string
     */
    protected function translate($message, $params = null)
    {
        if (!$this->translator) {
            $this->translator = $this->getServiceManager()->get('translator');
        }
        if (is_array($params)) {
            return vsprintf($this->translator->translate($message), $params);
        }
        return sprintf($this->translator->translate($message), $params);
    }

    /**
     * Seta as FKS das entidade relacionadas
     *
     * @param EntityManager $_em
     * @param $entity
     * @param array $arrSetterFk
     */
    protected function setFk($_em, $entity, array $arrSetterFk = array())
    {
        foreach ($arrSetterFk as $arrFk) {
            $method = $arrFk['method'];
            $setFk = $_em->getReference($arrFk['entity'], $arrFk['value']);
            $entity->$method($setFk);
        }
    }

    /**
     * Insere dados na entidade informada
     *
     * @param array $data
     * @param EntityManager $_em
     * @throws DataNotFoundException
     * @return $entity
     */
    public function insert(array $data, $_em = null)
    {
        $_em = is_null($_em)?$this->getDefaultEntityManager():$_em;
        if (!$data) {
            throw new DataNotFoundException('Não foi possível encontrar dados para inserir');
        }
        $this->saveData = $data;

        // insert
        $entity = new $this->entity($this->saveData);
        $this->setFk($entity, $this->saveObject);

        $_em->persist($entity);
        $_em->flush();
        $_em->clear();

        return $entity;
    }

    /**
     * Atualiza Dados da entidade
     *
     * @param array $data
     * @param $idEntity
     * @param EntityManager $_em
     * @return object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function update(array $data, $idEntity, $_em = null)
    {
        $_em = is_null($_em)?$this->getDefaultEntityManager():$_em;
        if (!$data || !$idEntity) {
            throw new BaseException('Não foi possível encontrar dados para atualizar');
        }
        $this->saveData = $data;
        // update
        $entity = $_em->getReference($this->entity, $idEntity);
        $hydrator = new Hydrator\ClassMethods();
        $hydrator->hydrate($this->saveData, $entity);
        $this->setFk($_em, $entity, $this->saveObject);

        $_em->persist($entity);
        $_em->flush();
        $_em->clear();

        return $entity;
    }

    /**
     * Delete um linha da entidade
     *
     * @param $idEntity
     * @param EntityManager $_em
     * @return null
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete($idEntity, $_em)
    {
        $entity = $_em->getReference($this->entity, $idEntity);
        if ($entity) {
            $_em->remove($entity);
            $_em->flush();
            $_em->clear();
            return $idEntity;
        }
        return null;
    }

    /**
     * Retorna todos os dados da entidade
     *
     * @return array
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Retorna dado(s) da entidade segundo critérios informados
     *
     * @param array $arrCriteria
     * @return array|null
     */
    public function findBy(array $arrCriteria = array())
    {
        $result = $this->getRepository()->findBy($arrCriteria);
        return ($result) ? $result : null;
    }

    /**
     * Retorna uma linha da entidade segundo critérios informados
     *
     * @param array $arrCriteria
     * @return null|object
     */
    public function findOneBy(array $arrCriteria = array())
    {
        return $this->getRepository()->findOneBy($arrCriteria);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getDefaultRepository()
    {
        return $this->getDefaultEntityManager()->getRepository($this->entity);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getSqlSvrRepository()
    {
        return $this->getSqlSvrEntityManager()->getRepository($this->entity);
    }

    /**
     * @param string $emServiceName
     * @return EntityManager
     */
    private function getEntityManager($emServiceName)
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->getServiceManager()->get($emServiceName);
        }

        return $this->entityManager;
    }

    /**
     * @return EntityManager
     */
    public function getDefaultEntityManager()
    {
        return $this->getEntityManager('doctrine.entitymanager.orm_default');
    }

    /**
     *  @return EntityManager
     */
    public function getSqlSvrEntityManager()
    {
        return  $this->getEntityManager('doctrine.entitymanager.orm_sip');
    }


    /**
     * Set ServiceManager
     * @param ServiceManager $srvManager
     */
    public static function setServiceManager($srvManager)
    {
        self::$srvManager = $srvManager;
    }

    /**
     * Get ServiceManager
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        return self::$srvManager;
    }

    /**
     * @param $srvAlias
     * @return array|object
     */
    public function getSm($srvAlias)
    {
        return self::getServiceManager()->get($srvAlias);
    }

    /**
     *
     * @param $dql
     * @param EntityManager $_em
     * @param null $mensagemException
     * @return array
     * @throws Exception\ServiceException
     */
    public function setDql($dql, $mensagemException = null, $_em=null)
    {
        if (!$_em) {
            $_em = $this->getDefaultEntityManager();
        }
        $query = $_em->createQuery($dql);
        $resultSet = $query->getResult();
        if ($mensagemException && !$resultSet) {
            throw new ServiceException("$mensagemException");
        }
        return $resultSet;
    }

    /**
     * Get a Entidade corrente
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Seta uma nova entidade
     *
     * @param String $entity
     * @return \Application\Service\AbstractService
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Envia e-mail segundo parâmetros
     *
     * @param String $subject - Título do Email
     * @param String $body - Corpo do Email
     * @param String $addTo - Destinatário
     * @param string $Cc - Com cópia para
     * @param $type - Tipo da instância do Service MailMessage (erro ou aluno) -  Default: aluno
     */
    public static function enviarEmail($subject, $body, $addTo, $Cc = null, $type = 'aluno')
    {
        $locator = self::getServiceManager();

        switch ($type) {
            case 'aluno':
                /** @var $mailMessage */
                $mailMessage = $locator->get('MailMessageAluno');
                break;;
            case 'erro':
                $mailMessage = $locator->get('MailMessageErro');
                break;;
            default:
                throw new ServiceException('O parâmetro $type deve ser dos tipos "erro" ou "aluno"');
                break;;
        }

        $mailTransport = $locator->get('MailTransport');

        $validator = new EmailAddress();

        if (!$validator->isValid($addTo)) {
            throw new ServiceException("Erro ao enviar e-mail. Verifique se o
             destinatário informado é um endereço de e-mail válido");
        }

        $bodyPart = new Message();

        $bodyMessage = new Part($body);
        $bodyMessage->type = 'text/html';

        $bodyPart->setParts(array($bodyMessage));

        $mailMessage->setSubject($subject)
            ->addTo($addTo)
            ->setBody($bodyPart);

        if (!is_null($Cc)) {
            if ($validator->isValid($Cc)) {
                $mailMessage->addCc($Cc);
            }
        }

        try {
            $mailTransport->send($mailMessage);
        } catch (BaseException $e) {
            echo "Ocorreu um erro ao tentar enviar o email";
        }
    }

    /**
     * @param $msg
     * @param $param
     */
    protected function addErrorMensages($msg , $param=null)
    {
        $this->errorMessages[] = $this->addMsg($msg, $param, self::ERROR_MSG);
    }

    /**
     * @param $msg
     * @param $param
     */
    protected function addAlertMensages($msg , $param=null)
    {
        $this->alertMessages[] = $this->addMsg($msg, $param, self::ALERT_MSG);
    }

    /**
     * @param $msg
     * @param $param
     * @param $tpMsg
     * @return array
     */
    private function addMsg($msg , $param, $tpMsg)
    {
        return array(
            'coMensagem' => $msg,
            'tpMensagem' => $tpMsg,
            'dsMessage' => $this->translate($msg, $param),
        );
    }

    protected function lancaListaDeErros()
    {
        if (count($this->errorMessages)) {
            throw new ServiceException($this->errorMessages);
        }
    }

    protected function lancaListaDeAlertas()
    {
        if (count($this->errorMessages)) {
            throw new ServiceException($this->alertMessages);
        }
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
