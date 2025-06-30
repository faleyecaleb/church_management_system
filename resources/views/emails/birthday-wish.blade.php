@component('mail::message')
# Happy Birthday, {{ $member->name }}!

Wishing you a very happy {{ $age }}th birthday from all of us at the church.

May God bless you with a wonderful day and a fantastic year to come.

Thanks,<br>
{{ config('app.name') }}
@endcomponent