<?php

declare(strict_types=1);

namespace StickleApp\Core\Dto;

readonly class LocationDataDto
{
    /**
     * @param  ?array<float>  $coordinates
     */
    public function __construct(
        public string $ip_address,
        public ?string $city,
        public ?string $country,
        public ?array $coordinates,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ip_address: $data['ip_address'],
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
            coordinates: $data['coordinates'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ip_address' => $this->ip_address,
            'city' => $this->city,
            'country' => $this->country,
            'coordinates' => $this->coordinates,
        ];
    }
}
