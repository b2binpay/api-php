<?php
declare(strict_types=1);

namespace B2Binpay;

/**
 * Amount Factory
 *
 * @package B2Binpay
 */
class AmountFactory
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * AmountFactory constructor.
     *
     * @param Currency|null $currency
     */
    public function __construct(Currency $currency = null)
    {
        $this->currency = $currency ?? new Currency();
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
