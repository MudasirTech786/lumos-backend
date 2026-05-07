<?php

namespace App\Services;

use App\Models\CrewMember;

class CrewService
{
    public function create(array $data)
    {
        return CrewMember::create($data);
    }

    public function update(CrewMember $crew, array $data)
    {
        $crew->update($data);

        return $crew;
    }

    public function delete(CrewMember $crew)
    {
        return $crew->delete();
    }
}