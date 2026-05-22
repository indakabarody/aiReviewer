<?php
/**
 * @file classes/TextExtractor.inc.php
 *
 * @class TextExtractor
 * @brief Utility class to extract text from PDF, DOCX, and DOC files.
 */

class TextExtractor {

    /**
     * Extracts text from a file based on its extension.
     *
     * @param string $filePath Absolute path to the file
     * @param string $extension The file extension (pdf, docx, doc)
     * @return string|null The extracted text or null on failure
     */
    public static function extractText($filePath, $extension) {
        $extension = strtolower($extension);
        
        if (!file_exists($filePath)) {
            error_log("TextExtractor: File does not exist at path: " . $filePath);
            return null;
        }

        switch ($extension) {
            case 'pdf':
                return self::extractPdfText($filePath);
            case 'docx':
                return self::extractDocxText($filePath);
            case 'doc':
                return self::extractDocText($filePath);
            default:
                error_log("TextExtractor: Unsupported file extension: " . $extension);
                return null;
        }
    }

    /**
     * Extracts text from a PDF file using smalot/pdfparser.
     *
     * @param string $filePath
     * @return string|null
     */
    public static function extractPdfText($filePath) {
        if (!class_exists('\Smalot\PdfParser\Parser')) {
            error_log("TextExtractor: Smalot\\PdfParser\\Parser class not found. Did you run 'composer install' in the plugin directory?");
            return null;
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            return trim($text);
        } catch (\Exception $e) {
            error_log("TextExtractor: Error parsing PDF - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extracts text from a DOCX file natively using ZipArchive.
     *
     * @param string $filePath
     * @return string|null
     */
    public static function extractDocxText($filePath) {
        if (!class_exists('ZipArchive')) {
            error_log("TextExtractor: ZipArchive PHP extension is missing.");
            return null;
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) === true) {
            // Document text is stored in word/document.xml inside the zip archive
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                
                // Strip XML tags to get pure text. 
                // We replace </w:p> with a newline to preserve paragraph breaks.
                $data = str_replace('</w:p>', "\n", $data);
                $text = strip_tags($data);
                
                return trim($text);
            }
            $zip->close();
            error_log("TextExtractor: 'word/document.xml' not found in DOCX.");
            return null;
        } else {
            error_log("TextExtractor: Failed to open DOCX file as ZipArchive.");
            return null;
        }
    }

    /**
     * Attempts to extract text from a legacy DOC file natively.
     * This is a basic fallback and might yield garbled text for complex documents.
     *
     * @param string $filePath
     * @return string|null
     */
    public static function extractDocText($filePath) {
        $fileHandle = fopen($filePath, "r");
        if (!$fileHandle) {
            error_log("TextExtractor: Cannot open DOC file for reading.");
            return null;
        }

        $stream = stream_get_contents($fileHandle);
        fclose($fileHandle);

        // A very crude way to extract printable strings from a binary DOC file.
        // Modern DOC files are often actually just rich text or OLE containers.
        // This regex looks for sequences of printable characters.
        $text = '';
        if (preg_match_all('/[\x09\x0A\x0D\x20-\x7E]{3,}/', $stream, $matches)) {
            $text = implode(" ", $matches[0]);
        }
        
        // Clean up excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
