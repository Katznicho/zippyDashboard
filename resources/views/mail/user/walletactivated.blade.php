<x-mail::message>
    Hello {{ $user->name }},

    {{$body}}

    Thanks
    {{ config('app.name') }}
</x-mail::message>