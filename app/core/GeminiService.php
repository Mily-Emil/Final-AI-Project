<?php
/**
 * Gemini Vision AI Service & Local Heuristic Fallback Engine
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * 
 * Mengimplementasikan:
 * 1. Multimodal Vision-to-Nutrition via Google Gemini API (gemini-2.5-flash)
 * 2. Kalkulasi Distribusi Makronutrien Atwater (1g C=4kkal, 1g P=4kkal, 1g F=9kkal) -> Tepat 100%
 * 3. Ambang Batas 70% Confidence Human-in-the-Loop (HITL)
 * 4. Local Heuristic Nutrition Parser Fallback jika API key belum diatur atau offline
 */

class GeminiService {

    private $apiKey;
    private $model;
    private $threshold;

    public function __construct() {
        $this->apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : (getenv('GEMINI_API_KEY') ?: '');
        $this->model  = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-2.5-flash';
        $this->threshold = defined('AI_CONFIDENCE_THRESHOLD') ? (float)AI_CONFIDENCE_THRESHOLD : 70.0;
    }

    /**
     * Analisis gambar makanan dari path absolut
     * Mengembalikan array terstruktur nutrisi, persentase makro, confidence score, dan status HITL
     */
    public function analyzeFoodImage($imageAbsolutePath, $mimeType = 'image/jpeg') {
        if (!file_exists($imageAbsolutePath)) {
            return $this->buildErrorResult("Berkas gambar tidak ditemukan di server.");
        }

        // Jika API Key dikonfigurasi, coba panggil Google Gemini Vision API
        if (!empty($this->apiKey) && trim($this->apiKey) !== 'YOUR_API_KEY_HERE') {
            try {
                $geminiResult = $this->callGeminiApi($imageAbsolutePath, $mimeType);
                if ($geminiResult && !empty($geminiResult['dish_name'])) {
                    return $this->formatAndValidateResponse($geminiResult, 'gemini_vision_api');
                }
            } catch (Throwable $e) {
                // Log error dan lanjutkan ke Fallback lokal
                error_log("Gemini Vision API Error: " . $e->getMessage());
            }
        }

        // Mekanisme Fallback: Local Heuristic Nutrition Parser (< 70% Confidence -> Masuk Antrean HITL)
        return $this->localHeuristicParser($imageAbsolutePath);
    }

    /**
     * Panggilan ke Google Gemini Multimodal REST API
     */
    private function callGeminiApi($imagePath, $mimeType) {
        $imageData = file_get_contents($imagePath);
        if (!$imageData) {
            return null;
        }

        $base64Image = base64_encode($imageData);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($this->model) . ":generateContent?key=" . urlencode($this->apiKey);

        $promptText = "Anda adalah Pakar Analisis Nutrisi Klinis & Ahli Gizi untuk Universitas Klabat (UNKLAB), Manado, Sulawesi Utara.
Analisis foto makanan ini secara mendalam dengan fokus pada hidangan Indonesia dan masakan khas Minahasa/Sulawesi Utara (seperti Tinutuan, Cakalang Fufu, Ayam Woku, Ikan Kuah Asam, Tahu, Tempe, Perkedel Jagung/Milu, Sayur Kangkung, Pisang Goroho, Nasi Putih/Merah).

Instruksi Analisis:
1. Kenali komponen hidangan dalam foto dan estimasikan berat porsi visualnya (dalam gram).
2. Hitung energi makronutrien dengan faktor Atwater:
   - 1g Karbohidrat = 4 kkal
   - 1g Protein = 4 kkal
   - 1g Lemak = 9 kkal
   Pastikan carbohydrate_pct + protein_pct + fat_pct tepat 100%.
3. Evaluasi kualitas foto dan kejelasan visual lauk/makanan. Berikan confidence_score (0.0 sampai 100.0).
   - Berikan nilai >= 70.0 jika foto jelas, pencahayaan baik, porsi dan lauk teridentifikasi tegas.
   - Berikan nilai < 70.0 (misalnya 45.0 - 65.0) jika foto buram, gelap, makanan tertutup kuah tebal, atau lauk ambigu/ragu-ragu.
4. Jika confidence_score < 70.0, isi 'ai_uncertainty_reason' dengan penjelasan mengapa Anda ragu.

KEMBALIKAN HANYA OBJEK JSON VALID DENGAN SKEMA PERSIS BERIKUT (TANPA MARKDOWN, TANPA TEKS LAIN):
{
  \"dish_name\": \"Nama Hidangan Lengkap\",
  \"food_components\": [
    {
      \"name\": \"Nama Komponen Makanan\",
      \"portion_g\": 150,
      \"calories\": 195,
      \"carbs_g\": 43.0,
      \"protein_g\": 3.8,
      \"fat_g\": 0.5,
      \"fiber_g\": 0.6
    }
  ],
  \"total_weight_g\": 350.0,
  \"total_calories\": 450.0,
  \"carbohydrate_g\": 55.0,
  \"protein_g\": 28.0,
  \"fat_g\": 12.0,
  \"fiber_g\": 4.5,
  \"carbohydrate_pct\": 50.0,
  \"protein_pct\": 25.5,
  \"fat_pct\": 24.5,
  \"confidence_score\": 85.0,
  \"ai_uncertainty_reason\": \"\",
  \"nutrition_notes\": \"Ringkasan gizi singkat dalam 1-2 kalimat.\"
}";

        $requestPayload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $promptText],
                        [
                            "inline_data" => [
                                "mime_type" => $mimeType,
                                "data" => $base64Image
                            ]
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
                "responseMimeType" => "application/json",
                "temperature" => 0.2
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Memastikan kompatibilitas lingkungan lokal XAMPP

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200 || empty($response)) {
            error_log("Gemini API HTTP $httpCode: $curlError | Body: $response");
            return null;
        }

        $resDecoded = json_decode($response, true);
        $candidateText = $resDecoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (empty($candidateText)) {
            return null;
        }

        // Bersihkan formatting markdown jika ada ```json
        $candidateText = trim($candidateText);
        $candidateText = preg_replace('/^```(?:json)?\s*/i', '', $candidateText);
        $candidateText = preg_replace('/\s*```$/i', '', $candidateText);

        $parsedJson = json_decode($candidateText, true);
        return is_array($parsedJson) ? $parsedJson : null;
    }

    /**
     * Local Heuristic Nutrition Parser (Aturan Fallback Sesuai PRD)
     * Dijalankan ketika GEMINI_API_KEY belum diatur atau koneksi offline.
     * Otomatis menetapkan confidence_score < 70% agar hasil masuk ke antrean HITL Administrator.
     */
    public function localHeuristicParser($imagePath) {
        $fileSize = file_exists($imagePath) ? filesize($imagePath) : 0;
        $imageHash = md5_file($imagePath) ?: md5((string)time());
        $hashNum = hexdec(substr($imageHash, 0, 4));

        // Pilihan template gizi lokal khas UNKLAB & Minahasa
        $catalog = [
            [
                'dish_name' => 'Tinutuan Komplit (Bubur Manado) + Perkedel Jagung',
                'components' => [
                    ['name' => 'Bubur Beras Labu & Kangkung', 'portion_g' => 280, 'calories' => 190, 'carbs_g' => 38.0, 'protein_g' => 4.5, 'fat_g' => 2.2, 'fiber_g' => 4.8],
                    ['name' => 'Perkedel Jagung Manado (Milu)', 'portion_g' => 75, 'calories' => 155, 'carbs_g' => 17.0, 'protein_g' => 3.8, 'fat_g' => 8.0, 'fiber_g' => 1.8],
                    ['name' => 'Sambal Roa / Dabu-dabu', 'portion_g' => 25, 'calories' => 45, 'carbs_g' => 3.0, 'protein_g' => 2.2, 'fat_g' => 2.8, 'fiber_g' => 0.5]
                ],
                'notes' => 'Kaya serat dan antioksidan alami dari labu kuning serta sayuran hijau khas Minahasa.'
            ],
            [
                'dish_name' => 'Nasi Putih + Cakalang Fufu Suwir + Sayur Kangkung Cah',
                'components' => [
                    ['name' => 'Nasi Putih Pulen', 'portion_g' => 150, 'calories' => 195, 'carbs_g' => 43.0, 'protein_g' => 3.8, 'fat_g' => 0.5, 'fiber_g' => 0.6],
                    ['name' => 'Cakalang Fufu Suwir Pedas', 'portion_g' => 110, 'calories' => 205, 'carbs_g' => 3.5, 'protein_g' => 31.0, 'fat_g' => 7.2, 'fiber_g' => 0.9],
                    ['name' => 'Sayur Kangkung Cah Bawang', 'portion_g' => 90, 'calories' => 65, 'carbs_g' => 3.8, 'protein_g' => 2.5, 'fat_g' => 4.0, 'fiber_g' => 2.2]
                ],
                'notes' => 'Tinggi protein berkualitas dan asam lemak omega-3 dari ikan cakalang asap khas Minahasa.'
            ],
            [
                'dish_name' => 'Nasi Putih + Ayam Woku Belanga + Tahu Goreng',
                'components' => [
                    ['name' => 'Nasi Putih Pulen', 'portion_g' => 150, 'calories' => 195, 'carbs_g' => 43.0, 'protein_g' => 3.8, 'fat_g' => 0.5, 'fiber_g' => 0.6],
                    ['name' => 'Ayam Woku Belanga Kemangi', 'portion_g' => 140, 'calories' => 245, 'carbs_g' => 4.2, 'protein_g' => 29.5, 'fat_g' => 11.8, 'fiber_g' => 1.1],
                    ['name' => 'Tahu Goreng Kuning', 'portion_g' => 80, 'calories' => 95, 'carbs_g' => 2.0, 'protein_g' => 8.5, 'fat_g' => 6.0, 'fiber_g' => 1.2]
                ],
                'notes' => 'Kombinasi seimbang protein hewani dan nabati dengan aroma kemangi dan kunyit anti-inflamasi.'
            ],
            [
                'dish_name' => 'Nasi Merah + Ikan Kuah Asam Manado + Tempe Bacem',
                'components' => [
                    ['name' => 'Nasi Merah Organik', 'portion_g' => 150, 'calories' => 165, 'carbs_g' => 34.5, 'protein_g' => 4.2, 'fat_g' => 1.2, 'fiber_g' => 2.8],
                    ['name' => 'Ikan Kuah Asam Segar', 'portion_g' => 160, 'calories' => 145, 'carbs_g' => 2.8, 'protein_g' => 24.5, 'fat_g' => 3.5, 'fiber_g' => 0.4],
                    ['name' => 'Tempe Orek Manis Gurih', 'portion_g' => 70, 'calories' => 135, 'carbs_g' => 10.5, 'protein_g' => 9.8, 'fat_g' => 6.8, 'fiber_g' => 2.2]
                ],
                'notes' => 'Pilihan rendah lemak dan ramah glikemik berkat kuah bening asam tomat dan serat beras merah.'
            ]
        ];

        // Pilih item katalog deterministik berbasis hash gambar
        $selected = $catalog[$hashNum % count($catalog)];

        // Akumulasi gizi
        $totalWeight = 0;
        $totalCalories = 0;
        $totalCarbs = 0;
        $totalProtein = 0;
        $totalFat = 0;
        $totalFiber = 0;

        foreach ($selected['components'] as $c) {
            $totalWeight += $c['portion_g'];
            $totalCalories += $c['calories'];
            $totalCarbs += $c['carbs_g'];
            $totalProtein += $c['protein_g'];
            $totalFat += $c['fat_g'];
            $totalFiber += $c['fiber_g'];
        }

        // Tetapkan confidence_score di bawah 70% sesuai aturan PRD (misal 54.0% - 63.5%)
        $simulatedConfidence = 52.0 + ($hashNum % 130) / 10.0;
        if ($simulatedConfidence >= 70.0) {
            $simulatedConfidence = 64.5;
        }

        $rawResult = [
            'dish_name' => $selected['dish_name'],
            'food_components' => $selected['components'],
            'total_weight_g' => round($totalWeight, 1),
            'total_calories' => round($totalCalories, 1),
            'carbohydrate_g' => round($totalCarbs, 1),
            'protein_g' => round($totalProtein, 1),
            'fat_g' => round($totalFat, 1),
            'fiber_g' => round($totalFiber, 1),
            'confidence_score' => round($simulatedConfidence, 1),
            'ai_uncertainty_reason' => 'Mode Fallback Heuristik Lokal aktif (GEMINI_API_KEY belum dikonfigurasi). Citra masakan diteruskan ke antrean Administrator untuk verifikasi porsi dan kalibrasi gizi (Human-in-the-Loop).',
            'nutrition_notes' => $selected['notes']
        ];

        return $this->formatAndValidateResponse($rawResult, 'local_heuristic_fallback');
    }

    /**
     * Hitung Atwater Makronutrien dan Validasikan Status HITL
     */
    private function formatAndValidateResponse(array $data, $engineSource = 'gemini_vision_api') {
        $carbsG   = max(0, (float)($data['carbohydrate_g'] ?? 0));
        $proteinG = max(0, (float)($data['protein_g'] ?? 0));
        $fatG     = max(0, (float)($data['fat_g'] ?? 0));
        $fiberG   = max(0, (float)($data['fiber_g'] ?? 0));

        // Kalkulasi Distribusi Energi Makronutrien berdasarkan Faktor Atwater:
        // 1g C = 4 kkal, 1g P = 4 kkal, 1g F = 9 kkal
        $cKkal = $carbsG * 4.0;
        $pKkal = $proteinG * 4.0;
        $fKkal = $fatG * 9.0;
        $macroEnergyTotal = $cKkal + $pKkal + $fKkal;

        if ($macroEnergyTotal > 0) {
            $cPct = round(($cKkal / $macroEnergyTotal) * 100, 1);
            $pPct = round(($pKkal / $macroEnergyTotal) * 100, 1);
            // Pastikan total persentase tepat 100%
            $fPct = round(100.0 - ($cPct + $pPct), 1);
        } else {
            $cPct = 0.0;
            $pPct = 0.0;
            $fPct = 0.0;
        }

        $totalCalories = round($macroEnergyTotal > 0 ? $macroEnergyTotal : (float)($data['total_calories'] ?? 0), 1);
        $totalWeight = max(0, (float)($data['total_weight_g'] ?? 0));

        $confidenceScore = (float)($data['confidence_score'] ?? 50.0);
        $confidenceScore = max(0.0, min(100.0, $confidenceScore));

        // Logika Percabangan Keputusan Ambang Batas 70% (HITL)
        if ($confidenceScore >= $this->threshold) {
            $status = 'auto_approved';
            $uncertaintyReason = null;
        } else {
            $status = 'pending_admin_review';
            $uncertaintyReason = !empty($data['ai_uncertainty_reason']) 
                ? $data['ai_uncertainty_reason'] 
                : 'Tingkat keyakinan AI di bawah 70.0% (makanan tertutup kuah, pencahayaan kurang, atau komponen ambigu). Memerlukan kalibrasi Administrator.';
        }

        $components = $data['food_components'] ?? [];
        if (!is_array($components)) {
            $components = [];
        }

        return [
            'success' => true,
            'engine' => $engineSource,
            'dish_name' => !empty($data['dish_name']) ? trim($data['dish_name']) : 'Hidangan Kampus UNKLAB',
            'food_components' => $components,
            'total_calories' => $totalCalories,
            'total_weight_g' => $totalWeight,
            'carbohydrate_g' => $carbsG,
            'protein_g' => $proteinG,
            'fat_g' => $fatG,
            'fiber_g' => $fiberG,
            'carbohydrate_pct' => $cPct,
            'protein_pct' => $pPct,
            'fat_pct' => $fPct,
            'confidence_score' => $confidenceScore,
            'status' => $status,
            'ai_uncertainty_reason' => $uncertaintyReason,
            'nutrition_notes' => $data['nutrition_notes'] ?? '',
            'threshold' => $this->threshold
        ];
    }

    private function buildErrorResult($message) {
        return [
            'success' => false,
            'message' => $message,
            'dish_name' => 'Gagal Memproses Gambar',
            'food_components' => [],
            'total_calories' => 0,
            'total_weight_g' => 0,
            'carbohydrate_g' => 0,
            'protein_g' => 0,
            'fat_g' => 0,
            'fiber_g' => 0,
            'carbohydrate_pct' => 0,
            'protein_pct' => 0,
            'fat_pct' => 0,
            'confidence_score' => 0,
            'status' => 'pending_admin_review',
            'ai_uncertainty_reason' => $message,
            'nutrition_notes' => ''
        ];
    }
}
