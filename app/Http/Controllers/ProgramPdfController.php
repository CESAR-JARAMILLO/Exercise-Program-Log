<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ProgramPdfController extends Controller
{
    public function preview(Program $program)
    {
        // Ensure user owns this program
        abort_unless($program->user_id === Auth::id(), 403);

        // Load all relationships
        $program->load([
            'weeks.days.exercises' => function ($query) {
                $query->orderBy('order');
            }
        ]);

        return view('programs.preview', compact('program'));
    }

    public function export(Program $program)
    {
        // Ensure user owns this program
        abort_unless($program->user_id === Auth::id(), 403);

        // Load all relationships
        $program->load([
            'weeks.days.exercises' => function ($query) {
                $query->orderBy('order');
            }
        ]);

        // Generate PDF
        $pdf = Pdf::loadView('programs.preview', compact('program'))
            ->setPaper('a4', 'portrait')
            ->setOption('enable-local-file-access', true);

        $filename = str_replace(' ', '_', $program->name) . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}

