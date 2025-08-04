<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\NoAtendidos;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class NoAtendidosController extends Controller
{
    public function pdfNoAtendidos()
    {
        // Aquí puedes implementar la lógica para generar el PDF de clientes no atendidos
        // Por ejemplo, podrías obtener los datos de NoAtendidos y pasarlos a una vista PDF
        $noAtendidos = NoAtendidos::all();
        // Generar el PDF con dompdf
        $pdf = Pdf::loadView('administrador.pdf.pdf_clientes_no_atendidos', compact('noAtendidos'));
        $pdf->setPaper('letter', 'horizontal');
        return $pdf->stream('caralogo.pdf');
    }

    public function subsanarObservaciones()
    {
        NoAtendidos::truncate();
        return response()->json([
            'message' => 'Observaciones de clientes no atendidos subsanadas correctamente.',
            'status' => 'success'
        ]);
    }
}
