<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Batch\Importar;
use App\Http\Controllers\Batch\ImportarCartera;
use App\Http\Controllers\Batch\ImportarClientes;
use App\Http\Controllers\Batch\ImportarMarcas;
use App\Http\Controllers\Batch\ImportarProductos;
use App\Http\Controllers\Batch\ImportarVendedores;
use Illuminate\Http\Request;

class BatchDataController extends Controller
{
    public function importarMarcas(Request $request)
    {
        return $this->importar($request, new ImportarMarcas());
    }

    public function importarProductos(Request $request)
    {
        return $this->importar($request, new ImportarProductos());
    }

    public function importarVendedores(Request $request)
    {
        return $this->importar($request, new ImportarVendedores());
    }

    public function importarClientes(Request $request)
    {
        return $this->importar($request, new ImportarClientes());
    }

    public function importarCartera(Request $request)
    {
        return $this->importar($request, new ImportarCartera());
    }

    private function importar(Request $request, Importar $objetoImportar)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'max:300'],
        ]);

        try {

            \DB::beginTransaction();

            $contenido = file_get_contents($request->archivo->path());
            $resultados = $objetoImportar->importar($contenido);

            \DB::commit();
            return response()->json(compact('resultados'));

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'response' => 'error',
                'message' => $ex->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
