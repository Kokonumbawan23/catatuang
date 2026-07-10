<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterController extends Controller
{
    public function export(Request $request, string $format)
    {
        return match ($format) {
            'xlsx' => $this->exportXlsx($request),
            'csv' => $this->exportCsv($request),
            'pdf' => $this->exportPdf($request),
            default => abort(404),
        };
    }

    private function exportXlsx(Request $request): BinaryFileResponse
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $type = $request->input('type');

        $transactions = $this->buildQuery($request)
            ->lazy();

        $totalIncome = $this->sumForUser($month, $year, 'Income');
        $totalExpense = $this->sumForUser($month, $year, 'Expense');

        $tempFile = tempnam(sys_get_temp_dir(), 'catatuang_xlsx_').'.xlsx';

        $writer = new XlsxWriter;
        $writer->openToFile($tempFile);

        $headerStyle = (new Style)->withFontBold(true);

        $writer->addRow(Row::fromValuesWithStyle(
            ['No', 'Tanggal', 'Tipe', 'Kategori', 'Nominal', 'Catatan'],
            $headerStyle
        ));

        $no = 1;
        foreach ($transactions as $transaction) {
            $writer->addRow(Row::fromValues([
                $no++,
                $transaction->transaction_at->format('d-m-Y'),
                $transaction->type === 'Income' ? 'Pemasukan' : 'Pengeluaran',
                $transaction->category->name ?? 'Tanpa Kategori',
                (float) $transaction->amount,
                $transaction->description ?? '',
            ]));
        }

        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValuesWithStyle(
            ['', '', '', 'Total Pemasukan', (float) $totalIncome, ''],
            $headerStyle
        ));
        $writer->addRow(Row::fromValuesWithStyle(
            ['', '', '', 'Total Pengeluaran', (float) $totalExpense, ''],
            $headerStyle
        ));
        $writer->addRow(Row::fromValuesWithStyle(
            ['', '', '', 'Saldo', (float) ($totalIncome - $totalExpense), ''],
            $headerStyle
        ));

        $writer->close();

        $filename = $this->filename($year, $month, $type, 'xlsx');

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function exportCsv(Request $request): StreamedResponse
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $type = $request->input('type');

        $transactions = $this->buildQuery($request)->lazy();

        $filename = $this->filename($year, $month, $type, 'csv');

        return response()->streamDownload(function () use ($transactions) {
            $headers = ['No', 'Tanggal', 'Tipe', 'Kategori', 'Nominal', 'Catatan'];

            echo $this->csvLine($headers);

            $no = 1;
            foreach ($transactions as $transaction) {
                echo $this->csvLine([
                    $no++,
                    $transaction->transaction_at->format('d-m-Y'),
                    $transaction->type === 'Income' ? 'Pemasukan' : 'Pengeluaran',
                    $transaction->category->name ?? 'Tanpa Kategori',
                    number_format($transaction->amount, 0, ',', '.'),
                    $transaction->description ?? '',
                ]);
            }

            $totalIncome = $transactions->where('type', 'Income')->sum('amount');
            $totalExpense = $transactions->where('type', 'Expense')->sum('amount');

            echo $this->csvLine([]);
            echo $this->csvLine(['', '', '', 'Total Pemasukan', number_format($totalIncome, 0, ',', '.')]);
            echo $this->csvLine(['', '', '', 'Total Pengeluaran', number_format($totalExpense, 0, ',', '.')]);
            echo $this->csvLine(['', '', '', 'Saldo', number_format($totalIncome - $totalExpense, 0, ',', '.')]);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportPdf(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $type = $request->input('type');

        $transactions = $this->buildQuery($request)->get();

        $totalIncome = $this->sumForUser($month, $year, 'Income');
        $totalExpense = $this->sumForUser($month, $year, 'Expense');

        $pdf = Pdf::loadView('exports.transactions-pdf', [
            'transactions' => $transactions,
            'month' => $month,
            'year' => $year,
            'type' => $type,
            'totalExpense' => $totalExpense,
            'totalIncome' => $totalIncome,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download($this->filename($year, $month, $type, 'pdf'));
    }

    private function buildQuery(Request $request): HasMany
    {
        $user = Auth::user();
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $type = $request->input('type');

        $query = $user->transactions()
            ->with('category')
            ->forMonth($month, $year)
            ->orderBy('transaction_at', 'desc')
            ->orderBy('created_at', 'desc');

        if ($type === 'Expense') {
            $query->expenses();
        } elseif ($type === 'Income') {
            $query->incomes();
        }

        return $query;
    }

    private function sumForUser(int $month, int $year, string $type): float
    {
        return (float) Auth::user()->transactions()
            ->forMonth($month, $year)
            ->{$type === 'Income' ? 'incomes' : 'expenses'}()
            ->sum('amount');
    }

    private function filename(int $year, int $month, ?string $type, string $extension): string
    {
        $suffix = $type ? '_'.strtolower($type) : '';

        return "transactions_{$year}_{$month}{$suffix}.{$extension}";
    }

    private function csvLine(array $values): string
    {
        $escaped = array_map(function ($value) {
            $value = (string) $value;

            if (preg_match('/[,"\r\n]/', $value)) {
                return '"'.str_replace('"', '""', $value).'"';
            }

            return $value;
        }, $values);

        return implode(',', $escaped)."\n";
    }
}
