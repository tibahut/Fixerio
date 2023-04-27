<?php

namespace tibahut\Fixerio;

class Result
{
    /**
     * The Base currency the result was returned in.
     *
     * @var string
     */
    private $base;

    /**
     * The date the result was generated.
     *
     * @var \DateTime
     */
    private $date;

    /**
     * All of the rates returned.
     *
     * @var array
     */
    private $rates;

    /**
     * Result constructor.
     */
    public function __construct(string $base, \DateTime $date, array $rates)
    {
        $this->base = $base;
        $this->date = $date;
        $this->rates = $rates;
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getRates(): array
    {
        return $this->rates;
    }

    /**
     * Get an individual rate by Currency code
     * Will return null if currency is not found in the result.
     */
    public function getRate(string $code): ?float
    {
        // the result won't have the base code in it,
        // because that would always be 1. But to make
        // dynamic code easier this prevents null if
        // the base code is asked for
        if ($code === $this->getBase()) {
            return 1.0;
        }

        if (!empty($this->rates[$code])) {
            return $this->rates[$code];
        }

        return null;
    }
}
