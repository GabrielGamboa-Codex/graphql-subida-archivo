<?php
require 'vendor/autoload.php';
require 'config.php';

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use App\Models\File;

header('Content-Type: application/json');

// Manejo de errores
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

set_exception_handler(function($exception) {
    error_log($exception->getMessage());
    echo json_encode(['error' => ['message' => $exception->getMessage()]]);
    exit();
});

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $tmpName = $_FILES['file']['tmp_name'];
    $uploadDir = 'uploads/';
    $uploadPath = $uploadDir . basename($filename);
    $tempPath = sys_get_temp_dir() . '/' . basename($filename);  // Ruta temporal para el archivo

    if (move_uploaded_file($tmpName, $tempPath)) {
        $mimetype = mime_content_type($tempPath);

        // Definir el esquema y la mutación
        $schema = new Schema([
            'mutation' => new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'uploadFile' => [
                        'type' => new ObjectType([
                            'name' => 'File',
                            'fields' => [
                                'filename' => ['type' => Type::string()],
                                'path' => ['type' => Type::string()],
                                'mimetype' => ['type' => Type::string()],
                            ]
                        ]),
                        'args' => [
                            'filename' => ['type' => Type::string()],
                            'path' => ['type' => Type::string()],
                            'mimetype' => ['type' => Type::string()]
                        ],
                        'resolve' => fn($root, $args) => File::create($args)
                    ]
                ]
            ])
        ]);

        $query = '
            mutation($filename: String!, $path: String!, $mimetype: String!) {
                uploadFile(filename: $filename, path: $path, mimetype: $mimetype) {
                    filename
                    path
                    mimetype
                }
            }
        ';

        $variables = [
            'filename' => $filename,
            'path' => $uploadPath,  // La ruta final del archivo
            'mimetype' => $mimetype
        ];

        try {
            $result = GraphQL::executeQuery($schema, $query, null, null, $variables);
            $output = $result->toArray();

            if (isset($output['data']['uploadFile'])) {
                // Mover el archivo a la ubicación final solo si la mutación fue exitosa
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                rename($tempPath, $uploadPath);
            }
        } catch (\Exception $e) {
            $output = [
                'error' => [
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]
            ];
            error_log($e->getMessage());
            // Eliminar el archivo temporal en caso de error
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        echo json_encode($output);
    } else {
        echo json_encode(['error' => 'Error moving uploaded file to temporary location.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method or no file uploaded.']);
}
