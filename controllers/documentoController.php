<?php
require_once __DIR__ . '/../models/documento.php';
class DocumentoController
{

    public function insertarDocumentos($request)
    {
        $id_abogado = $request['id_abogado'] ?? null;
        $tipo_documento = $request['tipo_documento'] ?? '';
        $archivo = $_FILES['archivo'] ?? null; 

        // Verificar que los campos obligatorios no estén vacíos
        if (empty($id_abogado) || empty($tipo_documento) || empty($archivo)) {
            echo json_encode([
                "status" => "error",
                "message" => "Todos los campos obligatorios deben ser completados."
            ]);
            return;
        }

        
    }

}