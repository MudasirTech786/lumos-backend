<?php

namespace App\Services;

use App\Models\CrewMember;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CrewService
{
    public function create(array $data)
    {
        $this->handlePhoto($data);

        return CrewMember::create($data);
    }

    public function update(CrewMember $crew, array $data)
    {
        $this->handlePhoto($data, $crew);

        $crew->update($data);

        return $crew;
    }

    public function delete(CrewMember $crew)
    {
        if ($crew->profile_photo) {
            Storage::disk('public')->delete($crew->profile_photo);
        }

        return $crew->delete();
    }

    private function handlePhoto(array &$data, ?CrewMember $crew = null): void
    {
        // No photo in payload — leave unchanged
        if (! array_key_exists('profile_photo', $data)) {
            return;
        }

        $photo = $data['profile_photo'];

        // Uploaded file — store new, delete old
        if ($photo instanceof UploadedFile) {
            if ($crew && $crew->profile_photo) {
                Storage::disk('public')->delete($crew->profile_photo);
            }

            $filename = Str::uuid() . '.' . $photo->getClientOriginalExtension();
            $data['profile_photo'] = $photo->storeAs('crew', $filename, 'public');
            return;
        }

        // Null / empty — explicitly remove photo
        if (! $photo) {
            if ($crew && $crew->profile_photo) {
                Storage::disk('public')->delete($crew->profile_photo);
            }
            $data['profile_photo'] = null;
            return;
        }

        // String — keep existing value as-is
    }
}