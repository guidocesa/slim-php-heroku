<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {        
        $parametros = $request->getParsedBody();    
     
        if  (isset($parametros['product_name']) && $parametros['product_name'] != ""&&
            isset($parametros['price']) && $parametros['price'] != ""  &&
            isset($parametros['prep_time_default']) && $parametros['prep_time_default'] != ""  ) 
        {
          $producto = new Producto();
          $producto->product_name = $parametros['product_name'];     
          $producto->price = $parametros['price'];   
          $producto->rol_id = $parametros['rol_id'];
          $producto->prep_time_default = $parametros['prep_time_default'];           
          $producto->crearProducto();            
          $payload = json_encode(array("mensaje" => "Producto creado con exito"));  
          $response->getBody()->write($payload);          
          return $response
            ->withHeader('Content-Type', 'application/json');
        } else{
          $payload = json_encode(array("mensaje" => "Ocurrio un error al crear el producto"));  
          $response->getBody()->write($payload);          
          return $response
            ->withHeader('Content-Type', 'application/json');
        }
    }    

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
}