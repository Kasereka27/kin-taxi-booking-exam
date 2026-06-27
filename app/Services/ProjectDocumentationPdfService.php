<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ProjectDocumentationPdfService
{
    public const DEFAULT_FILENAME = 'KinTaxiBooking-Documentation.pdf';

    public function outputPath(?string $directory = null): string
    {
        $directory ??= base_path('docs');

        return $directory.DIRECTORY_SEPARATOR.self::DEFAULT_FILENAME;
    }

    public function generate(?string $outputPath = null): string
    {
        $outputPath ??= $this->outputPath();

        $directory = dirname($outputPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf = Pdf::loadView('pdf.project-documentation', $this->viewData())
            ->setPaper('a4');

        file_put_contents($outputPath, $pdf->output());

        return $outputPath;
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        return [
            'generatedAt' => now()->format('d/m/Y H:i'),
            'appName' => config('app.name', 'KinTaxiBooking'),
            'studentName' => 'KASEREKA SALAMBUNGU SABIN',
            'institution' => 'Faculté des Sciences Informatiques (FASI) — Université Protestant du Congo (UPC)',
            'promotion' => 'L3 · Année académique 2025–2026',
            'githubUrl' => 'https://github.com/Kasereka27/kin-taxi-booking-exam',
        ];
    }
}
