<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->resource['status'] ?? 'error',
            'status_code' => $this->resource['status_code'] ?? null,
            'message' => $this->resource['message'] ?? null,
            'data' => $this->resource['data'] ?? null,
            'errors' => $this->resource['errors'] ?? null,
        ];
    }
}
