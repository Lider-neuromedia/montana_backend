<?php

namespace App\Http\Controllers;

use App\Entities\User;
use Illuminate\Http\Request;

class VendedorClienteController extends Controller
{
    public function index()
    {
        return \DB::table('vendedor_cliente')->get();
    }

    /**
     * TODO: se debe asignar el vendedor a la tienda y al cliente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vendedor_id' => ['required', 'integer', 'exists:users,id'],
            'cliente_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $vendedor = User::query()
            ->where('id', $request->get('vendedor_id'))
            ->where('rol_id', 2)
            ->firstOrFail();

        $cliente = User::query()
            ->where('id', $request->get('cliente_id'))
            ->where('rol_id', 3)
            ->firstOrFail();

        $cliente->vendedores()->syncWithoutDetaching([$vendedor->id]);

        return response()->json([
            'response' => 'success',
            'message' => 'Cliente asignado a vendedor correctamente.',
            'satatus' => 200,
        ], 200);
    }

    /**
     * Obtener clientes asignados al vendedor (id).
     */
    public function show($id)
    {
        $vendedor = User::findOrFail($id);
        $clientes = $vendedor->clientes()->get();
        return $clientes;
    }

    /**
     * Obtener el vendedor asignado al cliente logueado.
     * TODO: se debe entregar varios vendedores, no solo uno.
     */
    public function vendedorAsignado()
    {
        $cliente = auth()->user();
        $vendedor = $cliente->vendedores()->firstOrFail();
        return response()->json($vendedor, 200);
    }
}
