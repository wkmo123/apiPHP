<?php
require_once __DIR__ . '/../config/database.php';
class Documento
{


    public static function save($id_abogado, $tipo_documento, $ruta_archivo, $fecha_subida)
    {
        $db = getConnection();

        $sql = "INSERT INTO documentos (id_abogado,tipo_documento,ruta_archivo,fecha_subida)
        VALUES(:id_abogado,:tipo_documento,:ruta_archivo,:fecha_subida);";
        $stmt = $db->prepare($sql);

        try {
            $stmt->execute([
                ':id_abogado' => $id_abogado,
                ':tipo_documento' => $tipo_documento,
                ':ruta_archivo' => $ruta_archivo,
                ':fecha_subida' => $fecha_subida
            ]);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("error al guardar documentos: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'No se pudo guardar el documento: ' . $e->getMessage() // Mensaje de error
            ];
        }
    }
}