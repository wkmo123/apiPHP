<?php
require_once __DIR__ . '/../models/documento.php';
class DocumentoController
{

    public function insertarDocumentos($request, $files)
    {
        $id_abogado = $request['id_abogado'] ?? null;
        $documento = $files['documento'] ?? null;

        // Verificar que los campos obligatorios no estén vacíos
        if (empty($id_abogado) || empty($documento)) {
            echo json_encode([
                "status" => "error",
                "message" => "Todos los campos obligatorios deben ser completados."
            ]);
            return;
        }

        if (!$documento) {
            echo json_encode(["status" => "error", "message" => "No se envió ningún archivo."]);
            return;
        }

        //Validar el tamaño del archivo que no pase de 5MB
        $max_size = 5 * 1024 * 1024;
        if ($documento['size'] > $max_size) {
            echo json_encode(["status" => "error", "message" => "El archivo excede el tamaño máximo permitido (5 MB)."]);
            return;
        }

        //Validar formato archivos solo (PDF, PNG, JPG) permitidos
        $formatos_permitidos = ['application/pdf', 'image/png', 'image/jpeg'];
        if (!in_array($documento['type'], $formatos_permitidos)) {
            echo json_encode(["status" => "error", "message" => "Formato de archivo no permitido. Solo PDF, PNG, JPG."]);
            return;
        }

        //Crear carpeta si no existe
        $carpeta_abogado = __DIR__ . '/uploads/abogados/' . $id_abogado . '/';
        if (!file_exists($carpeta_abogado)) {
            mkdir($carpeta_abogado, 0777, true);
        }

        //Generar un nombre unico a cada archivo
        $fecha_actual = date('Ymd');
        $ext = pathinfo($documento['name'], PATHINFO_EXTENSION); // para obtener la extension del archivo

        $nombre_original = pathinfo($documento['name'], PATHINFO_FILENAME);
        $nombre_sanitizado = str_replace(' ', '_', strtolower($nombre_original));
        $nombre_archivo = $nombre_sanitizado . '_' . $fecha_actual . '.' . $ext;

        $ruta_archivo = $carpeta_abogado . $nombre_archivo;

        //mover archivo a la carpeta
        if (move_uploaded_file($documento['tmp_name'], $ruta_archivo)) {
            //guardarlo en la bd
            $fecha_subida = date('Y-m-d H:i:s');
            $result = Documento::save($id_abogado, $nombre_archivo, $ruta_archivo, $fecha_subida);

            if ($result) {
                echo json_encode(["status" => "success", "message" => "Documento subido correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al guardar el documento en la base de datos."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error al mover el archivo."]);
        }
    }

}