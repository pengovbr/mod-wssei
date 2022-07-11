<?php

require_once __DIR__ . '/vendor/autoload.php';

//Classes utilitárias para manipulação dos dados do SEI
require_once __DIR__ . '/src/utils/DatabaseUtils.php';
require_once __DIR__ . '/src/utils/ParameterUtils.php';

//Representação das páginas sob teste
require_once __DIR__ . '/src/paginas/PaginaTeste.php';
require_once __DIR__ . '/src/paginas/PaginaLogin.php';
require_once __DIR__ . '/src/paginas/PaginaControleProcesso.php';
require_once __DIR__ . '/src/paginas/PaginaIniciarProcesso.php';
require_once __DIR__ . '/src/paginas/PaginaProcesso.php';
require_once __DIR__ . '/src/paginas/PaginaEnviarProcesso.php';
require_once __DIR__ . '/src/paginas/PaginaIncluirDocumento.php';
require_once __DIR__ . '/src/paginas/PaginaDocumento.php';
require_once __DIR__ . '/src/paginas/PaginaAssinaturaDocumento.php';
require_once __DIR__ . '/src/paginas/PaginaTramitarProcesso.php';
require_once __DIR__ . '/src/paginas/PaginaConsultarAndamentos.php';
require_once __DIR__ . '/src/paginas/PaginaProcessosTramitadosExternamente.php';
require_once __DIR__ . '/src/paginas/PaginaReciboTramite.php';
require_once __DIR__ . '/src/paginas/PaginaEditarProcesso.php';

