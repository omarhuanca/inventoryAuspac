<?php

namespace App\Modules\Measure\Repository;

use App\Modules\Measure\Domain\Measure;

class MeasureRepository
{
    private Measure $measure;

    public function __construct(Measure $measure)
    {
        $this->measure = $measure;
    }

    public function getAll()
    {
        return $this->measure->all();
    }

    public function find(int $id)
    {
        return $this->measure->find($id);
    }

    public function create(array $data)
    {
        $measure = Measure::at($data['code']);
        $measure->save();
        return $measure;
    }

    public function update(int $id, array $data)
    {
        $measure = $this->find($id);

        if (isset($data['code'])) {
            Measure::at($data['code']);
            $measure->code = strtoupper(trim($data['code']));
        }

        $measure->save();
        return $measure;
    }
}
