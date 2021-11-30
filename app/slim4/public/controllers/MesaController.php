<?php
require_once './models/Mesa.php';
require_once './models/Factura.php';
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {        
        $mesa = new Mesa();
        $mesa->code = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5); 
        $mesa->table_state = 0;   
        $mesa->crearMesa();            
        $payload = json_encode(array("mensaje" => "Mesa creada con exito, su numero es: ".$mesa->code));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');        
    }    

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ServirMesa($request, $response, $args)
    {
      
       $parametros = $request->getParsedBody();    
       $rta = Mesa::cambiarEstadoMesa($parametros['nro_mesa'],2);
       $rta2 = Pedido::EntregarPedido($parametros['codigo_pedido']);      
       if($rta > 0 && $rta2 > 0)
       {
         $payload = json_encode(array("mensaje" => "Mesa Servida"));  
         $response->getBody()->write($payload);          
         return $response
           ->withHeader('Content-Type', 'application/json');
       }
       else
       {
        $payload = json_encode(array("mensaje" => "Error. Mesa ya servida o codigo erroneo."));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');
       }
    }

    public function CobrarMesa($request, $response, $args)
    {
       $parametros = $request->getParsedBody();   
       if(Mesa::cambiarEstadoMesa($parametros['nro_mesa'],3))
       {
         $rta = Mesa::CobrarPedidos($parametros['nro_mesa']);       
         $payload = json_encode(array("mensaje" => "Mesa Cobrada. Total a pagar: $".$rta));  
         $response->getBody()->write($payload);          
         return $response
           ->withHeader('Content-Type', 'application/json');
       }
       else{
        $payload = json_encode(array("mensaje" => "La mesa ya se encuentra cobrada."));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');   
       }   
    }

    public function CerrarMesa($request, $response, $args)
    {
       $parametros = $request->getParsedBody(); 
       $ok = Mesa::cambiarEstadoMesa($parametros['nro_mesa'],4);
       if ($ok > 0 )
       {
        $factura = new Factura();
        $factura->mesa = Mesa::obtenerIdSegunCodigo($parametros['nro_mesa'])['id'];
        $factura->total = $parametros['total'];
        $factura->crearFactura();        
        $payload = json_encode(array("mensaje" => "Mesa cerrada - Factura generada"));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');   
       }else{
        $payload = json_encode(array("mensaje" => "La mesa ya se encuentra cerrada"));  
        $response->getBody()->write($payload);          
        return $response
          ->withHeader('Content-Type', 'application/json');   
       }           
    }

    public function ObtenerLaMasUsada($request, $response, $args)
    {
      $parametros = $request->getParsedBody();    
      $rta = Mesa::getMasUsada();     
      $mesa = Mesa::getByID($rta['mesa']);       
      $payload = json_encode(array("mensaje" => "La mesa mas usada es la mesa: ".$mesa['code'].", con ".$rta['total']." usos" ));  
      $response->getBody()->write($payload);          
      return $response->withHeader('Content-Type', 'application/json');        
    }


    
    
}