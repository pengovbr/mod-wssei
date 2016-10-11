<?php

namespace Application\Service;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;

class Usuario extends AbstractService
{
    private $urlSoap;

    private $urlSoapSei;

    private $sistema;

    public function setUrlSoapSEI($url) {
        $this->urlSoapSei = $url;
    }

    public function getSistema()
    {
        return $this->sistema;
    }

    public function getPrivateKey()
    {
        return base64_decode('jrINhDBu7mYp7h0DdAJGrOx5uh6ZnfGlxMAl137Le4M=');
    }

    public function getVector()
    {
        return 'MEC.S.E.I.MOBILE';
    }

    public function getUsuarios($post)
    {
        $config = $this->getConfig();

        $urlSoap = $config['soap']['wsdl_sip'];

        $idUnidade = (isset($post['unidade']))? $post['unidade'] : null;

        $client = new \SoapClient($urlSoap);

        try {
            $return = $client->__call('carregarUsuarios',
                array(
                    'IdSistema' => $config['id_sistema'],
                    'IdUnidade' => "{$idUnidade}",
                    'Recurso' => false,
                    'Perfil' => false));

            return $return;

        } catch (\SoapFault $ex) {
            throw $ex;
        }
    }

    public function decryptPassword($senha)
    {
        //$senha = base64_decode('5V0qC+i+FP5bkbWqv9PLgQ==');
        /*$senha = base64_decode($senha);
        $senha =
            mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->getPrivateKey(), $senha, MCRYPT_MODE_CBC, $this->getVector());

        $dec_s = strlen($senha);
        $padding = ord($senha[$dec_s-1]);
        $senha = substr($senha, 0, -$padding);*/

        for ($i = 0; $i < strlen($senha); $i++) {
            $senha[$i] = ~$senha[$i];
        }

        return base64_encode($senha);
    }

    public function autenticar($usuario, $senha)
    {
        $config = $this->getConfig();

        $urlSoap = $config['soap']['wsdl_sip'];
        $senha = $this->decryptPassword($senha);

        $soap  = new \SoapClient($urlSoap);

        try {
            $return = $soap->autenticar(0, null, $usuario, $senha);

            if (!$return) {
                return false; // lancar exception 401
            }

            $usuario = $this->getRepository()->buscarSessaoAuth($usuario);

            if (!$usuario) {
                return false;
            }

            $returnUnids = $soap->carregarUnidades($config['id_sistema'], $usuario->getId());
            $unidades = array();
            $todasUnidades = false;

            foreach ($returnUnids as $unidade) {
                if (trim($unidade[2]) == '*') {
                    $todasUnidades = true;
                    continue;
                }

                $unidades[] = array(
                    'id' => $unidade[0],
                    'unidade' => $unidade[2]
                );
            }

            if ($todasUnidades) {
                $unidades = array();
                $result =  $this->getUnidadeRepository()->pesquisarUnidade(null,1000000, 0);
                foreach($result['result'] as $unidade) {
                    if (trim($unidade->getSigla()) != '*') {
                        $unidades[] =  array(
                            'id' => $unidade->getIdUnidade(),
                            'unidade' =>  $unidade->getSigla()
                        );
                    }
                }
            }

            return array(
                'idUsuario' => $usuario->getId(),
                'nome' => $usuario->getNome(),
                'login' => $usuario->getSigla(),
                'ultimaUnidade' => $this->listarUltimaUnidadeUsuario($usuario->getId()),
                'unidades' => $unidades
            );
        } catch (\SoapFault $ex) {
            throw $ex;
        }
    }

    public function alterarUltimaUnidadeUsuario($usuario, $unidade)
    {
        $repository = $this->getInfraDadoRepository();
        $result = $repository->findOneBy(array(
            'idUsuario' => $usuario,
            'nome' => 'INFRA_UNIDADE_ATUAL'
        ));

        if(!$result) return;

        $result->setValor($unidade);

        $this->getDefaultEntityManager()->persist($result);
        $this->getDefaultEntityManager()->flush();
    }

    public function listarUltimaUnidadeUsuario($usuario)
    {
        $repository = $this->getInfraDadoRepository();
        $result = $repository->findOneBy(array(
            'idUsuario' => $usuario,
            'nome' => 'INFRA_UNIDADE_ATUAL'
        ));

        if(!$result) return;

        /** @var Unidade $res */
        $res = $this->getUnidadeRepository()->find((int) $result->getValor());

        return array(
            'idUnidade' => $res->getIdUnidade(),
            'sigla' => $res->getSigla()
        );
    }


    public function getRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Usuario');
    }

    public function getUnidadeRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\Unidade');
    }

    public function getInfraDadoRepository()
    {
        return $this->getDefaultEntityManager()->getRepository('Application\Entity\InfraDadoUsuario');
    }
}