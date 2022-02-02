<?php

namespace App\Http\Controllers\Batch;

abstract class Importar
{
    abstract public function importar($contenido);
}
