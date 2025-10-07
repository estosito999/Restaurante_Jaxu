<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\RegistroVenta;
use App\Models\DetalleVenta;

class RegistroVentaController extends Controller {

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
        parent::__construct();
    }

    public function create() {
        $registroVenta = new RegistroVenta($this->db);
        $detalleVenta = new DetalleVenta($this->db);
        
        // Suponiendo que tienes los datos de la venta en $data y los detalles en $detalles
        $data = [
            'id_factura' => 1,  // ID de la factura creada
            'id_cierre' => null,  // Se asigna en el cierre de caja
            'id_apertura' => 1,  // ID de la apertura de caja
            'id_empleado' => 1,  // ID del empleado
            'fecha_venta' => date('Y-m-d H:i:s'),
            'monto_venta' => 100.00  // Suma de todos los detalles
        ];

        // Crear el registro de venta
        $registroVentaId = $registroVenta->create($data);

        // Crear los detalles de venta
        $detalles = [
            [
                'id_factura' => 1,
                'id_plato' => 1,
                'cantidad' => 2,
                'precio_unitario' => 25.00,
                'subtotal' => 50.00
            ],
            [
                'id_factura' => 1,
                'id_plato' => 2,
                'cantidad' => 1,
                'precio_unitario' => 30.00,
                'subtotal' => 30.00
            ]
        ];

        foreach ($detalles as $detalle) {
            $detalleVenta->create($detalle);
        }

        // Aquí se puede redirigir al cliente o generar una respuesta de éxito
    }

    // Listar ventas por fecha
    public function index() {
        $registroVenta = new RegistroVenta($this->db);
        $ventas = $registroVenta->all('', "start=2025-10-01&end=2025-10-31");        
        $this->view('ventas/index', ['ventas' => $ventas]);
    }
}
