<?php

class Mesa
{
    public $id;
    public $code;
    public $table_state;       
    

    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (code,table_state) VALUES (:code,:table_state)");       
        $consulta->bindValue(':code', $this->code, PDO::PARAM_STR);      
        $consulta->bindValue(':table_state', $this->table_state, PDO::PARAM_INT);   
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, code, table_state FROM mesas");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function cambiarEstadoMesa($nro_mesa,$estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Mesa::obtenerIdSegunCodigo($nro_mesa);
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET table_state = :estado  WHERE id = :id AND table_state != :estado1");
        $consulta->bindValue(':id', $id['id'], PDO::PARAM_STR);    
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);    
        $consulta->bindValue(':estado1', $estado, PDO::PARAM_STR);    
        $consulta->execute();
        return $consulta->rowCount();;
    }

    public static function getByID($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE id = :id");
        $consulta->bindParam(':id', $id, PDO::PARAM_STR);    
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }



    public static function obtenerIdSegunCodigo($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM mesas WHERE code = :codigo");
        $consulta->bindParam(':codigo', $codigo, PDO::PARAM_STR);    
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function CobrarPedidos($nro_mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Mesa::obtenerIdSegunCodigo($nro_mesa);
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM pedidos WHERE mesa_id = :nro_mesa");
        $consulta->bindParam(':nro_mesa', $id['id'], PDO::PARAM_STR);    
        $consulta->execute();
        $pedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $total = 0;        
        foreach ($pedidos as $pedido) {
            $consulta = $objAccesoDatos->prepararConsulta("SELECT i.product_name as 'Item', p.cantidad as 'Cantidad', i.price as 'Precio' FROM item_pedidos as p 
            LEFT JOIN productos as i ON i.id = p.producto_id
            LEFT JOIN pedidos as q ON q.id = p.pedido_id
            WHERE p.pedido_id = :id" );
            $consulta->bindParam(':id', $pedido['id'], PDO::PARAM_INT);    
            $consulta->execute();
            $itemPedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);
            foreach ($itemPedidos as $item) {
                $total += ($item['Cantidad'] * $item['Precio']);
            }
        }
      return $total;
    }


    
    public static function getMasUsada()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT mesa_id as mesa, COUNT(mesa_id) as total FROM facturas GROUP BY mesa_id ORDER BY total DESC ;");       
        $consulta->execute();       
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }



}