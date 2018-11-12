<?php
declare(strict_types=1);

namespace B2Binpay;

use B2Binpay\Exception\UnknownValueException;

class Currency
{
    const MAX_PRECISION = 18;

    private static $list = [
        840 => [
            'iso' => 840,
            'alpha' => 'USD',
            'name' => 'US Dollar',
            'precision' => 2
        ],
        978 => [
            'iso' => 978,
            'alpha' => 'EUR',
            'name' => 'Euro',
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
        2000 => [
            'iso' => 2000,
            'alpha' => 'B2BX',
            'name' => 'B2BX',
            'precision' => 18
        ],
        2005 => [
            'iso' => 2005,
            'alpha' => 'USDT',
            'name' => 'Tether USD',
            'precision' => 8
        ],
        2006 => [
            'iso' => 2006,
            'alpha' => 'EURT',
            'name' => 'Tether EUR',
            'precision' => 8
        ],
        2014 => [
            'iso' => 2014,
            'alpha' => 'NEO',
            'name' => 'NEO',
            'precision' => 3
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
                if ($item['alpha'] === $alpha) {
                    $carry = $item['iso'];
                }
                return (int)$carry;
            },
            array()
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
