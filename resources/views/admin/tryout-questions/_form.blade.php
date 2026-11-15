@php
    $optionValues = isset($question)
        ? $question->options->keyBy('option_label')
        : collect();
    $correctLabel = old('correct_option', $optionValues->firstWhere('is_correct', true)?->option_label ?? 'A');
@endphp
<div class="form-grid">
    <div class="form-group form-group-full">
        <label for="question_text">Pertanyaan</label>
        <textarea id="question_text" name="question_text" rows="5" required>{{ old('question_text', $question->question_text ?? '') }}</textarea>
    </div>
    <div class="form-group">
        <label for="points">Bobot Nilai</label>
        <input id="points" type="number" min="1" name="points" value="{{ old('points', $question->points ?? 1) }}" required>
    </div>
    <div class="form-group">
        <label for="order_number">Urutan Soal</label>
        <input id="order_number" type="number" min="1" name="order_number" value="{{ old('order_number', $question->order_number ?? ($tryout->questions()->max('order_number') + 1)) }}" required>
    </div>
    <div class="form-group form-group-full">
        <label>Pilihan Jawaban</label>
        @foreach (['A', 'B', 'C', 'D'] as $optionIndex => $label)
            @php($option = $optionValues->get($label))
            <div class="form-grid">
                <div class="form-group">
                    <label for="option_{{ $label }}">Pilihan {{ $label }}</label>
                    <input type="hidden" name="options[{{ $optionIndex }}][option_label]" value="{{ $label }}">
                    <input id="option_{{ $label }}" name="options[{{ $optionIndex }}][option_text]" value="{{ old("options.{$optionIndex}.option_text", $option?->option_text) }}" required>
                    <input type="hidden" name="options[{{ $optionIndex }}][order_number]" value="{{ $optionIndex + 1 }}">
                    <input type="hidden" name="options[{{ $optionIndex }}][is_correct]" value="0">
                </div>
                <div class="form-group">
                    <label>
                        <input type="radio" name="correct_option" value="{{ $label }}" @checked($correctLabel === $label)>
                        Jawaban benar
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>
<script>
    document.querySelectorAll('input[name="correct_option"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('input[name$="[is_correct]"]').forEach((input) => input.value = '0');
            document.querySelector(`input[name="options[${radio.value.charCodeAt(0) - 65}][is_correct]"]`).value = '1';
        });
    });
    document.querySelector('input[name="correct_option"]:checked')?.dispatchEvent(new Event('change'));
</script>
