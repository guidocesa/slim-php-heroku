<?php

class Usuario
{
    public $id;
    //public $tipo;
    public $username;
    public $pw;
    public $sector;
    public $rol_id;
    public $operaciones;
  

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (username,sector,rol_id,pw,operaciones) VALUES (:username,:sector,:rol_id,:pw,:operaciones)");            
        $consulta->bindValue(':username', $this->username, PDO::PARAM_STR);      
        $consulta->bindValue(':pw', $this->pw, PDO::PARAM_STR);      
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);      
        $consulta->bindValue(':rol_id', $this->rol_id, PDO::PARAM_INT);      
        $consulta->bindValue(':operaciones', 0, PDO::PARAM_INT);      
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, username, sector, rol_id, operaciones FROM usuarios");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function ObtenerRol($id)
    { 
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT rol_id FROM usuarios WHERE usuarios.id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);   
        $consulta->execute();
        return $consulta->fetch();    
    }

    public static function SumarOperacion($id_empleado)
    {
      
        try {          
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios SET operaciones = operaciones + 1 WHERE id = :id");            
            $consulta->bindValue(':id', $id_empleado, PDO::PARAM_INT);
            $consulta->execute();          
            $respuesta = array("Estado" => "OK", "Mensaje" => "Operación sumada correctamente.");
        } catch (Exception $e) {
            $mensaje = $e->getMessage();
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "$mensaje");
        }
        finally {
            return $respuesta;
        }
    }

}