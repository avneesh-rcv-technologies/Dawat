<?php

namespace Modules\Restaurant\Enums;
enum OnboardingStep: string
{
    case REGISTRATION = 'registration';
    case MENU = 'menu';
    case BANKING = 'banking';
    case TAX = 'tax';
    case KYC = 'kyc';

    public function label(): string
    {
        return match ($this) {
            self::REGISTRATION => 'Restaurant Registration',
            self::MENU => 'Menu Details',
            self::BANKING => 'Banking Information',
            self::TAX => 'Tax Information',
            self::KYC => 'KYC Documents',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::REGISTRATION => 1,
            self::MENU => 2,
            self::BANKING => 3,
            self::TAX => 4,
            self::KYC => 5,
        };
    }

    /**
     * Tailwind CSS color classes.
     */
    public function color(): string
    {
        return match ($this) {
            self::REGISTRATION => 'blue',
            self::MENU => 'violet',
            self::BANKING => 'emerald',
            self::TAX => 'amber',
            self::KYC => 'indigo',
        };
    }

    /**
     * Background, text and border classes for the step icon.
     */
    public function styles(): array
    {
        return match ($this) {
            self::REGISTRATION => [
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-600',
                'border' => 'border-blue-200',
            ],
            self::MENU => [
                'bg' => 'bg-violet-50',
                'text' => 'text-violet-600',
                'border' => 'border-violet-200',
            ],
            self::BANKING => [
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-600',
                'border' => 'border-emerald-200',
            ],
            self::TAX => [
                'bg' => 'bg-amber-50',
                'text' => 'text-amber-600',
                'border' => 'border-amber-200',
            ],
            self::KYC => [
                'bg' => 'bg-indigo-50',
                'text' => 'text-indigo-600',
                'border' => 'border-indigo-200',
            ],
        };
    }

    /**
     * Inline SVG icons.
     */
    public function icon(): string
    {
        return match ($this) {
            self::REGISTRATION => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
    <path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01M16 14v.01M16 17v.01"/>
</svg>
SVG,
            self::MENU => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 2v7a4 4 0 0 0 4 4h1V2M6 2v20"/>
    <path d="M17 2v20M17 2c-3 3-3 8 0 11h3V2z"/>
</svg>
SVG,
            self::BANKING => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    <path d="m3 10 9-7 9 7H3zM5 10v9M9 10v9M15 10v9M19 10v9M3 21h18M2 19h20"/>
</svg>
SVG,
            self::TAX => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    <path d="M7 3h7l5 5v13H7z"/>
    <path d="M14 3v5h5M10 13h6M10 17h6"/>
    <path d="M4 7v14"/>
</svg>
SVG,
            self::KYC => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/>
    <path d="m9 12 2 2 4-4"/>
</svg>
SVG,
        };
    }

    public static function ordered(): array
    {
        return [
            self::REGISTRATION,
            self::MENU,
            self::BANKING,
            self::TAX,
            self::KYC,
        ];
    }


    public static function canSkip(): array
    {
        return [
            self::MENU,
        ];
    }

    public static function required(): array
    {
        return array_values(array_filter(self::ordered(), fn (self $step) =>
            !in_array($step, self::canSkip(), true)
        ));
    }
}

