<?php

namespace App\Console\Commands;

use App\Services\ProjectDocumentationPdfService;
use Illuminate\Console\Command;

class GenerateDocumentationPdfCommand extends Command
{
    protected $signature = 'docs:generate-pdf {--output= : Chemin de sortie du fichier PDF}';

    protected $description = 'Génère le PDF de documentation du projet dans docs/';

    public function handle(ProjectDocumentationPdfService $service): int
    {
        $output = $this->option('output') ?: $service->outputPath();

        $path = $service->generate($output);

        $this->components->info('Documentation PDF générée : '.$path);

        return self::SUCCESS;
    }
}
