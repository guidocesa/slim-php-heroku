<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once './models/PDF.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {        
        $parametros = $request->getParsedBody();    
     /*    var_dump($parametros['tipo']);
        exit;  */   
        if  (isset($parametros['rol_id']) && $parametros['rol_id'] != ""&&
            isset($parametros['username']) && $parametros['username'] != "" &&
            isset($parametros['sector']) && $parametros['sector'] != ""  &&
            isset($parametros['pw']) && $parametros['pw'] != "") 
        {
          $usr = new Usuario();
          $usr->pw = $parametros['pw'];     
          $usr->username = $parametros['username'];     
          $usr->sector = $parametros['sector'];     
          $usr->rol_id = $parametros['rol_id'];     
          $usr->crearUsuario();            
          $payload = json_encode(array("mensaje" => "Usuario creado con exito"));  
          $response->getBody()->write($payload);          
          return $response
            ->withHeader('Content-Type', 'application/json');
        } else{
          $payload = json_encode(array("mensaje" => "Ocurrio un error"));  
          $response->getBody()->write($payload);          
          return $response
            ->withHeader('Content-Type', 'application/json');
        }
    }    

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }


    public  static function CrearPDF($request, $response, $args){  
    
      $pdf = new PDF();       
      //header
      $pdf->AddPage();
      //foter page
      $pdf->AliasNbPages();
      $pdf->SetFont('Arial','B',12);      
      $pdf->Output("F","reporte.pdf",true);

      $payload = json_encode(array("mensaje" => "PDF generado con exito"));  
      $response->getBody()->write($payload);          
      return $response
        ->withHeader('Content-Type', 'application/json');  
  }

  
   
    
}