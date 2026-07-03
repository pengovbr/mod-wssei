<?php
try {

  $strCaminhoSipPhp = dirname(__FILE__) . '/../../../../../sip/web/Sip.php';
  if (!file_exists($strCaminhoSipPhp)) {
    $strCaminhoSipPhp = dirname(__FILE__) . '/../../web/Sip.php';
  }
  require_once $strCaminhoSipPhp;

  session_start();
  SessaoSip::getInstance(false);

  function color($text, $colorCode) {
    return "\033[" . $colorCode . "m" . utf8_encode($text) . "\033[0m";
  }

  echo color("============================================\n", "34");
  echo color("   CARGA DE BANCO DE DADOS - SIP\n", "32");
  echo color("============================================\n\n", "34");

  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->setBolEcho(true);
  InfraDebug::getInstance()->limpar();
  InfraDebug::getInstance()->gravar('INÍCIO SIP');

  $objBanco = BancoSip::getInstance();
  $objBanco->setBolScript(true);
  $objBanco->abrirConexao();
  $objBanco->abrirTransacao();

  $objUsuario = new UsuarioBD($objBanco);
  $objUsuarioRN = new UsuarioRN();
  $objUnidade = new UnidadeBD($objBanco);
  $objUnidadeRN = new UnidadeRN();

  $unidades = [
    ['id_unidade' => 110000004, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-34-29', 'descricao' => 'Unidade 2022-06-1014-34-29', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000005, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-34-37', 'descricao' => 'Unidade 2022-06-1014-34-37', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000006, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-34-45', 'descricao' => 'Unidade 2022-06-1014-34-45', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000007, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-34-52', 'descricao' => 'Unidade 2022-06-1014-34-52', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000008, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-34-58', 'descricao' => 'Unidade 2022-06-1014-34-58', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000009, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-35-4', 'descricao' => 'Unidade 2022-06-1014-35-4', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000010, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-35-11', 'descricao' => 'Unidade 2022-06-1014-35-11', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000011, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-35-17', 'descricao' => 'Unidade 2022-06-1014-35-17', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000012, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-35-25', 'descricao' => 'Unidade 2022-06-1014-35-25', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000013, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-35-31', 'descricao' => 'Unidade 2022-06-1014-35-31', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000014, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-35-37', 'descricao' => 'Unidade 2022-06-1014-35-37', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000015, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-35-45', 'descricao' => 'Unidade 2022-06-1014-35-45', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000016, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-35-51', 'descricao' => 'Unidade 2022-06-1014-35-51', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000017, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-35-57', 'descricao' => 'Unidade 2022-06-1014-35-57', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000018, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-36-4', 'descricao' => 'Unidade 2022-06-1014-36-4', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000019, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-36-10', 'descricao' => 'Unidade 2022-06-1014-36-10', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000020, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-36-17', 'descricao' => 'Unidade 2022-06-1014-36-17', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000021, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-36-25', 'descricao' => 'Unidade 2022-06-1014-36-25', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000022, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-36-30', 'descricao' => 'Unidade 2022-06-1014-36-30', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000023, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-36-37', 'descricao' => 'Unidade 2022-06-1014-36-37', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000024, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-36-43', 'descricao' => 'Unidade 2022-06-1014-36-43', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000025, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-36-50', 'descricao' => 'Unidade 2022-06-1014-36-50', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000026, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-36-55', 'descricao' => 'Unidade 2022-06-1014-36-55', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000027, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-1', 'descricao' => 'Unidade 2022-06-1014-37-1', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000028, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-8', 'descricao' => 'Unidade 2022-06-1014-37-8', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000029, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-13', 'descricao' => 'Unidade 2022-06-1014-37-13', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000030, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-18', 'descricao' => 'Unidade 2022-06-1014-37-18', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000031, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-24', 'descricao' => 'Unidade 2022-06-1014-37-24', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000032, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-29', 'descricao' => 'Unidade 2022-06-1014-37-29', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000033, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-36', 'descricao' => 'Unidade 2022-06-1014-37-36', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000034, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-42', 'descricao' => 'Unidade 2022-06-1014-37-42', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000035, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-47', 'descricao' => 'Unidade 2022-06-1014-37-47', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000036, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-52', 'descricao' => 'Unidade 2022-06-1014-37-52', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000037, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-37-57', 'descricao' => 'Unidade 2022-06-1014-37-57', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000038, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-38-2', 'descricao' => 'Unidade 2022-06-1014-38-2', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000039, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-38-8', 'descricao' => 'Unidade 2022-06-1014-38-8', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000040, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-38-13', 'descricao' => 'Unidade 2022-06-1014-38-13', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000041, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-38-18', 'descricao' => 'Unidade 2022-06-1014-38-18', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000042, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-38-24', 'descricao' => 'Unidade 2022-06-1014-38-24', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
    ['id_unidade' => 110000043, 'id_orgao' => 0, 'sigla' => 'Un-2022-06-10-14-38-29', 'descricao' => 'Unidade 2022-06-1014-38-29', 'sin_ativo' => 'S', 'sin_global' => 'N', 'id_origem' => null],
  ];

  $usuarios = [
    ['id_usuario' => 100000002, 'id_orgao' => 0, 'sigla' => 'usuario3', 'nome' => 'Usuario de Teste 3', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 3', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000003, 'id_orgao' => 0, 'sigla' => 'usuario4', 'nome' => 'Usuario de Teste 4', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 4', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000004, 'id_orgao' => 0, 'sigla' => 'usuario1', 'nome' => 'Usuario de Teste 1', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 1', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000005, 'id_orgao' => 0, 'sigla' => 'usuario5', 'nome' => 'Usuario de Teste 5', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 5', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000006, 'id_orgao' => 0, 'sigla' => 'usuario2', 'nome' => 'Usuario de Teste 2', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 2', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000007, 'id_orgao' => 0, 'sigla' => 'usuario6', 'nome' => 'Usuario de Teste 6', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 6', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000008, 'id_orgao' => 0, 'sigla' => 'usuario7', 'nome' => 'Usuario de Teste 7', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 7', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000009, 'id_orgao' => 0, 'sigla' => 'usuario8', 'nome' => 'Usuario de Teste 8', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 8', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000010, 'id_orgao' => 0, 'sigla' => 'usuario9', 'nome' => 'Usuario de Teste 9', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 9', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000011, 'id_orgao' => 0, 'sigla' => 'usuario10', 'nome' => 'Usuario de Teste 10', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 10', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000012, 'id_orgao' => 0, 'sigla' => 'usuario11', 'nome' => 'Usuario de Teste 11', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 11', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000013, 'id_orgao' => 0, 'sigla' => 'usuario12', 'nome' => 'Usuario de Teste 12', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 12', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000014, 'id_orgao' => 0, 'sigla' => 'usuario13', 'nome' => 'Usuario de Teste 13', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 13', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000015, 'id_orgao' => 0, 'sigla' => 'usuario14', 'nome' => 'Usuario de Teste 14', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 14', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000016, 'id_orgao' => 0, 'sigla' => 'usuario15', 'nome' => 'Usuario de Teste 15', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 15', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000017, 'id_orgao' => 0, 'sigla' => 'usuario16', 'nome' => 'Usuario de Teste 16', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 16', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000018, 'id_orgao' => 0, 'sigla' => 'usuario17', 'nome' => 'Usuario de Teste 17', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 17', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000019, 'id_orgao' => 0, 'sigla' => 'usuario18', 'nome' => 'Usuario de Teste 18', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 18', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000020, 'id_orgao' => 0, 'sigla' => 'usuario19', 'nome' => 'Usuario de Teste 19', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 19', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000021, 'id_orgao' => 0, 'sigla' => 'usuario20', 'nome' => 'Usuario de Teste 20', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 20', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000022, 'id_orgao' => 0, 'sigla' => 'usuario21', 'nome' => 'Usuario de Teste 21', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 21', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000023, 'id_orgao' => 0, 'sigla' => 'usuario22', 'nome' => 'Usuario de Teste 22', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 22', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000024, 'id_orgao' => 0, 'sigla' => 'usuario23', 'nome' => 'Usuario de Teste 23', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 23', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000025, 'id_orgao' => 0, 'sigla' => 'usuario24', 'nome' => 'Usuario de Teste 24', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 24', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000026, 'id_orgao' => 0, 'sigla' => 'usuario25', 'nome' => 'Usuario de Teste 25', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 25', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000027, 'id_orgao' => 0, 'sigla' => 'usuario26', 'nome' => 'Usuario de Teste 26', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 26', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000028, 'id_orgao' => 0, 'sigla' => 'usuario27', 'nome' => 'Usuario de Teste 27', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 27', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000029, 'id_orgao' => 0, 'sigla' => 'usuario28', 'nome' => 'Usuario de Teste 28', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 28', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000030, 'id_orgao' => 0, 'sigla' => 'usuario29', 'nome' => 'Usuario de Teste 29', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 29', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000031, 'id_orgao' => 0, 'sigla' => 'usuario30', 'nome' => 'Usuario de Teste 30', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 30', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000032, 'id_orgao' => 0, 'sigla' => 'usuario31', 'nome' => 'Usuario de Teste 31', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 31', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000033, 'id_orgao' => 0, 'sigla' => 'usuario32', 'nome' => 'Usuario de Teste 32', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 32', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000034, 'id_orgao' => 0, 'sigla' => 'usuario33', 'nome' => 'Usuario de Teste 33', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 33', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000035, 'id_orgao' => 0, 'sigla' => 'usuario34', 'nome' => 'Usuario de Teste 34', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 34', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000036, 'id_orgao' => 0, 'sigla' => 'usuario35', 'nome' => 'Usuario de Teste 35', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 35', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000037, 'id_orgao' => 0, 'sigla' => 'usuario36', 'nome' => 'Usuario de Teste 36', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 36', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000038, 'id_orgao' => 0, 'sigla' => 'usuario37', 'nome' => 'Usuario de Teste 37', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 37', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000039, 'id_orgao' => 0, 'sigla' => 'usuario38', 'nome' => 'Usuario de Teste 38', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 38', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000040, 'id_orgao' => 0, 'sigla' => 'usuario39', 'nome' => 'Usuario de Teste 39', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 39', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000041, 'id_orgao' => 0, 'sigla' => 'usuario40', 'nome' => 'Usuario de Teste 40', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 40', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000042, 'id_orgao' => 0, 'sigla' => 'usuario41', 'nome' => 'Usuario de Teste 41', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 41', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000043, 'id_orgao' => 0, 'sigla' => 'usuario42', 'nome' => 'Usuario de Teste 42', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 42', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000044, 'id_orgao' => 0, 'sigla' => 'usuario43', 'nome' => 'Usuario de Teste 43', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 43', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000045, 'id_orgao' => 0, 'sigla' => 'usuario44', 'nome' => 'Usuario de Teste 44', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 44', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000046, 'id_orgao' => 0, 'sigla' => 'usuario45', 'nome' => 'Usuario de Teste 45', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 45', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000047, 'id_orgao' => 0, 'sigla' => 'usuario46', 'nome' => 'Usuario de Teste 46', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 46', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000048, 'id_orgao' => 0, 'sigla' => 'usuario47', 'nome' => 'Usuario de Teste 47', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 47', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000049, 'id_orgao' => 0, 'sigla' => 'usuario48', 'nome' => 'Usuario de Teste 48', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 48', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000050, 'id_orgao' => 0, 'sigla' => 'usuario49', 'nome' => 'Usuario de Teste 49', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 49', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000051, 'id_orgao' => 0, 'sigla' => 'usuario50', 'nome' => 'Usuario de Teste 50', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 50', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000052, 'id_orgao' => 0, 'sigla' => 'usuario51', 'nome' => 'Usuario de Teste 51', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 51', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
    ['id_usuario' => 100000053, 'id_orgao' => 0, 'sigla' => 'usuario52', 'nome' => 'Usuario de Teste 52', 'sin_ativo' => 'S', 'id_origem' => null, 'cpf' => null, 'nome_registro_civil' => 'Usuario de Teste 52', 'nome_social' => null, 'email' => null, 'sin_bloqueado' => 'N', 'dth_pausa_2fa' => null],
  ];

  foreach ($unidades as $un) {
    $dtoUnidade = new UnidadeDTO();
    $dtoUnidade->setBolExclusaoLogica(false);
    $dtoUnidade->setNumIdUnidade($un['id_unidade']);
    if ($objUnidadeRN->contar($dtoUnidade) == 0) {
      $dtoUnidade->setNumIdOrgao($un['id_orgao']);
      $dtoUnidade->setStrSigla($un['sigla']);
      $dtoUnidade->setStrDescricao($un['descricao']);
      $dtoUnidade->setStrSinAtivo($un['sin_ativo']);
      $dtoUnidade->setStrSinGlobal($un['sin_global']);
      if ($un['id_origem'] !== null) {
        $dtoUnidade->setStrIdOrigem($un['id_origem']);
      }
      $objUnidade->cadastrar($dtoUnidade);
    }
  }

  $idsUsuariosCadastrados = [];
  foreach ($usuarios as $u) {
    $dtoUsuario = new UsuarioDTO();
    $dtoUsuario->setBolExclusaoLogica(false);
    $dtoUsuario->setNumIdUsuario($u['id_usuario']);
    if ($objUsuarioRN->contar($dtoUsuario) == 0) {
      $dtoUsuario->setNumIdOrgao($u['id_orgao']);
      $dtoUsuario->setStrSigla($u['sigla']);
      $dtoUsuario->setStrNome($u['nome']);
      $dtoUsuario->setStrSinAtivo($u['sin_ativo']);
      $dtoUsuario->setStrNomeRegistroCivil($u['nome_registro_civil']);
      $dtoUsuario->setStrSinBloqueado($u['sin_bloqueado']);
      if ($u['id_origem'] !== null) {
        $dtoUsuario->setStrIdOrigem($u['id_origem']);
      }
      if ($u['cpf'] !== null) {
        $dtoUsuario->setDblCpf($u['cpf']);
      }
      if ($u['nome_social'] !== null) {
        $dtoUsuario->setStrNomeSocial($u['nome_social']);
      }
      if ($u['email'] !== null) {
        $dtoUsuario->setStrEmail($u['email']);
      }
      if ($u['dth_pausa_2fa'] !== null) {
        $dtoUsuario->setDthPausa2fa($u['dth_pausa_2fa']);
      }
      $retornoCadastro = $objUsuario->cadastrar($dtoUsuario);
      $idsUsuariosCadastrados[] = $retornoCadastro->getNumIdUsuario();
    }
  }

  $objSistemaRN = new SistemaRN();
  $dtoSistema = new SistemaDTO();
  $dtoSistema->retNumIdSistema();
  $dtoSistema->setBolExclusaoLogica(false);
  $dtoSistema->setStrSigla('SEI');
  $arrSistemasSei = $objSistemaRN->listar($dtoSistema);

  $objPerfilRN = new PerfilRN();
  $dtoPerfil = new PerfilDTO();
  $dtoPerfil->retNumIdPerfil();
  $dtoPerfil->setBolExclusaoLogica(false);
  $dtoPerfil->setStrNome('Básico');
  $dtoPerfil->setNumIdSistema($arrSistemasSei[0]->getNumIdSistema());
  $arrPerfisBasico = $objPerfilRN->listar($dtoPerfil);

  $objUnidadeRN = new UnidadeRN();
  $dtoUnidade = new UnidadeDTO();
  $dtoUnidade->retNumIdUnidade();
  $dtoUnidade->setBolExclusaoLogica(false);
  $dtoUnidade->setStrSigla('TESTE_1_2');
  $arrUnidadesTeste = $objUnidadeRN->listar($dtoUnidade);

  $numIdPerfil = !empty($arrPerfisBasico) ? $arrPerfisBasico[0]->getNumIdPerfil() : null;
  $numIdSistema = !empty($arrSistemasSei) ? $arrSistemasSei[0]->getNumIdSistema() : null;
  $numIdUnidade = !empty($arrUnidadesTeste) ? $arrUnidadesTeste[0]->getNumIdUnidade() : null;

  $idsUsuariosCadastrados = array_filter(
      $idsUsuariosCadastrados,
      function($id) { return (int)$id !== 100000004; }
  );

  $objUnidadeRN = new UnidadeRN();
  $dtoUnidade11 = new UnidadeDTO();
  $dtoUnidade11->retNumIdUnidade();
  $dtoUnidade11->setBolExclusaoLogica(false);
  $dtoUnidade11->setStrSigla('TESTE_1_1');
  $arrUnidadesTeste11 = $objUnidadeRN->listar($dtoUnidade11);

  $objUnidadeRN = new UnidadeRN();
  $dtoUnidadeTest1 = new UnidadeDTO();
  $dtoUnidadeTest1->retNumIdUnidade();
  $dtoUnidadeTest1->setBolExclusaoLogica(false);
  $dtoUnidadeTest1->setStrSigla('TESTE');
  $arrUnidadesTeste1 = $objUnidadeRN->listar($dtoUnidadeTest1);

  // Ajuste para prevenir ORA-01861: literal does not match format string
  // Usando TO_DATE para data em Oracle, mas string direta para outros bancos
  if (strtolower(get_class(BancoSip::getInstance())) === 'bancooracle' || getenv('DATABASE_TYPE') === 'Oracle') {
    $dataInicio = "TO_DATE('2022-06-10 00:00:00','YYYY-MM-DD HH24:MI:SS')";
  } else {
    $dataInicio = "'2022-06-10 00:00:00'";
  }
  $sql = 'INSERT INTO permissao (id_perfil, id_sistema, id_usuario, id_unidade, id_tipo_permissao, dta_inicio, dta_fim, sin_subunidades) ' .
         'VALUES (' . $numIdPerfil . ', ' . $numIdSistema . ', ' . 100000004 . ', ' . $arrUnidadesTeste11[0]->getNumIdUnidade() . ", 1, $dataInicio, NULL, 'N' )";
  BancoSip::getInstance()->executarSql($sql);

  $sql = 'INSERT INTO permissao (id_perfil, id_sistema, id_usuario, id_unidade, id_tipo_permissao, dta_inicio, dta_fim, sin_subunidades) ' .
            'VALUES (' . $numIdPerfil . ', ' . $numIdSistema . ', ' . 100000004 . ', ' . $arrUnidadesTeste1[0]->getNumIdUnidade() . ", 1, $dataInicio, NULL, 'N' )";
  BancoSip::getInstance()->executarSql($sql);

  foreach ($idsUsuariosCadastrados as $idUsuario) {
    $numIdUsuario = (int)$idUsuario;
    $sql = 'INSERT INTO permissao (id_perfil, id_sistema, id_usuario, id_unidade, id_tipo_permissao, dta_inicio, dta_fim, sin_subunidades) ' .
            'VALUES (' . $numIdPerfil . ', ' . $numIdSistema . ', ' . $numIdUsuario . ', ' . $numIdUnidade . ", 1, $dataInicio, NULL, 'N' )";
    BancoSip::getInstance()->executarSql($sql);
  }

  $sql = "DELETE FROM infra_parametro WHERE nome = 'VERSAO_MODULO_WSSEI'";
  BancoSip::getInstance()->executarSql($sql);

  BancoSip::getInstance()->confirmarTransacao();
  BancoSip::getInstance()->fecharConexao();

  InfraDebug::getInstance()->gravar('FIM SIP');

} catch (Exception $e) {

  try {
    BancoSip::getInstance()->cancelarTransacao();
  } catch (Exception $e) {}

  try {
    BancoSip::getInstance()->fecharConexao();
  } catch (Exception $e) {}

  echo(InfraException::inspecionar($e));
  try {
    LogSip::getInstance()->gravar(InfraException::inspecionar($e));
  } catch (Exception $e) {}
}
?>
