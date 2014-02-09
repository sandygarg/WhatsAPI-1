<?php

namespace WhatsAPI\Entity;

class Phone
{
    /**
     * @var string
     */
    protected $phoneNumber;
    /**
     * @var string
     */
    protected $country;
    /**
     * @var string
     */
    protected $cc;
    /**
     * @var string
     */
    protected $phone;
    /**
     * @var string
     */
    protected $mcc;
    /**
     * @var string
     */
    protected $iso3166;
    /**
     * @var string
     */
    protected $iso639;

    /**
     * @param string $phoneNumber
     */
    public function __construct($phoneNumber)
    {
        $this->setPhoneNumber($phoneNumber);
    }

    /**
     * @param  string $cc
     * @return $this
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @return string
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @param  string $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param  string $iso3166
     * @return $this
     */
    public function setIso3166($iso3166)
    {
        $this->iso3166 = $iso3166;

        return $this;
    }

    /**
     * @return string
     */
    public function getIso3166()
    {
        return $this->iso3166;
    }

    /**
     * @param  string $iso639
     * @return $this
     */
    public function setIso639($iso639)
    {
        $this->iso639 = $iso639;

        return $this;
    }

    /**
     * @return string
     */
    public function getIso639()
    {
        return $this->iso639;
    }

    /**
     * @param  string $mcc
     * @return $this
     */
    public function setMcc($mcc)
    {
        $this->mcc = $mcc;

        return $this;
    }

    /**
     * @return string
     */
    public function getMcc()
    {
        return $this->mcc;
    }

    /**
     * @param  string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param  string $phoneNumber
     * @return $this
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }
}
