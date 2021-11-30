<?php

class Producto
{
    public $id;
    public $product_name;
    public $price;       
    public $rol_id; 
    public $prep_time_default;      

    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (product_name,price,rol_id,prep_time_default) VALUES (:product_name,:price,:rol_id,:prep_time_default)");       
        $consulta->bindValue(':product_name', $this->product_name, PDO::PARAM_STR);
        $consulta->bindValue(':prep_time_default', $this->prep_time_default, PDO::PARAM_STR);     
        $consulta->bindValue(':price', $this->price, PDO::PARAM_INT);   
        $consulta->bindValue(':rol_id', $this->rol_id, PDO::PARAM_INT);   
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, product_name, price, rol_id,prep_time_default FROM productos");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function ObtenerDemoraDefault($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT prep_time_default FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
}