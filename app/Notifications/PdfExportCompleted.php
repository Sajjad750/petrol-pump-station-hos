<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PdfExportCompleted extends Notification
{
    use Queueable;

    protected string $filename;

    protected string $downloadUrl;

    protected int $totalRecords;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $filename, string $downloadUrl, int $totalRecords)
    {
        $this->filename = $filename;
        $this->downloadUrl = $downloadUrl;
        $this->totalRecords = $totalRecords;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'pdf_export_completed',
            'message' => "PDF export completed successfully! {$this->totalRecords} records exported.",
            'filename' => $this->filename,
            'download_url' => $this->downloadUrl,
            'total_records' => $this->totalRecords,
        ];
    }
}
