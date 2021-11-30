<?php
    class UserMiddleware{
        ///Valida el token.
        public static function ValidarToken($request,$handler){
            $token = $request->getHeader("Authorization");           
            $validacionToken = Token::DecodificarToken(substr($token[0],7));
                      if($validacionToken["Estado"] == "OK"){
                $res = $handler->handle($request);           
                return $res;
            }
            else{
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
                $response = $responseFactory->createResponse(401,'No autorizado');   
                $response->getBody()->write($validacionToken['Mensaje']);               
                return $response;
            }
        }

        /// Sólo puede acceder un empleado de tipo socio a esta característica.
        public static function ValidarSocio($request,$handler){
            $token = $request->getHeader("Authorization"); 
            $validacionToken = Token::DecodificarToken(substr($token[0],7));
            $payload = $validacionToken["Payload"];

            if($payload->tipo == 5){
                $res = $handler->handle($request);           
                return $res;
            }
            else{
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
                $response = $responseFactory->createResponse(401,'No autorizado');   
                $response->getBody()->write("Solo para socios...");               
                return $response;
            }
        }

        /// Sólo puede acceder un empleado de tipo mozo o socio a esta característica.
        public static function ValidarMozo($request,$handler){
            $token = $request->getHeader("Authorization"); 
            $validacionToken = Token::DecodificarToken(substr($token[0],7));
            $payload = $validacionToken["Payload"];           
            $tipoEmployee = $payload->tipo;
            if($tipoEmployee == 1 || $tipoEmployee == 5){
                $res = $handler->handle($request);           
                return $res;
            }
            else{
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
                $response = $responseFactory->createResponse(401,'No autorizado');   
                $response->getBody()->write("Solo para mozos o socios");               
                return $response;
            }
        }

        public static function SumarOperacionAEmpleado($request,$handler)
        {
            $token = $request->getHeader("Authorization"); 
            $validacionToken = Token::DecodificarToken(substr($token[0],7));
            $payload = $validacionToken["Payload"];   
            Usuario::SumarOperacion($payload->id);
            $res = $handler->handle($request);           
            return $res;
        }





    }
?>