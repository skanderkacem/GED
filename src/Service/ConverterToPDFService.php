<?php


namespace App\Service;

class ConverterToPDFService 
{


    public function convert(string $filename, string $inputDirectory)
    {
        //source file path +filename
        $inputFile = '"' . $inputDirectory . 'original\\' . $filename. '"';
        //destination
        $inputDirectory = $inputDirectory . 'converted';
        // Define the command to run the script
        $command = "2pdf.exe -src $inputFile -dst \"$inputDirectory\" -pdf -options alerts:no ";

        // Execute the command as a command line process
        exec($command, $outputLines, $returnValue);
        // Check the return value to see if the command succeeded
   
    }


}
