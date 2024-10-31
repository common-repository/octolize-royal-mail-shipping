<?php

namespace OctolizeShippingRoyalMailVendor\Octolize\RoyalMailShippingService;

use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services;
/**
 * A class that defines Royal Mail services.
 */
class RoyalMailServices
{
    /**
     * @return array
     */
    public function get_services(): array
    {
        $royal_mail_services = new Services();
        return ['domestic_uk' => $this->prepare_services($royal_mail_services->getDomesticServices()), 'international' => $this->prepare_services($royal_mail_services->getInternationalServices())];
    }
    /**
     * @return array
     */
    public function get_all_services(): array
    {
        return array_merge($this->get_services_domestic_uk(), $this->get_services_international());
    }
    /**
     * @return array
     */
    public function get_services_domestic_uk(): array
    {
        return $this->get_services()['domestic_uk'];
    }
    /**
     * @return array
     */
    public function get_services_international(): array
    {
        return $this->get_services()['international'];
    }
    /**
     * @param Services\Service[] $services
     * @return array
     */
    private function prepare_services(array $services): array
    {
        $prepared_services = [];
        foreach ($services as $code => $service) {
            $prepared_services[$code] = $service->getName();
        }
        return $prepared_services;
    }
    /**
     * @param array $enabled_services
     * @return Services\Service[]
     */
    public function get_royal_mail_services(array $enabled_services): array
    {
        $royal_mail_services = new Services();
        $all_services = array_merge($royal_mail_services->getDomesticServices(), $royal_mail_services->getInternationalServices());
        $enabled_royal_mail_services = [];
        foreach ($enabled_services as $service_code => $service) {
            if (isset($all_services[$service_code])) {
                $enabled_royal_mail_services[] = $all_services[$service_code];
            }
        }
        return $enabled_royal_mail_services;
    }
    /**
     * Can return service code for given service.
     *
     * @param Services\Service $service
     * @return string|void
     */
    public function get_service_code_for_service(Services\Service $service)
    {
        $royal_mail_services = new Services();
        $all_services = array_merge($royal_mail_services->getDomesticServices(), $royal_mail_services->getInternationalServices());
        foreach ($all_services as $service_code => $single_service) {
            if ($single_service->getName() === $service->getName()) {
                return $service_code;
            }
        }
    }
}
