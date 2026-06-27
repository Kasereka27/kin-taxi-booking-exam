<?php

use App\Services\ProjectDocumentationPdfService;

it('génère le PDF de documentation du projet', function () {
    $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kintaxi-docs-'.uniqid();
    mkdir($directory);

    $service = app(ProjectDocumentationPdfService::class);
    $path = $service->generate($directory.DIRECTORY_SEPARATOR.ProjectDocumentationPdfService::DEFAULT_FILENAME);

    expect($path)->toBeFile()
        ->and(filesize($path))->toBeGreaterThan(5000);

    unlink($path);
    rmdir($directory);
});

it('expose la commande artisan docs:generate-pdf', function () {
    $this->artisan('docs:generate-pdf', [
        '--output' => $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kintaxi-doc-'.uniqid().'.pdf',
    ])->assertSuccessful();

    expect($path)->toBeFile();

    unlink($path);
});
