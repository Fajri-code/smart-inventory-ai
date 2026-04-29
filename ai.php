<?php

function getAIInsight($nama, $jumlah, $status) {

    $apiKey = ""; // Tambahkan API key OpenAI jika diperlukan

    // =========================
    // 🔥 JIKA PAKAI AI BENERAN
    // =========================
    if(!empty($apiKey)){

        $prompt = "Barang: $nama, jumlah: $jumlah, status: $status. 
        Berikan insight singkat (maksimal 1 kalimat) untuk manajemen stok.";

        $data = [
            "model" => "gpt-4o-mini",
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);

        if(curl_errno($ch)){
            curl_close($ch);
            return fallbackAI($jumlah, $status);
        }

        curl_close($ch);

        $response = json_decode($result, true);

        if(isset($response['choices'][0]['message']['content'])){
            return $response['choices'][0]['message']['content'];
        }

        // kalau API gagal
        return fallbackAI($jumlah, $status);
    }

    // =========================
    // 🔥 TANPA API (FALLBACK)
    // =========================
    return fallbackAI($jumlah, $status);
}


// 🔥 FUNCTION FALLBACK (LOGIKA UTAMA)
function fallbackAI($jumlah, $status){

    if($status == "Reject"){
        return "⚠️ Barang bermasalah, perlu dicek";
    } elseif($status == "On Hold"){
        return "⏳ Barang tertahan, perlu tindakan";
    } elseif($jumlah < 5){
        return "⚠️ Stok menipis, segera restock";
    } else {
        return "✅ Stok dalam kondisi aman";
    }
}


// 🔥 PREDIKSI STOK
function prediksiStok($jumlah) {

    $rataHarian = 2;

    if($jumlah <= 0){
        return "❌ Stok habis";
    }

    $hari = ceil($jumlah / $rataHarian);

    if($hari <= 2){
        return "🔥 Akan habis dalam $hari hari!";
    } elseif($hari <= 5){
        return "⚠️ Habis dalam $hari hari";
    } else {
        return "✅ Aman $hari hari ke depan";
    }
}