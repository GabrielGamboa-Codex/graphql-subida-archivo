<?php
require_once "./config.php";
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

// Ejecutar la migración
Capsule::schema()->create('files', function (Blueprint $table) {
    $table->increments('id');
    $table->string('filename');
    $table->string('path');
    $table->string('mimetype');
});
