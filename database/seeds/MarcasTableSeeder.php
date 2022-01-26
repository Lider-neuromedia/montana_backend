<?php

use App\Entities\Marca;
use Illuminate\Database\Seeder;

class MarcasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $marcas = [
            ["01", "ATHLETIC"],
            ["02", "VITEK GUAYO"],
            ["03", "VITEK TORRETIN"],
            ["04", "VITEK"],
            ["05", "VARIOS"],
            ["06", "NEWSTEP"],
            ["07", "CHANCLAS"],
            ["10", "CALZADO USADO"],
            ["11", "VITEK TORRETIN"],
            ["13", "VITEK"],
            ["14", "CALZADO NUEVO IMPERFECTO Y TROCADOS"],
            ["16", "CALZADO ESCOLAR"],
            ["17", "DEVOLUCIONES LA 14 ATH VITEK Y CIMMS"],
            ["18", "VITEK GOMA"],
            ["19", "ATHLETIC PATIN"],
            ["26", "TAPABOCAS"],
            ["27", "CIMMS"],
            ["28", "ROPA DEPORTIVA"],
            ["DF", "SEPARADOS DAFITI"],
            ["EV", "SEPARADOS EVACOL"],
            ["FL", "SEPARADOS FLAMINGO"],
            ["KG", "SEPARADOS KAGELO"],
            ["MK", "SEPARADOS MARKETING"],
            ["SV", "SEPARADOS VARIOS"],
        ];

        foreach ($marcas as $m) {
            $existe = Marca::where('nombre_marca', $m[1])->exists();

            if (!$existe) {
                Marca::create(["nombre_marca" => $m[1]]);
            }
        }
    }
}
