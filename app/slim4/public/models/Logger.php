<?php


class Logger
{ 
    public $username;
    public $pw;
  

    public function log_in(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();      
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios  WHERE username = :username AND pw = :pw");       
        $consulta->bindValue(':username', $this->username, PDO::PARAM_STR); 
        $consulta->bindValue(':pw', $this->pw, PDO::PARAM_STR);      
        $consulta->execute();
        $resultado = $consulta->fetch();          
        return $resultado;       
    }

    public function save_log_in($user_id){
        $hora = date('H:m');
        $objAccesoDatos = AccesoDatos::obtenerInstancia();      
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ingresos_salidas (user_id,sign_in) VALUES (:user_id,:sign_in)");       
        $consulta->bindValue(':user_id', $user_id, PDO::PARAM_STR); 
        $consulta->bindValue(':sign_in', $hora, PDO::PARAM_STR);      
        $consulta->execute();
        $resultado = $consulta->fetch();          
        return $resultado;       
    }

    public function save_log_out($user_id){
        $hora = date('H:m');
        $objAccesoDatos = AccesoDatos::obtenerInstancia();      
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ingresos_salidas (user_id,sign_out) VALUES (:user_id,:sign_out)");       
        $consulta->bindValue(':user_id', $user_id, PDO::PARAM_STR); 
        $consulta->bindValue(':sign_out', $hora, PDO::PARAM_STR);      
        $consulta->execute();
        $resultado = $consulta->fetch();          
        return $resultado;       
    }

  
}