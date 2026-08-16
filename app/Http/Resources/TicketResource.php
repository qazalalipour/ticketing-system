<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'rejection_reason' => $this->rejection_reason,
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attachment' => $this->whenLoaded(
                'attachments',
                fn() => $this->attachments->map(fn($attachment) => [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size,
                    'file_path' => 'storage/' . $attachment->file_path,
                ])
            ),
            'status_history' => $this->whenLoaded(
                'statusHistories',
                fn() => $this->statusHistories->map(fn($history) => [
                    'from' => $history->from_status,
                    'to' => $history->to_status,
                    'reason' => $history->reason,
                    'changed_by' => $history->changed_by,
                    'created_at' => $history->created_at,
                ])
            ),
        ];
    }
}
