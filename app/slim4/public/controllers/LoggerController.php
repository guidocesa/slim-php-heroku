<?php
require_once './models/Logger.php';
require_once './models/Token.php';
require_once './interfaces/IApiUsable.php';

class LoggerController extends Logger 
{
    public function LogIn($request, $response, $args)
    {        
      
        $parametros = $request->getParsedBody();   
      
        if  (isset($parametros['username']) && $parametros['username'] != "" &&
            isset($parametros['pw']) && $parametros['pw'] != "" ) 
        {
          $log = new Logger();          
          $log->username = $parametros['username'];     
          $log->pw = $parametros['pw'];  
          $resultado = $log->log_in();
          if($resultado){
            $token = Token::CodificarToken($resultado['username'],$resultado['rol_id'],$resultado['id']);  
            $payload = json_encode(array("mensaje" => "OK token: ".$token));  
            $response->getBody()->write($payload);          
            return $response
              ->withHeader('Content-Type', 'application/json');
          }else{
            $payload = json_encode(array("mensaje" => "Usuario o contraseña incorrectos"));  
            $response->getBody()->write($payload);          
            return $response
              ->withHeader('Content-Type', 'application/json');
          }          
        
        } else{
          $payload = json_encode(array("mensaje" => "Ocurrio un error"));  
          $response->getBody()->write($payload);          
          return $response
            ->withHeader('Content-Type', 'application/json');
        }
    }    

   
    
}