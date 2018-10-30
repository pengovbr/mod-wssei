<?php 

//$file = file_get_contents("/opt/sei/web/modulos/mod-wssei/teste.pdf");
$file = file_get_contents("/opt/sei/web/modulos/mod-wssei/c.pdf");

$ch = curl_init("http://192.168.99.100/sei/modulos/mod-wssei/controlador_ws.php/api/v1/documento/externo/criar");

//distribuindo a informaчуo a ser enviada
$post = array(
    'processo'              => '232',
    'dataGeracaoDocumeto'   => '29/01/2017',
    'tipoDocumento'         => '46',
    'numero'                => '12321313',
    'descricao'             => 'Descricao de teste',
    'nomeArquivo'           => 'teste.pdf',
    'nivelAcesso'           => '1',
    'hipoteseLegal'         => '1',
    'grauSigilo'            => '',
    'assuntos'              => '[{"id": 79}]',
    'interessados'          => '[{"id": 100000012 },{"id":100000044}]',
    'destinatarios'         => '[{"id":100000044}]',
    'remetentes'            => '[{"id":100000044}]',
    'conteudoDocumento'     => $file,
    'observacao'            => 'documento Externo',
    'tipoConferencia'       => '2',
);
 
$headers = array();

curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('token: YTRhZDBmOTEyYjUxY2MzYTgzNjc3NDMwNWNjM2JiMzFmY2U4ZTkxYmFUVnhUV2sxYnoxOGZHazFjVTFwTlc4OWZId3dmSHc9'));

$data = curl_exec($ch);
 
//Fecha a conexуo para economizar recursos do servidor
curl_close($ch);

var_dump($data);
die();
 
?>