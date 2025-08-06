<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class PdfWatermarkService
{
    public static function addWatermark($pdfContent, $watermarkImagePath, $opacity = 0.2)
    {
         // FPDI का नया इंस्टेंस बनाएं
        $pdf = new Fpdi();

        // PDF कंटेंट को पार्स करें
        $pdf->setSourceFile(StreamReader::createByString($pdfContent));
        $pageCount = $pdf->setSourceFile(StreamReader::createByString($pdfContent));

        // हर पेज को प्रोसेस करें
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // पेज इम्पोर्ट करें
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            // नया पेज एड करें (सही ओरिएंटेशन के साथ)
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);

            // मूल कंटेंट ड्रॉ करें
            $pdf->useTemplate($templateId);

            // वॉटरमार्क एड करें (सिर्फ अगर इमेज पाथ वैलिड है)
            if ($watermarkImagePath && file_exists($watermarkImagePath)) {
                // ऑपेसिटी सेट करें
                $pdf->SetAlpha($opacity);

                // इमेज साइज (पेज के 80% एरिया में)
                $imgWidth = $size['width'] * 0.8;
                $imgHeight = $size['height'] * 0.8;

                // सेंटर पोजीशन कैलकुलेट करें
                $x = ($size['width'] - $imgWidth) / 2;
                $y = ($size['height'] - $imgHeight) / 2;

                // वॉटरमार्क इमेज एड करें
                $pdf->Image(
                    $watermarkImagePath,
                    $x, $y, $imgWidth, $imgHeight,
                    '', '', '', false, 300, '', false, false, 0
                );

                // ऑपेसिटी रीसेट करें
                $pdf->SetAlpha(1);
            }
        }

        // मॉडिफाइड PDF रिटर्न करें
        return $pdf->Output('S');
    }
}



