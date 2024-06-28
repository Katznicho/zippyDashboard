<x-mail::message>
    Hello {{ $user->name }},

    {{$message}}


    Thanks
    {{ config('app.name') }}
</x-mail::message>