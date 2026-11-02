<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TechnicalSpecificationController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        return response()->download(
            base_path('docs/TECHNICAL_SPECIFICATION.md'),
            'mercibuddy-technical-specification.md',
            ['Content-Type' => 'text/markdown; charset=UTF-8'],
        );
    }
}
