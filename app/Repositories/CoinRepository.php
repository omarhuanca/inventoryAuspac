<?php

namespace App\Repositories;

use App\Models\Coin;

class CoinRepository
{
    private Coin $coin;

    public function __construct(Coin $coin)
    {
        $this->coin = $coin;
    }

    public function getAll()
    {
        return $this->coin->all();
    }

    public function find(int $id)
    {
        return $this->coin->find($id);
    }

    public function create(array $data)
    {
        $coin = Coin::at($data['code']);
        $coin->save();
        return $coin;
    }

    public function update(int $id, array $data)
    {
        $coin = $this->find($id);

        if (isset($data['code'])) {
            Coin::at($data['code']);
            $coin->code = strtoupper(trim($data['code']));
        }

        $coin->save();
        return $coin;
    }
}
