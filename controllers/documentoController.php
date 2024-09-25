<?php
require_once __DIR__ . '/../models/documento.php';
class DocumentoController
{

    public function insertarDocumentos($request)
{
    // Verificando el contenido de $_POST y $_FILES
    error_log(print_r($_POST, true));
    error_log(print_r($_FILES, true));

    // Accediendo a los datos directamente desde $_POST
    $id_abogado = $_POST['id_abogado'] ?? null;
    $documentos = $_FILES['documento'] ?? null;

    // Verificar que los campos obligatorios no estén vacíos
    if (empty($id_abogado) || empty($documentos)) {
        echo json_encode([
            "status" => "error",
            "message" => "Todos los campos obligatorios deben ser completados."
        ]);
        return;
    }

    // Guardar los archivos en la carpeta privado en el servidor
    $carpeta_abogado = $_SERVER['DOCUMENT_ROOT'] . '/privado/abogados/' . $id_abogado . '/';
    if (!file_exists($carpeta_abogado)) {
        if (!mkdir($carpeta_abogado, 0777, true)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la carpeta del abogado."
            ]);
            return;
        }
    }

    // Verificar si se subió un archivo o múltiples
    if (isset($_FILES['documento']['name']) && is_array($_FILES['documento']['name'])) {
        $total_archivos = count($_FILES['documento']['name']);
    } else {
        $total_archivos = 1; // Solo un archivo
    }

    $formatos_permitidos = ['application/pdf', 'image/png', 'image/jpeg'];
    $max_size = 5 * 1024 * 1024; // 5MB

    for ($i = 0; $i < $total_archivos; $i++) {
        // Obtener los valores del archivo (manejar uno o múltiples archivos)
        if ($total_archivos == 1) {
            $nombre = $_FILES['documento']['name'];
            $tmp_name = $_FILES['documento']['tmp_name'];
            $size = $_FILES['documento']['size'];
            $type = $_FILES['documento']['type'];
            $error = $_FILES['documento']['error'];
        } else {
            $nombre = $_FILES['documento']['name'][$i];
            $tmp_name = $_FILES['documento']['tmp_name'][$i];
            $size = $_FILES['documento']['size'][$i];
            $type = $_FILES['documento']['type'][$i];
            $error = $_FILES['documento']['error'][$i];
        }

        // Verificar si el archivo tiene error
        if ($error != UPLOAD_ERR_OK) {
            echo json_encode(["status" => "error", "message" => "Error al subir el archivo $nombre."]);
            continue;
        }

        // Validar el tamaño del archivo
        if ($size > $max_size) {
            echo json_encode([
                "status" => "error",
                "message" => "El archivo $nombre excede el tamaño máximo permitido (5 MB)."
            ]);
            continue;
        }

        // Validar el formato del archivo
        if (!in_array($type, $formatos_permitidos)) {
            echo json_encode([
                "status" => "error",
                "message" => "Formato de archivo no permitido para $nombre. Solo PDF, PNG, JPG."
            ]);
            continue;
        }

        // Generar un nombre único para cada archivo
        $fecha_actual = date('Ymd');
        $ext = pathinfo($nombre, PATHINFO_EXTENSION);
        $nombre_original = pathinfo($nombre, PATHINFO_FILENAME);
        $nombre_sanitizado = str_replace(' ', '_', strtolower($nombre_original));
        $nombre_archivo = $nombre_sanitizado . '_' . $fecha_actual . '.' . $ext;
        $ruta_archivo = $carpeta_abogado . $nombre_archivo;

        // Mover archivo a la carpeta
        if (move_uploaded_file($tmp_name, $ruta_archivo)) {
            // Guardarlo en la BD
            $fecha_subida = date('Y-m-d H:i:s');
            $result = Documento::save($id_abogado, $nombre_archivo, $ruta_archivo, $fecha_subida);

            if (!$result) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al guardar el documento $nombre en la base de datos."
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al mover el archivo $nombre."
            ]);
        }
    }

    echo json_encode(["status" => "success", "message" => "Todos los documentos fueron procesados."]);
}



}