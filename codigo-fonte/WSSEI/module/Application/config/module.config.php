<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'documento-assinar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/documento/assinar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Documento',
                        'action'     => 'postAssinar',
                    )
                ),
            ),
            'documento-assinar-bloco' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/documento/assinar/bloco',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Documento',
                        'action'     => 'postAssinarBloco',
                    )
                ),
            ),
            'documento-ciencia' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/documento/ciencia/protocolo',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Documento',
                        'action'     => 'postCienciaDocumento',
                    )
                ),
            ),
            'documento-listar-ciencia' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/documento/ciencia/listar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Documento',
                        'action'     => 'getListarCienciaDocumento',
                    )
                ),
            ),
            'documento-listar-assinatura' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/documento/assinatura/listar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Documento',
                        'action'     => 'getListarAssinaturasDocumento',
                    )
                ),
            ),
            'processo-ciencia' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/ciencia/procedimento',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postCienciaProcesso',
                    )
                ),
            ),
            'processo-sobrestar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/sobrestar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postSobrestarProcesso',
                    )
                ),
            ),
            'processo-checar-unidades' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/listar/unidades',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getChecarUnidadesProcesso',
                    )
                ),
            ),
            'processo-checar-sobrestamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/listar/sobrestamento',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getChecarSobrestamento',
                    )
                ),
            ),
            'processo-sobrestar-unidades' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/sobrestar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postSobrestarProcesso',
                    )
                ),
            ),
            'processo-unidades' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/unidades',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getProcessoUnidades',
                    )
                ),
            ),
            'processo-sobrestar-cancelar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/sobrestar/cancelar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postCancelarSobrestarProcesso',
                    )
                ),
            ),
            'processo-listar-ciencia' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/ciencia/listar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getListarCiencia',
                    )
                ),
            ),
            'processo-acompanhar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/acompanhar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postAcompanhar',
                    )
                )
            ),
            'processo-acompanhamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/listar/acompanhamento/usuario',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getProcessoAcompanhamento',
                    )
                )
            ),

            'processo-listar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/listar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getListaProcesso',
                    )
                )
            ),

            'processo-pesquisar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/pesquisar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getPesquisarProcesso',
                    )
                )
            ),

            'processo-listar-documentos' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/listar/documentos',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getListarDocumentos',
                    )
                )
            ),

            'processo-download-anexo' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/processo/anexo/:extensao',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getDownloadAnexo',
                    )
                )
            ),

            'processo-listar-atividades' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/unidade/listar/atividades',
                    'constraints' => array(
                        'procedimento'     => '[0-9]+',
                        'unidade'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getListarAtividades',
                    )
                )
            ),

            'processo-listar-unidades' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/unidade/listar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getListarUnidades',
                    )
                )
            ),

            'processo-listar-cargo-funcao' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/cargo/funcao/listar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getListarCargoFuncao',
                    )
                )
            ),

            'processo-orgao-listar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/orgao/listar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getListarOrgao',
                    )
                )
            ),

            'processo-grupo-listar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/grupo/listar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'getListarGrupoAcompanhamento',
                    )
                )
            ),

            'processo-ciencia-processo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/ciencia/procedimento',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postCienciaProcesso',
                    )
                )
            ),

            'processo-agendar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/retornoProgramado/agendar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postAgendarRetornoProgramado',
                    )
                )
            ),

            'processo-andamento-cadastrar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/andamento/cadastrar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postCadastrarObservacao',
                    )
                )
            )/*,

            'processo-anotacao-cadastrar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/anotacao/cadastrar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postCadastrarAnotacao',
                    )
                )
            )*/,

            'anotacao-cadastrar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/anotacao/cadastrar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Anotacao',
                        'action'     => 'postCriarAnotacao',
                    )
                )
            ),

            'processo-concluir' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/concluir',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postConcluirProcesso',
                    )
                )
            ),

            'processo-atribuir' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/atribuir',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postAtribuirProcesso',
                    )
                )
            ),

            'processo-enviar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/processo/enviar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Processo',
                        'action'     => 'postEnviarProcesso',
                    )
                )
            ),

            'usuario-auth' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/usuario/auth',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Usuario',
                        'action'     => 'auth',
                    )
                )
            ),

            'usuario-listar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/usuario/listar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Usuario',
                        'action'     => 'getUsuarios',
                    )
                )
            ),

            'usuario-alterar-unidade' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/usuario/alterar/unidade',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Usuario',
                        'action'     => 'postAlterarUsuarioUnidade',
                    )
                )
            ),

            'bloco-consultar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/bloco/consultar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Bloco',
                        'action'     => 'getConsultarBloco',
                    )
                )
            ),

            'bloco-consultar-documentos' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/bloco/documentos/consultar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Bloco',
                        'action'     => 'getConsultarDocumentosBloco',
                    )
                )
            ),

            'bloco-cadastrar-anotacao' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/bloco/anotacao/cadastrar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Bloco',
                        'action'     => 'postCadastrarAnotacaoBloco',
                    )
                )
            ),

            'bloco-disponibilizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/bloco/disponibilizar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Bloco',
                        'action'     => 'postDisponibilizarBloco',
                    )
                )
            ),

            'bloco-retornar' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/bloco/retornar',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Bloco',
                        'action'     => 'postRetornarBloco',
                    )
                )
            )
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Anotacao' => 'Application\Controller\AnotacaoController',
            'Application\Controller\Documento' => 'Application\Controller\DocumentoController',
            'Application\Controller\Processo' => 'Application\Controller\ProcessoController',
            'Application\Controller\Usuario' => 'Application\Controller\UsuarioController',
            'Application\Controller\Bloco'  => 'Application\Controller\BlocoController',
            ''
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/clean'            => __DIR__ . '/../view/layout/clean.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                        __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver',
                ),
            ),
        ),
    ),
);
