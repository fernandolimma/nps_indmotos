<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Session;
use App\Core\Router;

date_default_timezone_set('America/Sao_Paulo');

Session::start();
Router::init();

// Rotas públicas
Router::get('/', function() {
    header('Location: ' . Router::baseUrl('/pesquisa'));
    exit;
});

Router::get('/setup', 'AuthController@setup');
Router::post('/setup', 'AuthController@setup');

Router::get('/login', 'AuthController@login');
Router::post('/login/authenticate', 'AuthController@authenticate');
Router::get('/logout', 'AuthController@logout');

Router::get('/pesquisa', 'PesquisaController@index');
Router::post('/pesquisa/submit', 'PesquisaController@submit');

// Rotas administrativas
Router::get('/dashboard', 'DashboardController@index');

Router::get('/carga', 'CargaController@index');
Router::post('/carga/upload', 'CargaController@upload');
Router::post('/carga/disparar', 'CargaController@disparar');
Router::post('/carga/limpar-pendentes', 'CargaController@limparPendentes');

Router::get('/perguntas', 'PerguntasController@index');
Router::get('/perguntas/create', 'PerguntasController@create');
Router::post('/perguntas/store', 'PerguntasController@store');
Router::get('/perguntas/(\d+)/edit', 'PerguntasController@edit');
Router::post('/perguntas/(\d+)/update', 'PerguntasController@update');
Router::get('/perguntas/(\d+)/delete', 'PerguntasController@delete');

Router::get('/relatorios', 'RelatoriosController@index');
Router::get('/relatorios/pendentes', 'RelatoriosController@pendentes');
Router::get('/relatorios/erros', 'RelatoriosController@erros');
Router::get('/relatorios/(\d+)', 'RelatoriosController@detalhe');

Router::get('/configuracoes', 'ConfiguracoesController@index');
Router::post('/configuracoes/save', 'ConfiguracoesController@save');
Router::post('/configuracoes/create-user', 'ConfiguracoesController@createUser');
Router::get('/configuracoes/(\d+)/delete-user', 'ConfiguracoesController@deleteUser');

Router::dispatch();
