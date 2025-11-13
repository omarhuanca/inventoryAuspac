<?php

namespace App\Modules\Coin\Service;

use App\Exceptions\NotFoundException;
use App\Modules\Coin\Domain\Coin;
use App\Modules\Coin\Repository\CoinRepository;

class CoinService
{
    private CoinRepository $coinRepository;

    public function __construct(CoinRepository $coinRepository)
    {
        $this->coinRepository = $coinRepository;
    }

    public function getAllCoins()
    {
        return $this->coinRepository->getAll();
    }

    public function getCoinById($id)
    {
        $coin = $this->coinRepository->find($id);

        if (!$coin) {
            throw new NotFoundException('Coin not found.');
        }

        return $coin;
    }

    public function createCoin(array $data)
    {
        if (Coin::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])->exists()) {
            throw new \RuntimeException('Coin code already exists.');
        }

        return $this->coinRepository->create($data);
    }

    public function updateCoin(int $id, array $data)
    {
        $coin = $this->coinRepository->find($id);

        if (!$coin) {
            throw new NotFoundException('Coin not found.');
        }

        if (isset($data['code'])) {
            $exists = Coin::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                throw new \RuntimeException('Coin code already exists.');
            }
        }

        return $this->coinRepository->update($id, $data);
    }
}
