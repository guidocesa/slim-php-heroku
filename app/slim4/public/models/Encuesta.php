<?php

class Encuesta
{
   public $id;
   public $comentario;
   public $puntuacion_mesa;
   public $puntuacion_mozo;
   public $puntuacion_restaurante;
   public $puntuacion_cocinero;
   public $mesa_id;
   public $pedido_id;

    public function crearEncuesta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO encuesta (comentario,puntuacion_mesa,puntuacion_mozo,puntuacion_restaurante,puntuacion_cocinero,mesa_id,pedido_id) 
        VALUES (:comentario,:puntuacion_mesa,:puntuacion_mozo,:puntuacion_restaurante,:puntuacion_cocinero,:mesa_id,:pedido_id)");       
        $consulta->bindValue(':comentario', $this->comentario, PDO::PARAM_STR);
        $consulta->bindValue(':puntuacion_mesa', $this->puntuacion_mesa, PDO::PARAM_INT); 
        $consulta->bindValue(':puntuacion_mozo', $this->puntuacion_mozo, PDO::PARAM_INT); 
        $consulta->bindValue(':puntuacion_restaurante', $this->puntuacion_restaurante, PDO::PARAM_INT); 
        $consulta->bindValue(':puntuacion_cocinero', $this->puntuacion_cocinero, PDO::PARAM_INT); 
        $consulta->bindValue(':mesa_id', $this->mesa_id, PDO::PARAM_INT);      
        $consulta->bindValue(':pedido_id', $this->pedido_id, PDO::PARAM_INT);   
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerMejorEncuesta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT AVG(puntuacion_mesa+puntuacion_mozo+puntuacion_restaurante+puntuacion_cocinero)/4.0 as avg, mesa_id as mesa, comentario as comentario FROM `encuesta` GROUP BY id ORDER BY avg DESC;");     
        $consulta->execute();
        return $consulta->fetch();
    }



}

?>