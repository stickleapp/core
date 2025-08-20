<?php

declare(strict_types=1);

namespace StickleApp\Core\Dto;

readonly class ModelDto
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $model_class,
        public string $object_uid,
        public string $label,
        public array $raw,
        public string $url,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            model_class: $data['model_class'],
            object_uid: $data['object_uid'],
            label: $data['label'],
            raw: $data['raw'],
            url: $data['url'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'model_class' => $this->model_class,
            'object_uid' => $this->object_uid,
            'label' => $this->label,
            'raw' => $this->raw,
            'url' => $this->url,
        ];
    }
}
