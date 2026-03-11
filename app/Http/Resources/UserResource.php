<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * Email is only exposed to the authenticated user themselves or to admins,
     * to prevent user enumeration and privacy leaks.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = Auth::user();
        $isSelf   = $authUser && $authUser->id === $this->id;
        $isAdmin  = $authUser && $authUser->is_admin;

        return [
            'id'                => $this->id,
            'avatar_url'        => $this->avatar ? Storage::url($this->avatar) : null,
            'name'              => $this->name,
            'email'             => ($isSelf || $isAdmin) ? $this->email : null,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'is_admin'          => (bool) $this->is_admin,
            'last_message'      => $this->last_message,
            'last_message_date' => $this->last_message_date,
        ];
    }
}
