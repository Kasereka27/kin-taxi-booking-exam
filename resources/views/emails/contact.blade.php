<x-mail::message>
# Nouveau message de contact

**De :** {{ $senderName }} ({{ $senderEmail }})  
**Sujet :** {{ $subjectLabel }}

---

{{ $body }}

<x-mail::subcopy>
Répondez directement à cet e-mail pour contacter {{ $senderName }}.
</x-mail::subcopy>
</x-mail::message>
