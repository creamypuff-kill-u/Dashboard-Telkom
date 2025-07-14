<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LookerDashboardDataController extends Controller
{
    protected $googleSheetService;
    // GANTI DENGAN ID GOOGLE SHEET ANDA
    protected $spreadsheetId = '1QOGWXZETQoOqeX4uGObrgQfNNYDxLkePhI0JHy3VMMI'; // <-- PASTIKAN INI SUDAH BENAR!

    // --- PERUBAHAN PENTING DI SINI: SESUAIKAN DENGAN NAMA TAB ASLI ANDA ---
    protected $tabelOrderSheetName = 'Tabel Order';          // Untuk data tabel utama
    protected $diagramFalloutSheetName = 'Diagram Fallout';  // Untuk data Diagram Fallout
    protected $teknisiSheetName = 'Teknisi';              // Mungkin untuk data Funneling B2C atau data teknisi lain
    protected $jadwalSheetName = 'Jadwal';                // Untuk data Jadwal Teknisi
    protected $psReB2cSheetName = 'PS/RE B2C';             // Untuk data PS/RE B2C
    // --- AKHIR PERUBAHAN PENTING ---


    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }

    // --- Metode untuk mengambil data Tabel Order (sebelumnya MainDataTable) ---
    public function getMainTableData()
    {
        // Gunakan nama tab 'Tabel Order' dan sesuaikan range selnya
        $range = "'" . $this->tabelOrderSheetName . "'!A1:F77"; // Contoh range, SESUAIKAN!
        $data = $this->googleSheetService->getData($this->spreadsheetId, $range);
        
        if (!empty($data) && count($data) > 1) {
            $headers = array_shift($data);
            $formattedData = [];
            foreach ($data as $row) {
                 if (count($row) === count($headers)) { // Pastikan jumlah kolom cocok
                    $formattedData[] = array_combine($headers, $row);
                }
            }
            return response()->json($formattedData);
        }
        return response()->json([]);
    }

    // --- Metode untuk mengambil data Diagram Fallout (sebelumnya FalloutData) ---
    public function getFalloutData()
    {
        // Gunakan nama tab 'Diagram Fallout' dan sesuaikan range selnya
        $range = "'" . $this->diagramFalloutSheetName . "'!A1:B4"; // Contoh range, SESUAIKAN!
        $data = $this->googleSheetService->getData($this->spreadsheetId, $range);
        
        if (!empty($data) && count($data) > 1) {
            $headers = array_shift($data);
            $formattedData = [];
            foreach ($data as $row) {
                if (count($row) === count($headers)) {
                    $formattedData[] = array_combine($headers, $row);
                }
            }
            return response()->json($formattedData);
        }
        return response()->json([]);
    }

    // --- Metode untuk mengambil data PS/RE B2C ---
    public function getPsReB2cData()
    {
        // Gunakan nama tab 'PS/RE B2C' dan sesuaikan range selnya
        $range = "'" . $this->psReB2cSheetName . "'!A1:C9"; // Contoh range, SESUAIKAN!
        $data = $this->googleSheetService->getData($this->spreadsheetId, $range);
        
        if (!empty($data) && count($data) > 1) {
            $headers = array_shift($data);
            $formattedData = [];
            foreach ($data as $row) {
                 if (count($row) === count($headers)) {
                    $formattedData[] = array_combine($headers, $row);
                }
            }
            return response()->json($formattedData);
        }
        return response()->json([]);
    }

    // --- Metode untuk mengambil data Jadwal Teknisi ---
    public function getTechnicianSchedule()
    {
        // Gunakan nama tab 'Jadwal' dan sesuaikan range selnya
        $range = "'" . $this->jadwalSheetName . "'!A1:B9"; // Contoh range, SESUAIKAN!
        $data = $this->googleSheetService->getData($this->spreadsheetId, $range);
        
        if (!empty($data) && count($data) > 1) {
            $headers = array_shift($data);
            $formattedData = [];
            foreach ($data as $row) {
                if (count($row) === count($headers)) {
                    $formattedData[] = array_combine($headers, $row);
                } else {
                    Log::warning('Jumlah kolom tidak cocok saat memproses jadwal teknisi. Baris dilewati.');
                }
            }
            return response()->json($formattedData);
        }
        return response()->json([]);
    }

    // --- Metode untuk data 'Teknisi' (ini perlu diklarifikasi apa isinya, mungkin Funneling B2C?) ---
    // Saya asumsikan 'Teknisi' ini berisi data Funneling B2C yang Anda sebutkan sebelumnya.
    // Jika tidak, Anda perlu membuat metode baru dan menyesuaikan namanya.
    public function getFunnelingData() // Mengganti nama metode ini menjadi lebih sesuai jika 'Teknisi' adalah Funneling B2C
    {
        // Gunakan nama tab 'Teknisi' dan sesuaikan range selnya
        $range = "'" . $this->teknisiSheetName . "'!A1:I9"; // Contoh range, SESUAIKAN!
        $data = $this->googleSheetService->getData($this->spreadsheetId, $range);
        
        if (!empty($data) && count($data) > 1) {
            $headers = array_shift($data);
            $formattedData = [];
            foreach ($data as $row) {
                if (count($row) === count($headers)) {
                    $formattedData[] = array_combine($headers, $row);
                } else {
                    Log::warning('Jumlah kolom tidak cocok saat memproses data funneling. Baris dilewati.');
                }
            }
            return response()->json($formattedData);
        }
        return response()->json([]);
    }

    // --- Metode addTechnicianSchedule dan updateTechnicianSchedule ---
    // Ini akan tetap menggunakan $this->jadwalSheetName
    public function addTechnicianSchedule(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:255',
                'jadwal' => 'required|string|in:LIBUR,MASUK',
            ]);

            $values = [
                [$request->nama, $request->jadwal]
            ];

            $range = "'" . $this->jadwalSheetName . "'!A:B"; // Gunakan nama tab 'Jadwal'
            $result = $this->googleSheetService->appendData($this->spreadsheetId, $range, $values);

            return response()->json(['message' => 'Jadwal berhasil ditambahkan.', 'data' => $result], 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error adding technician schedule: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menambahkan jadwal: ' . $e->getMessage()], 500);
        }
    }

    public function updateTechnicianSchedule(Request $request, int $rowIndex)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:255',
                'jadwal' => 'required|string|in:LIBUR,MASUK',
            ]);

            if ($rowIndex < 2) { 
                return response()->json(['message' => 'Nomor baris tidak valid untuk update.'], 400);
            }

            $values = [
                [$request->nama, $request->jadwal]
            ];

            $range = "'" . $this->jadwalSheetName . "'!A' . $rowIndex . ':B' . $rowIndex"; // Gunakan nama tab 'Jadwal'
            $result = $this->googleSheetService->updateData($this->spreadsheetId, $range, $values);

            return response()->json(['message' => 'Jadwal berhasil diperbarui.', 'data' => $result]);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating technician schedule: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui jadwal: ' . $e->getMessage()], 500);
        }
    }
}