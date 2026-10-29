@props(['tag'])

@php
$tagClasses = [
    'agency' => 'bg-blue-100 text-blue-800',
    'admin' => 'bg-gray-100 text-gray-800',
    'agency staff' => 'bg-blue-100 text-blue-800',
    'create' => 'bg-blue-100 text-blue-800',
    'delete' => 'bg-red-100 text-red-800',
    'update' => 'bg-purple-100 text-purple-800',
    'assign' => 'bg-blue-100 text-blue-800',
    'reject' => 'bg-red-100 text-red-800',
    'approve' => 'bg-green-100 text-green-800',
    'login' => 'bg-green-100 text-green-800',
    'view' => 'bg-blue-100 text-blue-800',
    'critical' => 'bg-red-100 text-red-800',
    'high' => 'bg-orange-100 text-orange-800',
    'medium' => 'bg-yellow-100 text-yellow-800',
    'low' => 'bg-green-100 text-green-800',
];

$class = $tagClasses[$tag] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="px-2 py-1 text-xs font-medium {{ $class }} rounded">{{ $tag }}</span>