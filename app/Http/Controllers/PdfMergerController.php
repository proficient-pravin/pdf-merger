<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class PdfMergerController extends Controller
{
    public function mergePDFs(Request $request)
    {

        //Here you have to put pdf urls you want to merge
        $pdfUrlToMerge = [
            'https://acm-drive.s3.ap-south-1.amazonaws.com/1680606918_do_sample.pdf',
            'https://acm-drive.s3.ap-south-1.amazonaws.com/167837087498585.pdf',
        ];

        $mergedPdfContent = $this->mergePdfUsingUrl($pdfUrlToMerge);

        // // Store in S3 the merged PDF
        // $mergedS3FilePath = "documents/shipping_bills/merged_pdfs/".Str::random(20) . '_' . time() . ".pdf";
        // Storage::disk('s3')->put($mergedS3FilePath, $mergedPdfContent);

        // //Get merged pdf s3 temporary url
        // $temporaryUrl = Storage::disk('s3')->temporaryUrl($mergedS3FilePath, now()->addHours(5));

        // if you want to download without saving anywhere
        return response($mergedPdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="merged-docs.pdf"',
        ]);
    }

    private function mergePdfUsingUrl($pdfUrlToMerge = [])
    {
        // Create an instance of Fpdi
        $pdf = new Fpdi();
        foreach ($pdfUrlToMerge as $url) {
            $firstPdfContent = file_get_contents($url);
            $pageCount = $pdf->setSourceFile(StreamReader::createByString($firstPdfContent));
            for ($page = 1; $page <= $pageCount; $page++) {
                $pdf->AddPage();
                $template = $pdf->importPage($page);
                list($width, $height) = $pdf->getTemplateSize($template);
                $pdf->useTemplate($template, 0, 0, $width, $height);
            }
        }
        return $pdf->Output('S');
    }


    public function checkTodayMenu(){

        



        // $servername = "89.117.157.221";
        // $username = "u956083354_resturent";
        // $password = "Resturent@1234 password";
        // $dbname = "u956083354_resturent";
        
        // // Create connection
        // $conn = DB::connection()->getPdo();
        
        // Check connection
        // if (!$conn) {
        //     die("Connection failed");
        // }
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            dump($table);
        }
        
        dd("Connected successfully");


        $foodSettings = [
            0 => ['Tuesday', 'Wednesday'],
            1 => ['Monday', 'Tuesday', 'Wednesday'],
            2 => ['Tuesday', 'Wednesday'],
            4 => ['Wednesday'],
        ];
        $canShowFoodToday  = $this->canShowFoodToday($foodSettings);
    }

    private function canShowFoodToday($settings) {
        $currentWeek = (integer) date('W');
        if(isset($settings[$currentWeek])) {
            $currentDay = date('l'); 
            return in_array($currentDay,$settings[$currentWeek - 1]);
        }
        return false;
    }

}
