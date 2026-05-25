<?php
namespace App\Http\Controllers;

use App\Http\Requests\ExtractDocumentRequest;
use App\Services\DocumentSchemaService;
use App\Services\VisionExtractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtractionController extends Controller
{
    public function __construct(
        private VisionExtractionService $visionService,
        private DocumentSchemaService   $schemaService,
    ) {}

    public function carteGrise(ExtractDocumentRequest $request): JsonResponse
    {
        return $this->process($request, 'carte_grise');
    }

    public function carteVerte(ExtractDocumentRequest $request): JsonResponse
    {
        return $this->process($request, 'carte_verte');
    }

    public function permis(ExtractDocumentRequest $request): JsonResponse
    {
        return $this->process($request, 'permis_conduire');
    }

    public function constat(ExtractDocumentRequest $request): JsonResponse
    {
        return $this->process($request, 'constat_amiable');
    }


    public function auto(ExtractDocumentRequest $request): JsonResponse
    {
        $docType = $request->input('doc_type');
        if (!$docType) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Le champ doc_type est requis pour cet endpoint.',
            ], 422);
        }
        return $this->process($request, $docType);
    }

    private function process(Request $request, string $docType): JsonResponse
    {
        try {
            $file     = $request->file('file');
            $mimeType = $file->getMimeType();

            // Gestion des PDF multi-pages : prendre la première page seulement
            // (pour les PDFs, idéalement convertir avec Imagick côté PHP)
            if ($mimeType === 'application/pdf') {
                $base64 = $this->pdfFirstPageToBase64($file->getRealPath());
                $mimeType = 'image/jpeg';
            } else {
                $base64 = base64_encode(file_get_contents($file->getRealPath()));
            }

            $prompt = $this->schemaService->getPrompt($docType);
            $result = $this->visionService->extract($base64, $mimeType, $prompt);

            return response()->json($result, 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function pdfFirstPageToBase64(string $pdfPath): string
    {
        // Nécessite Imagick (apt install php-imagick)
        $imagick = new \Imagick();
        $imagick->setResolution(200, 200); // 200 DPI pour une bonne lisibilité
        $imagick->readImage($pdfPath . '[0]'); // [0] = première page seulement
        $imagick->setImageFormat('jpeg');
        $imagick->setImageCompressionQuality(90);
        $blob = $imagick->getImageBlob();
        $imagick->destroy();
        return base64_encode($blob);
    }
}