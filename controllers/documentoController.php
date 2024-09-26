<?php
require_once __DIR__ . '/../models/documento.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
class DocumentoController
{

    public function insertarDocumentos($request)
    {
        $headers = apache_request_headers();
        $user = AuthMiddleware::validateAuth($headers);

        if (!$user || !isset($user['sub'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Acceso no autorizado."
            ]);
            return;
        }


        // Verifica si el usuario es un abogado
        if (!isset($user['role']) || $user['role'] !== 'abogado') {
            echo json_encode([
                "status" => "error",
                "message" => "No tiene permisos para subir documentos. Se requiere rol de abogado."
            ]);
            return;
        }


        // $id_abogado = $user['idUser'];
        $id_abogado = $user["sub"];
        //  error_log(print_r($id_abogado, true));

        //error_log(print_r($_POST, true));
        //error_log(print_r($_FILES, true));

        $documentos = $_FILES['documento'] ?? null;
        //$id_abogado = $_POST['id_abogado'] ?? null;

        if (empty($id_abogado) || empty($documentos)) {
            echo json_encode([
                "status" => "error",
                "message" => "Todos los campos obligatorios deben ser completados."
            ]);
            return;
        }

        $carpeta_abogado = $_SERVER['DOCUMENT_ROOT'] . '/privado/abogados/' . $id_abogado . '/';
        if (!file_exists($carpeta_abogado) && !mkdir($carpeta_abogado, 0777, true)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la carpeta del abogado."
            ]);
            return;
        }

        $formatos_permitidos = ['application/pdf', 'image/png', 'image/jpeg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $errores = [];

        // Verifica si se subió un archivo o múltiples
        $total_archivos = isset($documentos['name']) && is_array($documentos['name']) ? count($documentos['name']) : 1;

        for ($i = 0; $i < $total_archivos; $i++) {
            // Obtener los valores del archivo
            $nombre = $total_archivos == 1 ? $documentos['name'] : $documentos['name'][$i];
            $tmp_name = $total_archivos == 1 ? $documentos['tmp_name'] : $documentos['tmp_name'][$i];
            $size = $total_archivos == 1 ? $documentos['size'] : $documentos['size'][$i];
            $type = $total_archivos == 1 ? $documentos['type'] : $documentos['type'][$i];
            $error = $total_archivos == 1 ? $documentos['error'] : $documentos['error'][$i];

            // Verificar si el archivo tiene error
            if ($error != UPLOAD_ERR_OK) {
                $errores[] = "Error al subir el archivo $nombre.";
                continue;
            }

            // Validar el tamaño del archivo
            if ($size > $max_size) {
                $errores[] = "El archivo $nombre excede el tamaño máximo permitido (5 MB).";
                continue;
            }

            // Validar el formato del archivo
            if (!in_array($type, $formatos_permitidos)) {
                $errores[] = "Formato de archivo no permitido para $nombre. Solo PDF, PNG, JPG.";
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
                    $errores[] = "Error al guardar el documento $nombre en la base de datos.";
                }
            } else {
                $errores[] = "Error al mover el archivo $nombre.";
            }
        }

        if (!empty($errores)) {
            echo json_encode([
                "status" => "error",
                "messages" => $errores // Muestra todos los errores encontrados
            ]);
        } else {
            echo json_encode(["status" => "success", "message" => "Todos los documentos fueron procesados."]);
        }
    }




}