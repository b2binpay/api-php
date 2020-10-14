<?php
declare(strict_types=1);

namespace B2Binpay;

use B2Binpay\Exception\UnknownValueException;

/**
 * Currency directory
 *
 * @package B2Binpay
 */
class Currency
{
    const MAX_PRECISION = 18;

    /**
     * @var array[]
     */
    private static $list = [
        156 => [
            'iso' => 156,
            'alpha' => 'CNY',
            'name' => 'Chinese yuan',
            'precision' => 2
        ],
        344 => [
            'iso' => 344,
            'alpha' => 'HKD',
            'name' => 'Hong Kong dollar',
            'precision' => 2
        ],
        392 => [
            'iso' => 392,
            'alpha' => 'JPY',
            'name' => 'Japanese yen',
            'precision' => 2
        ],
        398 => [
            'iso' => 398,
            'alpha' => 'KZT',
            'name' => 'Tenge',
            'precision' => 2
        ],
        643 => [
            'iso' => 643,
            'alpha' => 'RUB',
            'name' => 'Russian ruble',
            'precision' => 2
        ],
        826 => [
            'iso' => 826,
            'alpha' => 'GBP',
            'name' => 'Pound Sterling',
            'precision' => 2
        ],
        840 => [
            'iso' => 840,
            'alpha' => 'USD',
            'name' => 'US Dollar',
            'precision' => 2
        ],
        933 => [
            'iso' => 933,
            'alpha' => 'BYN',
            'name' => 'Belarusian Ruble',
            'precision' => 2
        ],
        978 => [
            'iso' => 978,
            'alpha' => 'EUR',
            'name' => 'Euro',
            'precision' => 2
        ],
        980 => [
            'iso' => 980,
            'alpha' => 'UAH',
            'name' => 'Ukrainian hryvnia',
            'precision' => 2
        ],
        1000 => [
            'iso' => 1000,
            'alpha' => 'BTC',
            'name' => 'Bitcoin',
            'precision' => 8
        ],
        1002 => [
            'iso' => 1002,
            'alpha' => 'ETH',
            'name' => 'Ethereum',
            'precision' => 18
        ],
        1003 => [
            'iso' => 1003,
            'alpha' => 'LTC',
            'name' => 'Litecoin',
            'precision' => 8
        ],
        1005 => [
            'iso' => 1005,
            'alpha' => 'DASH',
            'name' => 'DASH',
            'precision' => 8
        ],
        1006 => [
            'iso' => 1006,
            'alpha' => 'BCH',
            'name' => 'Bitcoin Cash',
            'precision' => 8
        ],
        1007 => [
            'iso' => 1007,
            'alpha' => 'XMR',
            'name' => 'Monero',
            'precision' => 12
        ],
        1010 => [
            'iso' => 1010,
            'alpha' => 'XRP',
            'name' => 'Ripple',
            'precision' => 6
        ],
        1012 => [
            'iso' => 1012,
            'alpha' => 'XEM',
            'name' => 'NEM',
            'precision' => 6
        ],
        1018 => [
            'iso' => 1018,
            'alpha' => 'ADA',
            'name' => 'Cardano',
            'precision' => 6
        ],
        1019 => [
            'iso' => 1019,
            'alpha' => 'DOGE',
            'name' => 'Dogecoin',
            'precision' => 8
        ],
        1020 => [
            'iso' => 1020,
            'alpha' => 'ZEC',
            'name' => 'Zcash',
            'precision' => 8
        ],
        1021 => [
            'iso' => 1021,
            'alpha' => 'XLM',
            'name' => 'Stellar',
            'precision' => 7
        ],
        1022 => [
            'iso' => 1022,
            'alpha' => 'EOS',
            'name' => 'EOS',
            'precision' => 4
        ],
        1026 => [
            'iso' => 1026,
            'alpha' => 'TRX',
            'name' => 'TRON',
            'precision' => 6
        ],
        2005 => [
            'iso' => 2005,
            'alpha' => 'USDT',
            'name' => 'Tether USD',
            'precision' => 8,
            'node' => [
                'usdt-omni',
                'usdt-eth'
            ]
        ],
        2006 => [
            'iso' => 2006,
            'alpha' => 'EURT',
            'name' => 'Tether EUR',
            'precision' => 8,
            'node' => [
                'eurt-omni',
                'eurt-eth'
            ]
        ],
        2014 => [
            'iso' => 2014,
            'alpha' => 'NEO',
            'name' => 'NEO',
            'precision' => 3
        ],
        2021 => [
            'iso' => 2021,
            'alpha' => 'PAX',
            'name' => 'PAX',
            'precision' => 18,
            'node' => [
                'pax-eth'
            ]
        ],
        2022 => [
            'iso' => 2022,
            'alpha' => 'TUSD',
            'name' => 'TrueUSD',
            'precision' => 18,
            'node' => [
                'tusd-eth'
            ]
        ],
        2023 => [
            'iso' => 2023,
            'alpha' => 'GUSD',
            'name' => 'Gemini dollar',
            'precision' => 2,
            'node' => [
                'gusd-eth'
            ]
        ],
        2024 => [
            'iso' => 2024,
            'alpha' => 'USDC',
            'name' => 'USD//Coin',
            'precision' => 6,
            'node' => [
                'usdc-eth'
            ]
        ],
        2025 => [
            'iso' => 2025,
            'alpha' => 'BNB',
            'name' => 'Binance Coin',
            'precision' => 8
        ],
        2068 => [
            'iso' => 2068,
            'alpha' => 'DAI',
            'name' => 'Dai Stablecoin',
            'precision' => 18,
            'node' => [
                'dai-eth'
            ]
        ],
        2077 => [
            'iso' => 2077,
            'alpha' => 'BUSD',
            'name' => 'Binance USD',
            'precision' => 18,
            'node' => [
                'busd-eth'
            ]
        ]
    ];

    /**
     * @param int $iso
     * @return string
     * @throws UnknownValueException
     */
    public function getAlpha(int $iso): string
    {
        if (!array_key_exists($iso, Currency::$list)) {
            throw new UnknownValueException($iso);
        }
        return Currency::$list[$iso]['alpha'];
    }

    /**
     * @param string $alpha
     * @return int
     * @throws UnknownValueException
     */
    public function getIso(string $alpha): int
    {
        $alpha = strtoupper($alpha);

        $iso = array_reduce(
            self::$list,
            function ($carry, $item) use ($alpha) {
               $nodeList = isset($item['node']) ? $item['node'] : [];
               $nodeList = array_map('strtoupper', $nodeList);

                if ($item['alpha'] === $alpha || in_array($alpha, $nodeList)) {
                    $carry = $item['iso'];
                }
                return (int)$carry;
            }
        );

        if (empty($iso)) {
            throw new UnknownValueException($alpha);
        }

        return $iso;
    }

    /**
     * @param int $iso
     * @return int
     * @throws UnknownValueException
     */
    public function getPrecision(int $iso): int
    {
        if (!array_key_exists($iso, self::$list)) {
            throw new UnknownValueException($iso);
        }

        return self::$list[$iso]['precision'];
    }

    /**
     * @return int
     */
    public function getMaxPrecision(): int
    {
        return self::MAX_PRECISION;
    }

    /**
     * @param int $iso
     * @return string
     * @throws UnknownValueException
     */
    public function getName(int $iso): string
    {
        if (!array_key_exists($iso, self::$list)) {
            throw new UnknownValueException($iso);
        }

        return self::$list[$iso]['name'];
    }
}
