<?php

namespace App\Services;

use Google\Client; // <-- PERLU DIIMPORT
use Google\Service\Sheets; // <-- PERLU DIIMPORT
use Google\Service\Sheets\ValueRange; // Sudah ada
use Google\Service\Exception as GoogleServiceException; // <-- PERLU DIIMPORT (dengan alias)
use Illuminate\Support\Facades\Log; // <-- PERLU DIIMPORT

class GoogleSheetService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Admin Dashboard App');
        // Pastikan scope sudah SPREADSHEETS untuk operasi tulis
        $this->client->setScopes([Sheets::SPREADSHEETS]); 
        $this->client->setAuthConfig(base_path(env('GOOGLE_SHEETS_CREDENTIALS')));

        $this->service = new Sheets($this->client);
    }

    /**
     * Ambil data dari Google Sheet.
     *
     * @param string $spreadsheetId ID Spreadsheet Google Anda
     * @param string $range Contoh: 'Sheet1!A1:D10'
     * @return array
     */
    public function getData(string $spreadsheetId, string $range): array
    {
        try {
            $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
            return $response->getValues();
        } catch (GoogleServiceException $e) { // Menggunakan alias GoogleServiceException
            Log::error('Error reading Google Sheet: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Tambah baris data baru ke Google Sheet.
     *
     * @param string $spreadsheetId ID Spreadsheet Google Anda
     * @param string $range Contoh: 'Sheet1!A:B' (akan menambahkan ke baris kosong pertama)
     * @param array $values Array of arrays, contoh: [['Nama', 'Jadwal']]
     * @return mixed Response dari Google Sheets API
     */
    public function appendData(string $spreadsheetId, string $range, array $values)
    {
        $body = new ValueRange([
            'values' => $values
        ]);
        $params = [
            'valueInputOption' => 'RAW' // RAW: input apa adanya; USER_ENTERED: seperti input user
        ];
        try {
            $result = $this->service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
            return $result;
        } catch (GoogleServiceException $e) { // Menggunakan alias GoogleServiceException
            Log::error('Error appending data to Google Sheet: ' . $e->getMessage());
            throw new \Exception('Gagal menambahkan data ke Google Sheet: ' . $e->getMessage());
        }
    }

    /**
     * Update baris data yang ada di Google Sheet.
     *
     * @param string $spreadsheetId ID Spreadsheet Google Anda
     * @param string $range Contoh: 'Sheet1!A2:B2' (range spesifik untuk update)
     * @param array $values Array of arrays, contoh: [['Nama Baru', 'Jadwal Baru']]
     * @return mixed Response dari Google Sheets API
     */
    public function updateData(string $spreadsheetId, string $range, array $values)
    {
        $body = new ValueRange([
            'values' => $values
        ]);
        $params = [
            'valueInputOption' => 'RAW'
        ];
        try {
            $result = $this->service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
            return $result;
        } catch (GoogleServiceException $e) { // Menggunakan alias GoogleServiceException
            Log::error('Error updating data in Google Sheet: ' . $e->getMessage());
            throw new \Exception('Gagal memperbarui data di Google Sheet: ' . $e->getMessage());
        }
    }
}