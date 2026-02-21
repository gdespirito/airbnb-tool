<x-mail::message>
# Nueva nota de reserva

**Huésped:** {{ $note->reservation->guest_name }}

**Propiedad:** {{ $note->reservation->property?->name ?? 'N/A' }}

**Fechas:** {{ $note->reservation->check_in->format('d/m/Y') }} — {{ $note->reservation->check_out->format('d/m/Y') }}

---

{{ $note->content }}

<x-mail::button :url="config('app.url') . '/reservations/' . $note->reservation_id">
Ver reserva
</x-mail::button>
</x-mail::message>
