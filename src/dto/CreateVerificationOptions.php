<?php
namespace Wigl\WiglSmsPackage\dto;

class CreateVerificationOptions
{
    //Options
    public ?string $locale;
    public ?string $customCode;

    //Signals
    public ?string $deviceId;
    public ?string $devicePlatform;
    public ?string $ip;
    public ?string $appVersion;

    public function __construct(
        ?string $locale = null,
        ?string $customCode = null,
        ?string $deviceId = null,
        ?string $devicePlatform = null,
        ?string $ip = null,
        ?string $appVersion = null
    ) {
        $this->locale = $locale ?? config('services.sms_service.default_options.locale');
        $this->customCode = $customCode;
        $this->deviceId = $deviceId;
        $this->devicePlatform = $devicePlatform;
        $this->ip = $ip;
        $this->appVersion = $appVersion;
    }

    /**
     * Set options.
     *
     * @param string|null $locale
     * @param string|null $customCode
     * @return $this
     */
    public function setOptions(?string $locale = null, ?string $customCode = null): self
    {
        $this->locale = $locale ?? $this->locale;
        $this->customCode = $customCode ?? $this->customCode;
        return $this;
    }

    /**
     * Set signals.
     *
     * @param string|null $deviceId
     * @param string|null $devicePlatform
     * @param string|null $ip
     * @param string|null $appVersion
     * @return $this
     */
    public function setSignals(
        ?string $deviceId = null,
        ?string $devicePlatform = null,
        ?string $ip = null,
        ?string $appVersion = null
    ): self {
        $this->deviceId = $deviceId ?? $this->deviceId;
        $this->devicePlatform = $devicePlatform ?? $this->devicePlatform;
        $this->ip = $ip ?? $this->ip;
        $this->appVersion = $appVersion ?? $this->appVersion;
        return $this;
    }

    /**
     * Get locale.
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Get custom code.
     */
    public function getCustomCode(): ?string
    {
        return $this->customCode;
    }

    /**
     * Get device ID.
     */
    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    /**
     * Get device platform.
     */
    public function getDevicePlatform(): ?string
    {
        return $this->devicePlatform;
    }

    /**
     * Get IP address.
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Get app version.
     */
    public function getAppVersion(): ?string
    {
        return $this->appVersion;
    }

    public function toArray(): array
    {
        return [
            'options' => array_filter([
                'locale' => $this->locale,
                'custom_code' => $this->customCode,
            ], fn($value) => $value !== null),

            'signals' => array_filter([
                'app_version' => $this->appVersion,
                'device_id' => $this->deviceId,
                'device_platform' => $this->devicePlatform,
                'ip' => $this->ip,
            ], fn($value) => $value !== null),
        ];
    }
}
