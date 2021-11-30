<?php

class Pedido
{
    public $id;
    public $pedido_state;
    public $demora;
    public $cliente_name;
    public $foto = null;
    public $mesa_id;
    public $codigo;
    
    private static function ObtenerPedidoState($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT MIN(estado) AS 'estado'  FROM item_pedidos
        WHERE pedido_id = :id;");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);

    }

    public static function SubirFotoPedido($archivo,$pedido){     
        $dir_subida = './Fotos/';   
        if (!file_exists($dir_subida)) {
            mkdir($dir_subida, 0777, true);    
            echo 'Se creó el directorio';
        }
        $fecha = date('Y-m-d');    
        if (move_uploaded_file($archivo['tmp_name'], $dir_subida.$pedido.'.jpg')) {    
        echo "Se creó correctamente el archivo";
        } else {
        echo "¡Error!\n";
        }
    } 


    public function crearPedido()
    {       
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (cliente_name,foto,mesa_id,codigo) VALUES (:cliente_name,:foto,:mesa_id,:codigo)");         
        $consulta->bindValue(':cliente_name', $this->cliente_name, PDO::PARAM_STR);   
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);   
        $consulta->bindValue(':mesa_id', $this->mesa_id, PDO::PARAM_STR);   
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);   
        $consulta->execute();        
        return $objAccesoDatos->obtenerUltimoId();     
    }

    public function crearItemPedido($pedido,$producto,$cantidad)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $demora = Producto::ObtenerDemoraDefault($producto);
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO item_pedidos (pedido_id,producto_id,cantidad,estado,prep_time) VALUES (:pedido_id,:producto_id,:cantidad,:estado, :prep_time)");       
        $consulta->bindValue(':pedido_id', $pedido, PDO::PARAM_INT);     
        $consulta->bindValue(':producto_id', $producto, PDO::PARAM_INT);     
        $consulta->bindValue(':cantidad', $cantidad, PDO::PARAM_STR);         
        $consulta->bindValue(':estado', 0, PDO::PARAM_INT);
        $consulta->bindValue(':prep_time', $demora['prep_time_default'], PDO::PARAM_INT);         
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }


    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id,cliente_name,codigo,mesa_id FROM pedidos");
        $consulta->execute();
        $pedidos = $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
        foreach ($pedidos as $key => $value) {
            $value->pedido_state = Pedido::ObtenerPedidoState($value->id)['estado'];
            $value->demora = Pedido::GetDemora($value->mesa_id,$value->codigo)[0]['Demora'];
        }
        return $pedidos;
    }

    public static function obtenerPedidos($id,$estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(" SELECT q.product_name as producto, i.pedido_id as pedido, i.cantidad as cantidad, i.estado as estado FROM item_pedidos as i 
        LEFT JOIN pedidos as p ON i.pedido_id = p.id
        LEFT JOIN productos as q ON q.id = i.producto_id
        WHERE q.rol_id = :id AND i.estado = :estado;");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);   
        $consulta->bindValue(':estado', $estado, PDO::PARAM_INT);   
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function IniciarPreparacion($nro_pedido,$item_id,$prep_time)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = $this->obtenerIdSegunCodigo($nro_pedido); 
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE item_pedidos as i
        SET estado = 1, prep_time = :prep_time
        WHERE i.pedido_id = :pedido_id AND i.producto_id = :item_id AND estado = 0");
        $consulta->bindValue(':prep_time', $prep_time, PDO::PARAM_INT);   
        $consulta->bindValue(':pedido_id', $id['id'], PDO::PARAM_INT);   
        $consulta->bindValue(':item_id', $item_id, PDO::PARAM_INT);   
         $consulta->execute();      
         return $consulta->rowCount();
    }

    public function UpdateFoto($nro_pedido,$foto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = $this->obtenerIdSegunCodigo($nro_pedido); 
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos as p
        SET foto = :foto
        WHERE p.id = :pedido_id");
        $consulta->bindValue(':foto', $foto, PDO::PARAM_STR);   
        $consulta->bindValue(':pedido_id', $id['id'], PDO::PARAM_INT);   
        $consulta->execute();      
         return $consulta->rowCount();
    }

    

    
    public static function obtenerIdSegunCodigo($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM pedidos WHERE codigo = :codigo");
        $consulta->bindParam(':codigo', $codigo, PDO::PARAM_STR);    
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function ObtenerIdSegunMesa($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM pedidos WHERE mesa_id = :id");
        $consulta->bindParam(':id', $id, PDO::PARAM_INT);    
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    
    public static function GetDemora($nro_mesa,$nro_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Pedido::obtenerIdSegunCodigo($nro_pedido);   
        var_dump($id['id']);   
        $consulta = $objAccesoDatos->prepararConsulta("SELECT MAX(prep_time) AS 'Demora'
        FROM item_pedidos
        WHERE pedido_id = :nro_pedido;");
        $consulta->bindValue(':nro_pedido', $id['id'], PDO::PARAM_INT);
        $consulta->execute();       
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    
    
    public static function obtenerTodosLosPendientes()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo,  MAX(i.prep_time) AS 'Demora' FROM pedidos as p 
        LEFT JOIN  item_pedidos as i ON i.pedido_id = p.id
        WHERE i.estado = 1 
        GROUP BY codigo;");  
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function EntregarPedido($nro_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Pedido::obtenerIdSegunCodigo($nro_pedido); 
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE item_pedidos as i
        SET estado = 3
        WHERE i.pedido_id = :pedido_id");      
        $consulta->bindValue(':pedido_id', $id['id'], PDO::PARAM_INT); 
         $consulta->execute();      
         return $consulta->rowCount();
    }

    

    public static function obtenerPedidosListosParaServir()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT p.codigo, COUNT(CASE WHEN i.estado=2 THEN 1 END) as listas, COUNT(i.id) as total FROM pedidos as p  LEFT JOIN item_pedidos as i ON i.pedido_id = p.id GROUP BY codigo;");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function ObtenerDemorados()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT q.codigo FROM `item_pedidos` LEFT JOIN productos as p ON p.id = producto_id  LEFT JOIN pedidos as q ON pedido_id = q.id WHERE prep_time > p.prep_time_default GROUP BY pedido_id;");
        $consulta->execute();
        $pedidosDemorados = $consulta->fetchAll(PDO::FETCH_ASSOC);
        return $pedidosDemorados;
    }
   
   


    
}
