@extends('layouts.student')

@section('title', 'Mengerjakan ' . $tryout->title)

@section('content')
    <section class="attempt-header">
        <div>
            <p class="eyebrow">TRY OUT ONLINE</p>
            <h1>{{ $tryout->title }}</h1>
            <p>{{ $tryout->subject->code }} · {{ $tryout->subject->name }}</p>
        </div>
        <div class="attempt-timer" aria-live="polite">
            <span>Sisa waktu</span>
            <strong id="countdown">--:--:--</strong>
        </div>
    </section>

    <div class="attempt-layout" data-attempt-root>
        <aside class="attempt-navigation">
            <h2>Navigasi soal</h2>
            <div class="question-number-grid">
                @foreach ($tryout->questions as $index => $question)
                    <button
                        class="question-number {{ $answers->get($question->id)?->tryout_question_option_id ? 'is-answered' : '' }}"
                        type="button"
                        data-question-target="{{ $index }}"
                        aria-label="Soal {{ $question->order_number }}"
                    >{{ $question->order_number }}</button>
                @endforeach
            </div>
            <p class="attempt-legend"><span class="legend-dot"></span> Sudah dijawab</p>
        </aside>

        <section class="student-card attempt-question-card">
            @foreach ($tryout->questions as $index => $question)
                @php($selectedOptionId = $answers->get($question->id)?->tryout_question_option_id)
                <article class="attempt-question" data-question-panel="{{ $index }}" @if ($index !== 0) hidden @endif>
                    <p class="question-count">Soal {{ $question->order_number }} dari {{ $tryout->questions->count() }}</p>
                    <h2>{{ $question->question_text }}</h2>

                    <div class="option-list">
                        @foreach ($question->options as $option)
                            <label class="attempt-option">
                                <input
                                    type="radio"
                                    name="question_{{ $question->id }}"
                                    value="{{ $option->id }}"
                                    data-question-id="{{ $question->id }}"
                                    @checked($selectedOptionId === $option->id)
                                >
                                <span><b>{{ $option->option_label }}.</b> {{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>

                    <p class="save-status" data-save-status="{{ $question->id }}"></p>
                    <div class="attempt-actions">
                        <button class="button button-secondary" type="button" data-previous @disabled($index === 0)>Soal sebelumnya</button>
                        <button class="button button-primary" type="button" data-next @disabled($index === $tryout->questions->count() - 1)>Soal berikutnya</button>
                    </div>
                </article>
            @endforeach

            <form method="POST" action="{{ route('student.tryouts.attempts.submit', [$tryout, $attempt]) }}" onsubmit="return confirm('Kumpulkan jawaban sekarang? Jawaban tidak dapat diubah setelah dikumpulkan.');">
                @csrf
                <button class="button button-primary attempt-submit" type="submit">Kumpulkan jawaban</button>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-attempt-root]');
    const panels = [...document.querySelectorAll('[data-question-panel]')];
    const navigationButtons = [...document.querySelectorAll('[data-question-target]')];
    const expiresAt = new Date(@json($attempt->expires_at->toIso8601String())).getTime();
    const answerUrl = @json(route('student.tryouts.attempts.answers.update', [$tryout, $attempt]));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let activeIndex = 0;

    const showQuestion = (index) => {
        activeIndex = Math.max(0, Math.min(index, panels.length - 1));
        panels.forEach((panel, panelIndex) => panel.hidden = panelIndex !== activeIndex);
        navigationButtons.forEach((button, buttonIndex) => button.classList.toggle('is-current', buttonIndex === activeIndex));
    };

    navigationButtons.forEach((button, index) => button.addEventListener('click', () => showQuestion(index)));
    document.querySelectorAll('[data-previous]').forEach((button) => button.addEventListener('click', () => showQuestion(activeIndex - 1)));
    document.querySelectorAll('[data-next]').forEach((button) => button.addEventListener('click', () => showQuestion(activeIndex + 1)));

    document.querySelectorAll('.attempt-option input').forEach((input) => input.addEventListener('change', async () => {
        const status = document.querySelector(`[data-save-status="${input.dataset.questionId}"]`);
        status.textContent = 'Menyimpan jawaban...';

        try {
            const response = await fetch(answerUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    question_id: Number(input.dataset.questionId),
                    option_id: Number(input.value),
                }),
            });

            if (! response.ok) {
                const payload = await response.json();
                throw new Error(payload.message || 'Jawaban gagal disimpan.');
            }

            navigationButtons.find((button) => Number(button.textContent) === Number(
                input.closest('[data-question-panel]').querySelector('.question-count').textContent.match(/\d+/)[0]
            ))?.classList.add('is-answered');
            status.textContent = 'Jawaban tersimpan.';
        } catch (error) {
            status.textContent = error.message;
        }
    }));

    const countdown = document.getElementById('countdown');
    const updateTimer = () => {
        const remaining = Math.max(0, expiresAt - Date.now());
        const totalSeconds = Math.floor(remaining / 1000);
        const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');
        countdown.textContent = `${hours}:${minutes}:${seconds}`;

        if (remaining === 0) {
            window.location.reload();
        }
    };

    showQuestion(0);
    updateTimer();
    window.setInterval(updateTimer, 1000);
});
</script>
@endpush