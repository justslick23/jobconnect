<?php

namespace App\Http\Controllers;

use App\Console\Commands\AutoShortlistCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ShortlistingReportController extends Controller
{
    /**
     * Generate shortlisting report for a specific job requisition
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'requisition_id' => 'nullable|integer|exists:job_requisitions,id',
            'threshold' => 'nullable|numeric|min:0|max:100',
            'force' => 'nullable|boolean',
        ]);

        $requisitionId = $request->get('requisition_id');
        $threshold = $request->get('threshold');
        $force = $request->get('force', false);

        try {
            // Build artisan command
            $command = 'jobs:auto-shortlist --generate-report';
            
            if ($requisitionId) {
                $command .= " --requisition-id={$requisitionId}";
            }
            
            if ($threshold) {
                $command .= " --threshold={$threshold}";
            }
            
            if ($force) {
                $command .= ' --force';
            }

            // Execute the command
            $exitCode = Artisan::call($command);
            $output = Artisan::output();

            if ($exitCode === 0) {
                // Extract file information from command output if available
                $lines = explode("\n", $output);
                $downloadUrl = null;
                $fileName = null;

                foreach ($lines as $line) {
                    if (strpos($line, 'Download URL:') !== false) {
                        $downloadUrl = trim(str_replace('🔗 Download URL:', '', $line));
                    }
                    if (strpos($line, 'shortlisting_report_') !== false && strpos($line, '.xlsx') !== false) {
                        preg_match('/shortlisting_report_[\d\-_]+\.xlsx/', $line, $matches);
                        if (!empty($matches)) {
                            $fileName = $matches[0];
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Shortlisting report generated successfully',
                    'download_url' => $downloadUrl,
                    'file_name' => $fileName,
                    'command_output' => $output,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate report',
                    'error' => $output,
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a previously generated report
     */
    public function downloadReport($fileName)
    {
        // Validate filename to prevent path traversal
        if (!preg_match('/^shortlisting_report_[\d\-_]+\.xlsx$/', $fileName)) {
            return response()->json(['error' => 'Invalid file name'], 400);
        }

        $filePath = "public/{$fileName}";

        if (!Storage::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Storage::download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * List available shortlisting reports
     */
    public function listReports()
    {
        $files = Storage::files('public');
        $reports = collect($files)
            ->filter(function ($file) {
                return preg_match('/shortlisting_report_[\d\-_]+\.xlsx$/', basename($file));
            })
            ->map(function ($file) {
                $fileName = basename($file);
                return [
                    'file_name' => $fileName,
                    'size' => Storage::size($file),
                    'created_at' => Storage::lastModified($file),
                    'created_at_human' => date('Y-m-d H:i:s', Storage::lastModified($file)),
                    'download_url' => route('shortlisting.download', ['fileName' => $fileName]),
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'success' => true,
            'reports' => $reports,
        ]);
    }

    /**
     * Delete a shortlisting report
     */
    public function deleteReport($fileName)
    {
        // Validate filename
        if (!preg_match('/^shortlisting_report_[\d\-_]+\.xlsx$/', $fileName)) {
            return response()->json(['error' => 'Invalid file name'], 400);
        }

        $filePath = "public/{$fileName}";

        if (!Storage::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        try {
            Storage::delete($filePath);
            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report: ' . $e->getMessage(),
            ], 500);
        }
    }
}