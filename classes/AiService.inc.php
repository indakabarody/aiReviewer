<?php
/**
 * @file classes/AiService.inc.php
 *
 * @class AiService
 * @brief Service class to handle communication with AI APIs.
 */

class AiService {

    /**
     * Generates a review using the specified AI model.
     *
     * @param string $text The extracted manuscript text
     * @param string $model The chosen AI model (gemini, claude, gpt4o)
     * @param string $apiKey The API key
     * @return string|null The JSON encoded review or null on failure
     */
    public static function generateReview($text, $model, $apiKey, $responseLanguage = 'English', $customPrompt = '') {
        // Build the prompt
        $prompt = self::_buildPrompt($text, $responseLanguage, $customPrompt);

        if (strpos($model, 'gemini') === 0) {
            return self::_callGemini($prompt, $model, $apiKey);
        } elseif (strpos($model, 'claude') === 0) {
            return self::_callClaude($prompt, $model, $apiKey);
        } elseif (strpos($model, 'gpt') === 0 || strpos($model, 'o1') === 0 || strpos($model, 'o3') === 0) {
            return self::_callOpenAI($prompt, $model, $apiKey);
        } else {
            error_log("AiService: Unsupported model '{$model}'.");
            return null;
        }
    }

    /**
     * Constructs the academic review prompt.
     * 
     * @param string $text
     * @return string
     */
    private static function _buildPrompt($text, $responseLanguage = 'English', $customPrompt = '') {
        $prompt = "You are an expert academic reviewer. Please review the following manuscript text.\n\n";
        $prompt .= "CRITICAL INSTRUCTION: You MUST write your ENTIRE review in " . strtoupper($responseLanguage) . " language.\n\n";
        
        if (empty(trim($customPrompt))) {
            $prompt .= "Provide your review in the following structured format, formatting your evaluation points as paragraphs to serve as a reference for the author's revisions. Keep it concise but clear:\n\n";
        
        $prompt .= "**1. Judul Artikel:**\n";
        $prompt .= "Tinjau kesesuaian dan kecocokan judul dengan isi artikel secara keseluruhan. Apakah judul mencerminkan fokus penelitian? Apakah judul sudah jelas dan tidak ambigu? Pertimbangkan apakah judul ini cukup menarik dan relevan untuk audiens yang menjadi target artikel ini. Jika ada aspek yang perlu disesuaikan atau lebih diperjelas, sebutkan secara rinci. Jadikan point evaluasi dalam bentuk paragraf supaya dapat di jadikan acuan perbaikan author. Buatlah yang ringkas namun jelas.\n\n";

        $prompt .= "**2. Abstrak:**\n";
        $prompt .= "Tinjau kesesuaian abstrak artikel ilmiah berikut dengan pedoman penulisan jurnal yang mengharuskan abstrak ditulis dengan jelas, singkat, dan deskriptif, serta berdiri sendiri tanpa kutipan, gambar, atau persamaan matematika. Pastikan abstrak memberikan latar belakang masalah yang singkat (1-2 kalimat), tujuan penelitian yang jelas, metode yang dijelaskan secara ringkas, dan temuan utama yang disajikan dalam ringkasan singkat tanpa diskusi lebih lanjut. Evaluasi apakah abstrak mencakup kesimpulan yang singkat dan tepat, serta menggunakan bahasa yang akurat, jelas, dan bebas dari jargon teknis atau singkatan yang tidak umum. Abstrak juga harus sesuai dengan pedoman jurnal dalam hal akurasi, kejelasan, dan format. Buatlah dalam bentuk paragraf singkat dan point nya saja. Jadikan point evaluasi dalam bentuk paragraf supaya dapat di jadikan acuan perbaikan author. Buatlah yang ringkas namun jelas.\n\n";

        $prompt .= "**3. Pendahuluan (Introduction):**\n";
        $prompt .= "Evaluasi kualitas dan kejelasan pendahuluan artikel. Apakah penulis berhasil memaparkan latar belakang penelitian dengan baik? Apakah masalah yang dibahas relevan dengan penelitian yang dilakukan? Periksa apakah tujuan penelitian dijelaskan secara eksplisit dan apakah penulis menghubungkan penelitian ini dengan literatur yang ada secara efektif. Selain itu, evaluasi apakah ada kerangka teoritis yang mendasari penelitian ini dan apakah gap penelitian dijelaskan dengan jelas. Jika ada bagian yang lemah atau perlu diperbaiki, berikan saran perbaikannya. Buatlah dalam bentuk paragraf singkat dan point nya saja. Jadikan point evaluasi dalam bentuk paragraf supaya dapat di jadikan acuan perbaikan author.\n\n";

        $prompt .= "**4. Metode (Methods):**\n";
        $prompt .= "Periksa apakah penulis menjelaskan dengan rinci metode yang digunakan dalam penelitian ini. Apakah metode penelitian yang diterapkan sesuai dengan tujuan penelitian? Tinjau apakah ada justifikasi yang memadai untuk pemilihan metode dan apakah desain penelitian telah diuraikan dengan jelas. Pastikan bahwa instrumen yang digunakan, sampel atau partisipan (jika ada), serta prosedur eksperimen dijelaskan secara mendetail. Jika ada informasi yang kurang jelas atau perlu penjelasan lebih lanjut, beri saran yang tepat. Buatlah dalam bentuk paragraf singkat dan point nya saja. Jadikan point evaluasi dalam bentuk paragraf supaya dapat di jadikan acuan perbaikan author.\n\n";

        $prompt .= "**5. Hasil dan Diskusi (Results and Discussion):**\n";
        $prompt .= "Tinjau apakah hasil penelitian disajikan dengan jelas dan sesuai dengan tujuan penelitian. Apakah penulis menyajikan data dengan cara yang mudah dipahami? Pastikan bahwa grafik, tabel, atau gambar yang digunakan relevan dan memiliki keterangan yang memadai. Evaluasi apakah hasil disajikan secara objektif tanpa interpretasi yang berlebihan. Jika hasil belum cukup jelas atau terdapat kesalahan interpretasi, berikan umpan balik dan saran untuk memperbaikinya. Evaluasi bagaimana penulis membahas hasil penelitian dalam konteks literatur yang ada. Apakah penulis berhasil menghubungkan temuan penelitian dengan teori atau penelitian sebelumnya? Periksa apakah penulis memberikan interpretasi yang tepat dan tidak terlalu spekulatif. Apakah ada diskusi mengenai keterbatasan penelitian dan arah penelitian selanjutnya? Jika ada bagian yang perlu diperkuat atau ada pernyataan yang tidak didukung oleh data, sebutkan dengan jelas dan jadikan point evaluasi dalam bentuk paragraf supaya dapat di jadikan acuan perbaikan author. Buatlah yang ringkas namun jelas. Buatlah dalam bentuk paragraf singkat dan point nya saja. Jadikan point evaluasi dalam bentuk paragraf supaya dapat di jadikan acuan perbaikan author.\n\n";

        $prompt .= "**6. Kesimpulan (Conclusion):**\n";
        $prompt .= "Periksa apakah kesimpulan yang disampaikan sesuai dengan hasil dan diskusi yang ada dalam artikel. Apakah penulis merangkum temuan utama dengan jelas dan menyimpulkan dengan tepat? Evaluasi apakah kesimpulan memberikan kontribusi yang berarti untuk bidang studi yang dibahas. Jika kesimpulan tidak mencerminkan inti penelitian atau perlu dikembangkan lebih lanjut, beri masukan yang konstruktif. Buatlah dalam bentuk paragraf singkat dan point nya saja.\n\n";

        $prompt .= "**7. Referensi (Daftar Pustaka):**\n";
        $prompt .= "Tinjau kesesuaian dan kelengkapan referensi yang digunakan. Apakah penulis mengutip sumber-sumber terbaru dan relevan dengan topik penelitian? Pastikan bahwa referensi yang digunakan mendukung argumen yang dibuat dalam artikel dan sesuai dengan format yang diharuskan. Jika ada referensi yang kurang tepat atau perlu diperbarui. Jadikan point evaluasi dalam bentuk paragraf supaya dapat di jadikan acuan perbaikan author. Buatlah yang ringkas namun jelas. Buatlah dalam bentuk paragraf singkat dan point nya saja. Jadikan point evaluasi dalam bentuk paragraf supaya dapat di jadikan acuan perbaikan author.\n\n";
        
        $prompt .= "Format your response using Markdown.\n\n";
        } else {
            $prompt .= "Follow these specific guidelines for your review:\n\n";
            $prompt .= $customPrompt . "\n\n";
        }
        
        $prompt .= "=== MANUSCRIPT TEXT ===\n";
        
        // Truncate text if it's absurdly long, though modern LLMs handle 100k+ tokens.
        // Let's limit to 50,000 characters for safety in a standard plugin.
        if (strlen($text) > 50000) {
            $text = substr($text, 0, 50000) . "\n...[TRUNCATED]";
        }
        
        $prompt .= $text;
        
        return $prompt;
    }

    private static function _callGemini($prompt, $model, $apiKey) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        return self::_makeRequest($url, $data, []);
    }

    private static function _callClaude($prompt, $model, $apiKey) {
        $url = 'https://api.anthropic.com/v1/messages';
        $headers = [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'content-type: application/json'
        ];
        $data = [
            'model' => $model,
            'max_tokens' => 2000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        return self::_makeRequest($url, $data, $headers);
    }

    private static function _callOpenAI($prompt, $model, $apiKey) {
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];
        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];
        
        // o1 models don't support max_tokens (they use max_completion_tokens), 
        // so we omit max_tokens entirely to be safe across all OpenAI models.

        return self::_makeRequest($url, $data, $headers);
    }

    private static function _makeRequest($url, $data, $headers = []) {
        $ch = curl_init($url);
        
        if (empty($headers)) {
            $headers = ['Content-Type: application/json'];
        }
        
        $payload = json_encode($data);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        // SSL verification should ideally be true in production
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            error_log('AiService cURL error: ' . curl_error($ch));
            curl_close($ch);
            return "Error: Could not connect to AI Service.";
        }
        
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return self::_parseResponse($response);
        } else {
            error_log('AiService API error: ' . $response);
            return "API Error (Code: $httpCode): Unable to generate review. Please check your API key and model.";
        }
    }

    private static function _parseResponse($response) {
        $decoded = json_decode($response, true);
        
        // Attempt to parse Gemini
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            return $decoded['candidates'][0]['content']['parts'][0]['text'];
        }
        // Attempt to parse OpenAI
        if (isset($decoded['choices'][0]['message']['content'])) {
            return $decoded['choices'][0]['message']['content'];
        }
        // Attempt to parse Claude
        if (isset($decoded['content'][0]['text'])) {
            return $decoded['content'][0]['text'];
        }

        return "Error parsing AI response. Raw output: " . substr($response, 0, 500);
    }
}
