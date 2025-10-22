<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Measure;
use App\Repositories\MeasureRepository;

class MeasureService
{
    private MeasureRepository $measureRepository;

    public function __construct(MeasureRepository $measureRepository)
    {
        $this->measureRepository = $measureRepository;
    }

    public function getAllMeasures()
    {
        return $this->measureRepository->getAll();
    }

    public function getMeasureById($id)
    {
        $measure = $this->measureRepository->find($id);

        if (!$measure) {
            throw new NotFoundException('Measure not found.');
        }

        return $measure;
    }

    public function createMeasure(array $data)
    {
        if (Measure::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])->exists()) {
            throw new \RuntimeException('Measure code already exists.');
        }

        return $this->measureRepository->create($data);
    }

    public function updateMeasure(int $id, array $data)
    {
        $measure = $this->measureRepository->find($id);

        if (!$measure) {
            throw new NotFoundException('Measure not found.');
        }

        if (isset($data['code'])) {
            $exists = Measure::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                throw new \RuntimeException('Measure code already exists.');
            }
        }

        return $this->measureRepository->update($id, $data);
    }
}
