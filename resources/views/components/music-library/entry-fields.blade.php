@props(['form', 'scanWarning' => null])

<div class="space-y-4">
    <flux:input wire:model="form.name" label="Name" required />
    <flux:input wire:model="form.sort_key" label="Sort key" required />
    <flux:textarea wire:model="form.description" label="Description" />
    <div class="grid grid-cols-3 gap-4">
        <flux:select wire:model="form.tradition" label="Tradition">
            <flux:select.option value="">None</flux:select.option>
            @foreach (\App\Enums\MusicTradition::cases() as $tradition)
                <flux:select.option :value="$tradition->value">{{ $tradition->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model="form.tone" label="Tone">
            <flux:select.option value="">None</flux:select.option>
            @foreach (range(1, 8) as $tone)
                <flux:select.option :value="$tone">{{ $tone }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:separator />

    <flux:select wire:model.live="form.parts" label="Number of parts" description="Choose this first; it decides which files are looked for.">
        <flux:select.option value="">Choose...</flux:select.option>
        @foreach (range(1, 4) as $parts)
            <flux:select.option :value="$parts">{{ $parts }}</flux:select.option>
        @endforeach
    </flux:select>
    <flux:field>
        <flux:label>S3 path</flux:label>
        <flux:description>
            For example, <code>divine-liturgy/second-antiphon/only-begotten</code>. Leave off the file extension (no <code>.pdf</code> or <code>.mp3</code>).
        </flux:description>
        <div class="flex gap-2">
            <flux:input wire:model="form.s3_path" placeholder="divine-liturgy/second-antiphon/only-begotten" :disabled="blank($form['parts'])" class="flex-1" />
            <flux:button wire:click="populateFromPath" :disabled="blank($form['parts'])" icon="magnifying-glass">Populate</flux:button>
        </div>
        <flux:error name="form.s3_path" />
    </flux:field>
    @if ($scanWarning)
        <flux:callout variant="warning" icon="exclamation-triangle" :heading="$scanWarning" />
    @endif

    <flux:input wire:model="form.icon_path" label="Image" />
    <flux:input wire:model="form.sheet_music_pdf_path" label="Sheet music PDF" />
    <flux:input wire:model="form.note_playback_path" label="Note playback" />
    @php($partFields = match ((int) $form['parts']) {
        2 => ['s' => 'Melody', 'b' => 'Ison'],
        3, 4 => ['s' => 'Soprano', 'a' => 'Alto', 't' => 'Tenor', 'b' => 'Bass'],
        default => [],
    })
    @if ($partFields !== [])
        <div class="grid grid-cols-2 gap-4">
            @foreach ($partFields as $letter => $label)
                <flux:input wire:model="form.note_playback_{{ $letter }}_path" :label="$label.' playback'" />
            @endforeach
        </div>
    @endif
    <flux:input wire:model="form.example_mp3_path" label="Example MP3" />
    <flux:input wire:model="form.example_youtube_url" label="Example YouTube URL" type="url" />
</div>
