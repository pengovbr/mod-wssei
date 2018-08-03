<?php
session_start();

/**
 * Controlador (API v1) de servicos REST usando o framework Slim
 */

require_once dirname(__FILE__) . '/../../SEI.php';
require_once dirname(__FILE__) . '/vendor/autoload.php';


//// parada da validação do token e login

session_start();


//$_REQUEST['documento_id'] = '105';
//$_REQUEST['token'] = 'YTE5YzgzNDM0OWFhMDNlNzAxMWY3OWNkN2UwZTYwZDdlZDAxNzFkM2FUVnhUV2sxYnoxOGZHazFjVTFwTlc4OWZId3dmSHc9';

if(empty($_REQUEST['id_documento']))
    throw new InfraException('Deve ser passado valor para o (id_documento).');

if(empty($_REQUEST['token']))
    throw new InfraException('Deve ser passado token no header.');


$token = $_REQUEST['token'];

if(!$token)
    return new InfraException('Acesso negado!');

$rn = new MdWsSeiUsuarioRN();

$result = $rn->autenticarToken($token);

if(!$result['sucesso']){
    new InfraException('Token inválido!');
}

$tokenData = $rn->tokenDecode($token);

$rn = new MdWsSeiUsuarioRN();
$usuarioDTO = new UsuarioDTO();
$contextoDTO = new ContextoDTO();
$usuarioDTO->setStrSigla($tokenData[0]);
$usuarioDTO->setStrSenha($tokenData[1]);
$contextoDTO->setNumIdContexto(null);
$orgaoDTO = new OrgaoDTO();
$orgaoDTO->setNumIdOrgao(null);
$return = $rn->apiAutenticar($usuarioDTO, $contextoDTO, $orgaoDTO);

// Recupera o id do procedimento
$protocoloDTO = new DocumentoDTO();
$protocoloDTO->setDblIdDocumento($_REQUEST['id_documento']);
$protocoloDTO->retDblIdProcedimento();
$protocoloRN = new DocumentoRN();
$protocoloDTO = $protocoloRN->consultarRN0005($protocoloDTO);

if(empty($protocoloDTO))
    return new InfraException('Documento não encontrado');


$linkassinado = SessaoSEI::getInstance()->assinarLink('/sei/controlador.php?acao=editor_montar&acao_origem=arvore_visualizar&id_procedimento=' . $protocoloDTO->getDblIdProcedimento() . '&id_documento=' . $_REQUEST['id_documento']);

 header('Location: ' . $linkassinado);

