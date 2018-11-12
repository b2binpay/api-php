<?php
declare(strict_types=1);

namespace B2Binpay;

class AmountFactory
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * AmountFactory constructor.
     *
     * @param Currency $currency
     */
    public function __construct(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param string $sum
     * @param int|null $iso
     * @param int|null $pow
     * @return Amount
     */
    public function create(string $sum, int $iso = null, int $pow = null)
    {
        $amount = new Amount($this->currency);
        $amount->set($sum, $pow, $iso);

        return $amount;
    }
}
