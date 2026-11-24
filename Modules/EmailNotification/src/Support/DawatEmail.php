<?php

namespace Modules\EmailNotification\Support;

/**
 * Builds email template HTML in the shared Dawat (onboarding-step) design.
 *
 * Every value may contain {{placeholder}} tokens; they are kept verbatim and
 * replaced at send time by NotificationTemplate::compile().
 *
 * Spec keys:
 *  - title       <title> text (defaults to headline without markup)
 *  - preheader   hidden inbox preview text
 *  - badge       small pill above the headline, e.g. 'ORDER UPDATE'
 *  - headline    large serif heading; may contain <br>. An orange full stop is appended
 *                unless it already ends with punctuation or 'dot' => false.
 *  - greeting    e.g. 'Hello {{customer_name}},'
 *  - intro       string|string[] paragraphs (HTML allowed)
 *  - blocks      list of blocks:
 *                  ['details', title, rows => [label => value], footer => [label => value]]
 *                  ['code',    label, value, caption]
 *                  ['callout', title, body]
 *                  ['list',    title, items => [...]]
 *                  ['text',    body]
 *                Each block is ['type' => ..., ...] (see layout view).
 *  - buttons     [['label' => ..., 'url' => ...], ...]; the first is the primary button
 *  - notes       string|string[] small paragraphs after the buttons
 *  - signoff     line above "The Dawat Team" (default 'See you at the table,')
 *  - footer      reason line under the card ("You received this email because ...")
 *  - year        defaults to '{{current_year}}'
 */
class DawatEmail
{
    public static function render(array $spec): string
    {
        $headline = (string) ($spec['headline'] ?? '');
        $dot = $spec['dot'] ?? ! preg_match('/[.!?…]\s*$/u', strip_tags($headline));

        return SeederEmailView::make('emailnotification::emails.layout', [
            'title' => $spec['title'] ?? trim(strip_tags(str_replace('<br>', ' ', $headline))).' — Dawat',
            'preheader' => $spec['preheader'] ?? null,
            'badge' => $spec['badge'] ?? null,
            'headline' => $headline,
            'headlineDot' => (bool) $dot,
            'greeting' => $spec['greeting'] ?? null,
            'intro' => array_values((array) ($spec['intro'] ?? [])),
            'blocks' => array_values($spec['blocks'] ?? []),
            'buttons' => array_values(array_filter($spec['buttons'] ?? [])),
            'notes' => array_values((array) ($spec['notes'] ?? [])),
            'signoff' => $spec['signoff'] ?? 'See you at the table,',
            'footer' => $spec['footer'] ?? null,
            'year' => $spec['year'] ?? '{{current_year}}',
        ])->render();
    }

    public static function details(?string $title, array $rows, array $footer = []): array
    {
        return ['type' => 'details', 'title' => $title, 'rows' => $rows, 'footer' => $footer];
    }

    public static function code(string $label, string $value, ?string $caption = null, string $spacing = '6px'): array
    {
        return ['type' => 'code', 'label' => $label, 'value' => $value, 'caption' => $caption, 'spacing' => $spacing];
    }

    public static function callout(?string $title, string $body): array
    {
        return ['type' => 'callout', 'title' => $title, 'body' => $body];
    }

    public static function list(?string $title, array $items): array
    {
        return ['type' => 'list', 'title' => $title, 'items' => $items];
    }

    public static function text(string $body): array
    {
        return ['type' => 'text', 'body' => $body];
    }

    public static function button(string $label, string $url): array
    {
        return ['label' => $label, 'url' => $url];
    }
}
