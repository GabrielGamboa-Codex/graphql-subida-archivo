<?php

/*function handleFileUpload($fileKey = 'file', $uploadDir = 'uploads/') {: Esta línea define una función llamada 
handleFileUpload que acepta dos parámetros opcionales:

$fileKey: La clave del archivo en el array $_FILES, por defecto es 'file'.

$uploadDir: El directorio donde se suben los archivos, por defecto es 'uploads/'. */
function handleFileUpload($fileKey = 'file', $uploadDir = 'uploads/') {

    /*if (!is_dir($uploadDir)) {: Verifica si el directorio de subida no existe.
    mkdir($uploadDir, 0775, true);: Si el directorio no existe, lo crea con permisos 0775. 
    El tercer parámetro true indica que también debe crear directorios padres si no existen. */
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES[$fileKey])) {
        /*$tmpName = $_FILES[$fileKey]['tmp_name'];: Obtiene el nombre temporal del archivo en el servidor.
        $uploadPath = $uploadDir . basename($filename);: Define la ruta de subida final del archivo 
        combinando el directorio de subida y el nombre del archivo. */
        $filename = $_FILES[$fileKey]['name'];
        $tmpName = $_FILES[$fileKey]['tmp_name'];
        $uploadPath = $uploadDir . basename($filename);

        if (move_uploaded_file($tmpName, $uploadPath)) {
            return [
                'filename' => $filename,
                'path' => $uploadPath,
                'mimetype' => mime_content_type($uploadPath)
            ];
        } else {
            throw new Exception('Error moving uploaded file.');
        }
    } else {
        throw new Exception('Invalid request method or no file uploaded.');
    }
}



/**<?php
require 'vendor/autoload.php';
require 'config.php';

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use App\Models\File;

header('Content-Type: application/json');

try {
    // Define el tipo de archivo
    $fileType = new ObjectType([
        'name' => 'File',
        'fields' => [
            'filename' => ['type' => Type::string()],
            'path' => ['type' => Type::string()],
            'mimetype' => ['type' => Type::string()],
        ],
    ]);

    // Define la consulta (aunque sea mínima)
    $queryType = new ObjectType([
        'name' => 'Query',
        'fields' => [
            'hello' => [
                'type' => Type::string(),
                'resolve' => function() {
                    return 'Hello world!';
                }
            ]
        ]
    ]);

    // Define la mutación
    $mutationType = new ObjectType([
        'name' => 'Mutation',
        'fields' => [
            'uploadFile' => [
                'type' => $fileType,
                'args' => [
                    'filename' => ['type' => Type::string()],
                    'path' => ['type' => Type::string()],
                    'mimetype' => ['type' => Type::string()]
                ],
                'resolve' => function($root, $args) {
                    try {
                        $file = File::create([
                            'filename' => $args['filename'],
                            'path' => $args['path'],
                            'mimetype' => $args['mimetype']
                        ]);

                        return $file;
                    } catch (\Exception $e) {
                        throw new \Exception('Error in resolve function: ' . $e->getMessage());
                    }
                }
            ]
        ]
    ]);

    // Crear un nuevo esquema GraphQL
    $schema = new Schema([
        'query' => $queryType,
        'mutation' => $mutationType
    ]);

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (!isset($input['query'])) {
        throw new \Exception('No query found in input JSON.');
    }

    $query = $input['query'];
    $variableValues = isset($input['variables']) ? $input['variables'] : null;

    $rootValue = [];
    $result = GraphQL::executeQuery($schema, $query, $rootValue, null, $variableValues);
    $output = $result->toArray();
} catch (\Exception $e) {
    $output = [
        'error' => [
            'message' => 'Internal server error: ' . $e->getMessage()
        ]
    ];
    error_log($e->getMessage());
}

echo json_encode($output);

 */