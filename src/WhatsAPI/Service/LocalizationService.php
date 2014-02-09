<?php

namespace WhatsAPI\Service;

use WhatsAPI\Entity\Phone;
use WhatsAPI\Exception\InvalidArgumentException;

class LocalizationService
{
    /**
     * Dissect country code from phone number.
     *
     * @param  Phone                    $phone
     * @return Phone
     * @throws InvalidArgumentException
     */
    public function dissectPhone(Phone $phone)
    {
        if (($handle = fopen(__DIR__ . '/../../../data/countries.csv', 'rb')) !== false) {
            while (($data = fgetcsv($handle, 1000)) !== false) {
                if (strpos($phone->getPhoneNumber(), $data[1]) === 0) {
                    // Return the first appearance.
                    fclose($handle);

                    $mcc = explode("|", $data[2]);
                    $mcc = $mcc[0];

                    //hook:
                    //fix country code for North America
                    if (substr($data[1], 0, 1) == "1") {
                        $data[1] = "1";
                    }

                    $phone->setCountry($data[0])
                        ->setCc($data[1])
                        ->setPhone(substr(
                                $phone->getPhoneNumber(),
                                strlen($data[1]),
                                strlen($phone->getPhoneNumber())
                            )
                        )
                        ->setMcc($mcc)
                        ->setIso3166(isset($data[3]) ? $data[3] : null)
                        ->setIso639(isset($data[4]) ? $data[4] : null);

                    return $phone;
                }
            }
            fclose($handle);
        }

        throw new InvalidArgumentException("Phone number not recognized");
    }
}
