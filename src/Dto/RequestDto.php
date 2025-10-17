<?php

declare(strict_types=1);

namespace StickleApp\Core\Dto;

use Illuminate\Support\Facades\Date;
use Carbon\Carbon;

readonly class RequestDto
{
    /**
     * @param  ?array<string, mixed>  $properties
     */
    public function __construct(
        public string $type,
        public string $model_class,
        public string $object_uid,
        public string $session_uid,
        public Carbon $timestamp,
        public ModelDto $model,
        public ?string $ip_address,
        public ?array $properties,
        public ?LocationDataDto $location_data,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            model_class: $data['model_class'],
            object_uid: $data['object_uid'],
            session_uid: $data['session_uid'],
            timestamp: $data['timestamp'] instanceof Carbon ? $data['timestamp'] : Date::parse($data['timestamp']),
            model: $data['model'] instanceof ModelDto ? $data['model'] : ModelDto::fromArray($data['model']),
            ip_address: $data['ip_address'],
            properties: $data['properties'] ?? null,
            location_data: isset($data['location_data']) ? LocationDataDto::fromArray($data['location_data']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'model_class' => $this->model_class,
            'object_uid' => $this->object_uid,
            'session_uid' => $this->session_uid,
            'ip_address' => $this->ip_address,
            'properties' => $this->properties,
            'timestamp' => $this->timestamp,
            'location_data' => $this->location_data?->toArray(),
            'model' => $this->model->toArray(),
        ];
    }
}
