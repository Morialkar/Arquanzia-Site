<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{{ $title }}</title>
    <subtitle>Les parutions de l’univers d’Arquanzia.</subtitle>
    {{-- L'identifiant doit rester stable pour une même sélection : c'est lui qui dit au
         lecteur qu'il s'agit du même flux d'une fois sur l'autre. --}}
    <id>{{ $selfUrl }}</id>
    <link rel="self" type="application/atom+xml" href="{{ $selfUrl }}"/>
    <link rel="alternate" type="text/html" href="{{ route('home') }}"/>
    <updated>{{ $updated->toAtomString() }}</updated>
    <author><name>Créations Sortilège</name></author>
    <rights>Tous droits réservés — l’univers d’Arquanzia n’est couvert par aucune licence de réutilisation.</rights>
    <generator uri="{{ route('home') }}">Arquanzia</generator>

    @foreach($entries as $entry)
    <entry>
        <title>{{ $entry['title'] }}</title>
        <id>{{ $entry['url'] }}</id>
        <link rel="alternate" type="text/html" href="{{ $entry['url'] }}"/>
        <published>{{ $entry['published']->toAtomString() }}</published>
        <updated>{{ $entry['updated']->toAtomString() }}</updated>
        <category term="{{ $entry['category'] }}"/>
        @if($entry['summary'])
        <summary type="text">{{ $entry['summary'] }}</summary>
        @endif
        @if($entry['content'])
        <content type="html">{{ $entry['content'] }}</content>
        @endif
    </entry>
    @endforeach
</feed>
