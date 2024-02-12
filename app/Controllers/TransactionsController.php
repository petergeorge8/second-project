<?php

declare(strict_types=1);

namespace App\Controllers;

use App\View;
use App\Model\TransactionsModel;

class TransactionsController
{
    private string $filePath;
    private array $fileData;

    private TransactionsModel $transactionModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionsModel();
    }
    public function index(): View
    {
        $transactionData = $this->transactionModel->selectAllTransactionsData(); // from database
        // $transactionData = $this->handleRowDataForView($transactionData);
        return View::make('transactions', ['transactionsData' => $transactionData]);
    }

    public function submitFileFromUser()
    {
        $this->storeFileToServer();

        $this->fileData = $this->parseFile();

        $this->saveToDatabase($this->fileData);

        header('Location: /transactions');
    }


    private function parseFile(): array
    {
        $file = fopen($this->filePath, 'r');
        $data = [];
        while (($row = fgetcsv($file)) !== false) {
            // Skip the header row
            if ($row[0] === 'Date') {
                continue;
            }
            $row = $this->handleRowDataForDB($row);
            $data[] = $row;
        }
        fclose($file);

        return $data;
    }

    private function handleRowDataForDB(array $row): array
    {
        $row[3] = str_replace(['$', ','], '', $row[3]);
        $row[0] = date('Y-m-d', strtotime($row[0]));
        return $row;
    }
    
    private function saveToDatabase(array $data): void
    {
        (new TransactionsModel())->saveTransactions($data);
    }
    private function storeFileToServer(): void
    {
        // check if file is not uploaded
        if (!isset($_FILES['csv_file'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No file uploaded']);
            return;
        }

        // get the file from $_FILES array
        $file = $_FILES['csv_file'];
        $this->filePath = STORAGE_PATH . '/' . uniqid() . '.csv';

        try {
            $this->validateFile($file['size'], $file['name']);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }

        move_uploaded_file($file['tmp_name'], $this->filePath);
    }

    private function validateFile(int $fileSize, string $fileName): bool
    {
        // Check file size
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        if ($fileSize > $maxFileSize) {
            throw new \Exception('File size limit exceeded. Maximum file size allowed is 5MB.');
        }

        // Check file extension
        $allowedExtensions = ['csv', 'xlsx'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new \Exception('Invalid file extension. Only CSV and XLSX files are allowed.');
        }
        return true;
    }
}
