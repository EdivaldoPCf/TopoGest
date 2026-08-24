<?php

namespace App\Services;

use App\Models\Base;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Exception;

class BaseProcessorService
{
    public function __construct(private GeoGeometryService $geo)
    {
    }

    /**
     * Processes a PPP Zip file and registers a new Base.
     * 
     * @param string $zipPath The absolute path to the uploaded/synced zip file.
     * @return bool True if successful, False otherwise.
     */
    public function processPppZip($zipPath)
    {
        try {
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== TRUE) {
                throw new Exception("Cannot open zip file: " . $zipPath);
            }

            $pdfContent = null;
            $kmlContent = null;
            $pdfFilename = null;
            $kmlFilename = null;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if ($ext === 'pdf') {
                    $pdfContent = $zip->getFromIndex($i);
                    $pdfFilename = basename($filename);
                } elseif ($ext === 'kml') {
                    $kmlContent = $zip->getFromIndex($i);
                    $kmlFilename = basename($filename);
                }
            }
            $zip->close();

            if (!$pdfContent || !$kmlContent) {
                Log::warning("BaseProcessorService: PDF or KML not found in ZIP ({$zipPath}).");
                return false;
            }

            // Extract coordinates
            $lat = null;
            $lng = null;
            $norte = null;
            $este = null;

            // 1. Parse Lat/Lng from KML
            $dom = new \DOMDocument();
            @$dom->loadXML($kmlContent);
            $coordsTags = $dom->getElementsByTagName('coordinates');
            if ($coordsTags->length > 0) {
                $partes = explode(',', trim($coordsTags->item(0)->nodeValue));
                if (count($partes) >= 2) {
                    $lng = (float) trim($partes[0]);
                    $lat = (float) trim($partes[1]);
                }
            }

            // 2. Parse PDF for UTM (Easting/Northing)
            // Try to extract UTM coordinates from PDF if needed.
            // If we can't extract, we'll convert Lat/Lng to UTM (Zone 20, South) as a fallback.
            $tmpPdf = tempnam(sys_get_temp_dir(), 'ppp_pdf');
            file_put_contents($tmpPdf, $pdfContent);
            
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($tmpPdf);
                $text = $pdf->getText();
                
                // Typical format in PPP PDF:
                // Latitude / Longitude
                // N: 8990789.066 m E: 179966.321 m
                if (preg_match('/N:\s*([\d\.\,]+)\s*m.*E:\s*([\d\.\,]+)\s*m/is', $text, $matches)) {
                    $norte = round((float) str_replace(',', '.', str_replace('.', '', $matches[1])), 3); // Adjust if thousands sep is used
                    $este = round((float) str_replace(',', '.', str_replace('.', '', $matches[2])), 3);
                }
            } catch (\Throwable $e) {
                Log::warning("BaseProcessorService: Failed to parse PDF text - " . $e->getMessage());
            }
            @unlink($tmpPdf);

            // Fallback: Convert Lat/Lng to UTM if not found in PDF
            if ((!$norte || !$este) && $lat && $lng) {
                $utmZone = $this->geo->zonaPorLongitude($lng);
                $utm = $this->geo->latLngToUtm($lat, $lng, $utmZone);
                $norte = round($utm['northing'], 3);
                $este = round($utm['easting'], 3);
            }

            if (!$norte || !$este) {
                Log::warning("BaseProcessorService: Could not extract or calculate UTM coordinates from {$zipPath}");
                return false;
            }

            // Next Base Name
            $nextNumber = 1;
            $bases = Base::all();
            foreach ($bases as $base) {
                if (preg_match('/(?:BASE\s*)(\d+)/i', $base->nome, $matches)) {
                    $num = (int)$matches[1];
                    if ($num >= $nextNumber) {
                        $nextNumber = $num + 1;
                    }
                }
            }
            $baseName = "BASE " . $nextNumber;

            // Repackage ZIP
            $cleanZipName = uniqid() . '_base.zip';
            $cleanZipPath = storage_path('app/public/bases_zip/' . $cleanZipName);
            
            if (!is_dir(dirname($cleanZipPath))) {
                mkdir(dirname($cleanZipPath), 0755, true);
            }

            $newZip = new ZipArchive();
            if ($newZip->open($cleanZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $newZip->addFromString($pdfFilename, $pdfContent);
                $newZip->addFromString($kmlFilename, $kmlContent);
                $newZip->close();
            } else {
                throw new Exception("Could not create clean zip file at " . $cleanZipPath);
            }

            $novoCaminho = 'bases_zip/' . $cleanZipName;

            DB::beginTransaction();
            try {
                // Delete existing bases within 50 meters
                $this->deleteNearbyBases($norte, $este, 50);

                // Create Base
                Base::create([
                    'nome' => $baseName,
                    'norte' => $norte,
                    'este' => $este,
                    'norte_original' => $norte,
                    'este_original' => $este,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'arquivo_zip' => $novoCaminho
                ]);
                DB::commit();
                return true;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("BaseProcessorService: Error saving base - " . $e->getMessage());
                @unlink($cleanZipPath);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("BaseProcessorService: Error processing PPP Zip: " . $e->getMessage());
            return false;
        }
    }

    private function deleteNearbyBases($norte, $este, $radius)
    {
        $nearbyBases = Base::select('*')
            ->selectRaw("SQRT(POW(norte - ?, 2) + POW(este - ?, 2)) AS distancia", [$norte, $este])
            ->having('distancia', '<', $radius)
            ->get();

        foreach ($nearbyBases as $base) {
            Log::info("BaseProcessorService: Deleting nearby base {$base->nome} (dist: {$base->distancia})");
            if ($base->arquivo_zip) {
                Storage::disk('public')->delete($base->arquivo_zip);
            }
            $base->delete();
        }
    }
}
