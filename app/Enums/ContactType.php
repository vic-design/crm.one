<?php

namespace App\Enums;

enum ContactType: string
{
    case PHONE = 'phone';
    case TELEGRAM = 'telegram';
    case WHATSAPP = 'whatsapp';
    case SKYPE = 'skype';
    case OTHER = 'other';

    /**
     * Summary of label
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PHONE => 'Телефон',
            self::TELEGRAM => 'Telegram',
            self::WHATSAPP => 'WhatsApp',
            self::SKYPE => 'Skype',
            self::OTHER => 'Другое',
        };
    }

    public function needsDescription(): bool
    {
        return $this === self::OTHER;
    }

    public function valuePlaceholder(): string
    {
        return match($this) {
            self::PHONE => '+7 (999) 000-00-00',
            self::TELEGRAM => '@username',
            self::WHATSAPP => '+7 (999) 000-00-00',
            self::SKYPE => 'username',
            self::OTHER => 'Значение',
        };
    }
}
