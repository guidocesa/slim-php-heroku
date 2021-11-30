<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/LoggerController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/EncuestaController.php';
include_once './middlewares/UserMiddleware.php';
require_once './db/AccesoDatos.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();
/* $app->setBasePath('/public'); */
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("hola alumnos de los lunes!");
    return $response;
});

// peticiones
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos'); 
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
  });

 $app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos'); 
  $group->post('[/]', \ProductoController::class . ':CargarUno');
}); 

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':TraerTodos'); 
  $group->post('[/]', \MesaController::class . ':CargarUno');
  $group->post('/servir', \MesaController::class . ':ServirMesa')->add(\UserMiddleware::class . ':SumarOperacionAEmpleado')->add(\UserMiddleware::class . ':ValidarMozo')->add(\UserMiddleware::class . ':ValidarToken');
  $group->post('/cobrar_mesa', \MesaController::class . ':CobrarMesa')->add(\UserMiddleware::class . ':SumarOperacionAEmpleado')->add(\UserMiddleware::class . ':ValidarMozo')->add(\UserMiddleware::class . ':ValidarToken');
  $group->post('/cerrar_mesa', \MesaController::class . ':CerrarMesa')->add(\UserMiddleware::class . ':SumarOperacionAEmpleado')->add(\UserMiddleware::class . ':ValidarSocio')->add(\UserMiddleware::class . ':ValidarToken');
  $group->get('/mas_usada', \MesaController::class . ':ObtenerLaMasUsada')->add(\UserMiddleware::class . ':ValidarSocio')->add(\UserMiddleware::class . ':ValidarToken');

}); 

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos'); 
  $group->get('/mis_pendientes', \PedidoController::class . ':TraerPendientes')->add(\UserMiddleware::class . ':ValidarToken'); 
  $group->get('/mis_en_preparacion', \PedidoController::class . ':TraerEnPreparacion')->add(\UserMiddleware::class . ':ValidarToken'); 
  $group->post('[/]', \PedidoController::class . ':CargarUno')->add(\UserMiddleware::class . ':SumarOperacionAEmpleado')->add(\UserMiddleware::class . ':ValidarToken');
  $group->post('/subir_foto', \PedidoController::class . ':SubirFoto')->add(\UserMiddleware::class . ':SumarOperacionAEmpleado')->add(\UserMiddleware::class . ':ValidarToken');    
  $group->post('/iniciar', \PedidoController::class . ':ComenzarPedido')->add(\UserMiddleware::class . ':SumarOperacionAEmpleado')->add(\UserMiddleware::class . ':ValidarToken');  
  $group->post('/finalizar', \PedidoController::class . ':FinalizarPreparacionC')->add(\UserMiddleware::class . ':SumarOperacionAEmpleado')->add(\UserMiddleware::class . ':ValidarToken');  
}); 

$app->group('/consultas', function (RouteCollectorProxy $group) {
  $group->post('/demora', \PedidoController::class . ':ConsultarDemora');
  $group->get('/pendientes', \PedidoController::class . ':ObtenerPendientes');
  $group->get('/listos_servir', \PedidoController::class . ':ObtenerListosServir');
  $group->get('/pedidos_demorados', \PedidoController::class . ':ObtenerPedidosDemorados');
  $group->get('/logo', \UsuarioController::class . ':CrearPDF');

}); 

$app->group('/encuesta',  function (RouteCollectorProxy $group) 
{
  $group->post('/crear_una', \EncuestaController::class . ':CrearUna');
  $group->get('/mejor_encuesta', \EncuestaController::class . ':getBestEncuesta');
});


$app->group('/login', function (RouteCollectorProxy $group) {
  $group->post('[/]', \LoggerController::class . ':LogIn'); 
}); 

// Run app
$app->run();

