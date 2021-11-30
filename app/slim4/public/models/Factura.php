<?php

class Factura
{
    public $id;
    public $mesa;
    public $total; 
    public $date;  

    public function crearFactura()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $now = date('Y-m-d H:i:s');
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO facturas (mesa_id,total_cost,fecha) VALUES (:mesa_id,:total,:fecha)");       
        $consulta->bindValue(':mesa_id', $this->mesa, PDO::PARAM_INT);      
        $consulta->bindValue(':total', $this->total, PDO::PARAM_INT);   
        $consulta->bindValue(':fecha', $now, PDO::PARAM_INT);   
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }



}