<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {        
        $parametros = $request->getParsedBody();    
     
        if  (isset($parametros['producto']) && $parametros['producto'] != "" &&
        isset($parametros['cliente']) && $parametros['cliente'] != "" &&
        isset($parametros['cantidad']) && $parametros['cantidad'] != "") 
        {         
          $pedido = new Pedido();                         
          $pedido->cliente_name = $parametros['cliente'];  
          $mesa_id = Mesa::obtenerIdSegunCodigo($parametros['mesa']);  
          $pedido->mesa_id = $mesa_id['id'];  
          $pedido->codigo = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5); 
          if (isset($_FILES['foto'])){
            $pedido->foto = $_FILES['foto']['name'];   
            Pedido::SubirFotoPedido($_FILES['foto'],$pedido->codigo);           
          }                    
          $pedido->preparation_time_total =  null;  
          $pedido_id = $pedido->crearPedido();       
          foreach ($parametros['producto'] as $key=>$producto) {         
            $item = Pedido::crearItemPedido($pedido_id,$producto,$parametros['cantidad'][$key]);      
          }  
          Mesa::cambiarEstadoMesa($parametros['mesa'],1);            
          $payload = json_encode(array("mensaje" => "Pedido creado con exito, Su código de pedido es: ".$pedido->codigo));  
          $response->getBody()->write($payload);          
          return $response
            ->withHeader('Content-Type', 'application/json');
        } else{
          $payload = json_encode(array("mensaje" => "Ocurrio un error al crear el pedido"));  
          $response->getBody()->write($payload);          
          return $response
            ->withHeader('Content-Type', 'application/json');
        }
    }    

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function SubirFoto($request, $response, $args)
    {
      $parametros = $request->getParsedBody();    
      if (isset($parametros['nro_pedido']) && $parametros['nro_pedido'] != "" && isset($_FILES['foto'])){        
        Pedido::SubirFotoPedido($_FILES['foto'],$parametros['nro_pedido']);   
        $rta = Pedido::UpdateFoto($parametros['nro_pedido'],$_FILES['foto']['name']);   
        if ($rta > 0){
          $payload = json_encode(array("mensaje" => "Foto subida"));  
          $response->getBody()->write($payload);          
          return $response
            ->withHeader('Content-Type', 'application/json');  
        }else{
          $payload = json_encode(array("mensaje" => "ERROR"));  
          $response->getBody()->write($payload);          
          return $response
            ->withHeader('Content-Type', 'application/json');  
        }                
      }else{
        $payload = json_encode(array("mensaje" => "ERROR AL CARGAR LOS CAMPOS"));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');  
      }     
    }

    
    public function TraerPendientes($request, $response, $args)
    {
      $token = $request->getHeader('Authorization');           
      $validacionToken = Token::DecodificarToken(substr($token[0],7));      
      $rol =  Usuario::ObtenerRol($validacionToken['Payload']->id);
      $lista = Pedido::obtenerPedidos($rol[0],0);
      $payload = json_encode(array("listaPedidosPendientes" => $lista));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

       
    public function ComenzarPedido($request, $response, $args)
    {
      $parametros = $request->getParsedBody();       
      $rta = Pedido::IniciarPreparacion($parametros['nro_pedido'],$parametros['item_id'],$parametros['prep_time']);       
        if ($rta > 0){
        $payload = json_encode(array("mensaje" => "Item en preparación... Tiempo estipulado: ". $parametros['prep_time']));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');
      }else{
        $payload = json_encode(array("mensaje" => "El item ya está en preparación"));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');
      }
    }

     
    public function ConsultarDemora($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $rta = Pedido::GetDemora($parametros['nro_mesa'], $parametros['nro_pedido']);
    
      $payload = json_encode(array("Demora" => $rta));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerPendientes($request, $response, $args)
    {
      $rta = Pedido::obtenerTodosLosPendientes();
    
      $payload = json_encode(array("PedidosEnCurso" => $rta));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function TraerEnPreparacion($request, $response, $args)
    {
      $token = $request->getHeader('Authorization');           
      $validacionToken = Token::DecodificarToken(substr($token[0],7));     
      $rol =  Usuario::ObtenerRol($validacionToken['Payload']->id);
      $lista = Pedido::obtenerPedidos($rol[0],1);
      $payload = json_encode(array("listaPedidosEnPreparacion" => $lista));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

          
    public function FinalizarPreparacionC($request, $response, $args)
    {
      $parametros = $request->getParsedBody();       
      $rta = Pedido::FinalizarPreparacion($parametros['nro_pedido'],$parametros['item_id']);       
        if ($rta > 0){
        $payload = json_encode(array("mensaje" => "Item listo para servir!"));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');
      }else{
        $payload = json_encode(array("mensaje" => "Ese item ya se encontraba listo para servir"));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');
      }
    }

    public function ObtenerListosServir($request, $response, $args)
    {
      $lista = Pedido::obtenerPedidosListosParaServir();
      $listaFiltrada = array();
      foreach ($lista as $pedido) {
      if($pedido['total'] > 0 && $pedido['total'] == $pedido['listas']){
      array_push($listaFiltrada,$pedido);
      }   
      }
      $payload = json_encode(array("listaPedidosListosParaServir" => $listaFiltrada));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerPedidosDemorados($request, $response, $args)
    {
      $lista =  Pedido::ObtenerDemorados();
      $payload = json_encode(array("listaPedidosDemorados" => $lista));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');      
    }
  

    
  



    
}