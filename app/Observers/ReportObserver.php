<?php

namespace App\Observers;

use App\Models\CashFlow;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportObserver
{
    /**
     * Handle the Report "creating" event.
     */
    public function creating(Report $report): void
    {       
            $logo = Setting::first()?->logo;
            $today = now()->format('Ymd');
            $countToday = Report::whereDate('created_at', today())
            ->count() + 1;

            // Buat nama file dan path
            $fileName = 'LAPORAN-' . $today . '-' . str_pad($countToday, 2, '0', STR_PAD_LEFT);
            $path = 'reports/' . $fileName;

            if ($report->report_type == 'inflow') {
                // Ambil data Inflow sesuai start_date dan end_date
                $data = CashFlow::query()->where('type', 'income')
                    ->when($report->start_date, fn ($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                    ->when($report->end_date, fn ($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                    ->get();

                // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.pemasukan', [
                    'fileName' => $fileName,
                    'data' => $data,
                    'logo' => $logo,
                ])->setPaper('a4', 'portrait');

            } elseif($report->report_type == 'outflow') {
                 // Ambil data Outflow sesuai start_date dan end_date
                 $data = CashFlow::query()->where('type', 'expense')
                 ->when($report->start_date, fn ($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                 ->when($report->end_date, fn ($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                 ->get();

                 // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.pengeluaran', [
                    'fileName' => $fileName,
                    'data' => $data,
                    'logo' => $logo,
                ])->setPaper('a4', 'portrait');
            } else {
                // Ambil data Outflow sesuai start_date dan end_date
                 $data = Transaction::query()
                 ->when($report->start_date, fn ($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                 ->when($report->end_date, fn ($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                 ->get();

                 // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.penjualan', [
                    'fileName' => $fileName,
                    'data' => $data,
                    'logo' => $logo,
                ])->setPaper('a4', 'portrait');
            }

            // Simpan PDF ke storage (mendukung disk lokal maupun S3/R2)
            if (config('filesystems.default') === 's3') {
                Storage::disk('s3')->put($path, $pdf->output());
            } else {
                $pathDirectory = storage_path('app/public/reports');
                if (!file_exists($pathDirectory)) {
                    mkdir($pathDirectory, 0755, true);
                }
                $fullPath = storage_path('app/public/' . $path);
                $pdf->save($fullPath);
            }

            // Set nama dan path_file ke model
            $report->name = $fileName;
            $report->path_file = $path;
    }

    /**
     * Handle the Report "update" event.
     */
    public function updated(Report $report): void
    {
            $logo = Setting::first()?->logo;
            // Buat nama file dan path
            $path = 'reports/' . $report->name;

            if ($report->report_type == 'inflow') {
                // Ambil data Inflow sesuai start_date dan end_date
                $data = CashFlow::query()->where('type', 'income')
                    ->when($report->start_date, fn ($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                    ->when($report->end_date, fn ($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                    ->get();

                // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.pemasukan', [
                    'fileName' => $report->name,
                    'data' => $data,
                    'logo' => $logo,
                ])->setPaper('a4', 'portrait');

            } elseif($report->report_type == 'outflow') {
                 // Ambil data Outflow sesuai start_date dan end_date
                 $data = CashFlow::query()->where('type', 'expense')
                 ->when($report->start_date, fn ($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                 ->when($report->end_date, fn ($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                 ->get();

                 // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.pengeluaran', [
                    'fileName' => $report->name,
                    'data' => $data,
                    'logo' => $logo,
                ])->setPaper('a4', 'portrait');
            } else {
                // Ambil data Outflow sesuai start_date dan end_date
                 $data = Transaction::query()
                 ->when($report->start_date, fn ($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                 ->when($report->end_date, fn ($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                 ->get();

                 // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.penjualan', [
                    'fileName' => $report->name,
                    'data' => $data,
                    'logo' => $logo,
                ])->setPaper('a4', 'portrait');
            }

            // Simpan PDF ke storage (mendukung disk lokal maupun S3/R2)
            if (config('filesystems.default') === 's3') {
                Storage::disk('s3')->put($path, $pdf->output());
            } else {
                $pathDirectory = storage_path('app/public/reports');
                if (!file_exists($pathDirectory)) {
                    mkdir($pathDirectory, 0755, true);
                }
                $fullPath = storage_path('app/public/' . $path);
                $pdf->save($fullPath);
            }

    }

    /**
     * Handle the Report "deleted" event.
     */
    public function deleted(Report $report): void
    {
        $path = 'reports/' . $report->name;

        if (Storage::disk(config('filesystems.default'))->exists($path)) {
            Storage::disk(config('filesystems.default'))->delete($path);
        }
    }
   
}
