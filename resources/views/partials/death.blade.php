@php
    $deathEnabled = $enabled ?? true;
    $deathPreview = $deathPreview ?? false;
@endphp

@if ($deathPreview)
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>YOU DIED - ForgeAction</title>
@endif

<style>
    @if ($deathPreview)
        body {
            margin: 0;
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(circle at center, rgba(80, 12, 8, 0.24), rgba(0, 0, 0, 0.92)),
                #111;
            font-family: sans-serif;
        }
    @endif

    @keyframes lowHealthPulse {
        0%,
        100% {
            opacity: var(--low-health-opacity-min, 0.1);
        }

        50% {
            opacity: var(--low-health-opacity-max, 0.18);
        }
    }

    #lowHealthOverlay {
        position: fixed;
        inset: 0;
        z-index: 4;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.35s ease;
        background:
            radial-gradient(circle at center, rgba(180, 0, 0, 0) 52%, rgba(180, 0, 0, 0.14) 82%, rgba(180, 0, 0, 0.32) 100%),
            linear-gradient(90deg, rgba(170, 0, 0, 0.22), rgba(170, 0, 0, 0) 16%, rgba(170, 0, 0, 0) 84%, rgba(170, 0, 0, 0.22));
    }

    #lowHealthOverlay.is-wounded {
        --low-health-opacity-min: 0.08;
        --low-health-opacity-max: 0.18;
        opacity: 0.14;
        animation: lowHealthPulse 2.2s ease-in-out infinite;
    }

    #lowHealthOverlay.is-critical {
        --low-health-opacity-min: 0.22;
        --low-health-opacity-max: 0.38;
        opacity: 0.3;
        animation: lowHealthPulse 1.15s ease-in-out infinite;
        background:
            radial-gradient(circle at center, rgba(220, 0, 0, 0) 48%, rgba(220, 0, 0, 0.18) 78%, rgba(220, 0, 0, 0.34) 100%),
            linear-gradient(90deg, rgba(220, 0, 0, 0.26), rgba(220, 0, 0, 0) 20%, rgba(220, 0, 0, 0) 80%, rgba(220, 0, 0, 0.26));
    }

    #lowHealthOverlay.is-dead {
        opacity: 0.42;
        animation: none;
        background:
            radial-gradient(circle at center, rgba(20, 20, 20, 0) 46%, rgba(90, 90, 90, 0.2) 78%, rgba(20, 20, 20, 0.56) 100%),
            linear-gradient(90deg, rgba(20, 20, 20, 0.36), rgba(20, 20, 20, 0) 18%, rgba(20, 20, 20, 0) 82%, rgba(20, 20, 20, 0.36));
        filter: grayscale(1);
    }

    #deathSequenceOverlay {
        position: fixed;
        inset: 0;
        z-index: 60;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        background: radial-gradient(circle at center, rgba(0, 0, 0, 0.18), rgba(0, 0, 0, 0.72));
        transition: opacity 0.12s ease, visibility 0.12s ease;
    }

    #deathSequenceOverlay.is-active {
        opacity: 1;
        visibility: visible;
    }

    #deathSequenceImage {
        width: min(58vw, 520px);
        max-height: 78vh;
        object-fit: contain;
        filter: drop-shadow(0 0 34px rgba(255, 70, 20, 0.45)) drop-shadow(0 0 70px rgba(0, 0, 0, 0.9));
        transform: scale(0.98);
    }

    @if ($deathPreview)
        #deathPreviewControls {
            position: fixed;
            left: 50%;
            bottom: 32px;
            z-index: 80;
            transform: translateX(-50%);
        }

        #deathPreviewPlay {
            border: 1px solid rgba(255, 110, 64, 0.55);
            border-radius: 8px;
            padding: 10px 18px;
            background: rgba(15, 15, 15, 0.82);
            color: #f5d0a8;
            cursor: pointer;
            font-size: 0.95rem;
            letter-spacing: 0;
            box-shadow: 0 0 24px rgba(255, 70, 20, 0.24);
        }
    @endif
</style>

@if ($deathPreview)
    </head>
    <body>
@endif

@if ($deathEnabled)
    <div id="lowHealthOverlay" class="{{ $deathPreview ? 'is-dead' : '' }}" aria-hidden="true"></div>
    <div id="deathSequenceOverlay" class="{{ $deathPreview ? 'is-active' : '' }}" aria-hidden="true">
        <img id="deathSequenceImage"
            src="{{ $deathPreview ? asset('assets/images/death/death-skull-05-laugh-open.png') : '' }}"
            alt="">
    </div>
@endif

@if ($deathPreview)
    <div id="deathPreviewControls">
        <button id="deathPreviewPlay" type="button">Reproduzir morte</button>
    </div>
@endif

@if ($deathPreview)
        <script>
            const frames = [
                '/assets/images/death/death-skull-01-fire-start.png',
                '/assets/images/death/death-skull-02-eyes.png',
                '/assets/images/death/death-skull-03-emerge.png',
                '/assets/images/death/death-skull-04-laugh-start.png',
                '/assets/images/death/death-skull-05-laugh-open.png',
                '/assets/images/death/death-skull-06-laugh-close.png',
                '/assets/images/death/death-skull-07-final-laugh.png'
            ];
            const overlay = document.getElementById('deathSequenceOverlay');
            const image = document.getElementById('deathSequenceImage');
            const playButton = document.getElementById('deathPreviewPlay');
            let running = false;

            function wait(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            function createPreviewAudio(src, loop, volume) {
                const audio = new Audio(src);
                audio.loop = loop;
                audio.volume = volume;
                return audio;
            }

            function stopPreviewAudio(audio) {
                audio.pause();
                audio.currentTime = 0;
            }

            async function playDeathPreview() {
                if (running) return;

                running = true;
                playButton.disabled = true;
                overlay.classList.add('is-active');

                const fireSound = createPreviewAudio('/assets/audio/death/fireSound.mp3', true, 0.55);
                const evilLaugh = createPreviewAudio('/assets/audio/death/evilLaugh.mp3', false, 0.9);

                try {
                    await fireSound.play().catch(() => null);

                    for (const frame of [0, 1, 2, 3]) {
                        image.src = frames[frame];
                        await wait(95);
                    }

                    let laughFinished = false;
                    const fallbackTimer = setTimeout(() => {
                        laughFinished = true;
                    }, 3500);

                    evilLaugh.addEventListener('ended', () => {
                        laughFinished = true;
                    }, { once: true });

                    await evilLaugh.play().catch(() => {
                        laughFinished = true;
                    });

                    while (!laughFinished) {
                        for (const frame of [4, 5, 6]) {
                            if (laughFinished) break;
                            image.src = frames[frame];
                            await wait(115);
                        }
                    }

                    clearTimeout(fallbackTimer);

                    for (const frame of [2, 1, 0]) {
                        image.src = frames[frame];
                        await wait(100);
                    }
                } finally {
                    stopPreviewAudio(fireSound);
                    stopPreviewAudio(evilLaugh);
                    overlay.classList.remove('is-active');
                    document.getElementById('lowHealthOverlay').classList.add('is-dead');
                    playButton.disabled = false;
                    running = false;
                }
            }

            playButton.addEventListener('click', playDeathPreview);
            window.addEventListener('load', playDeathPreview);
        </script>
    </body>
    </html>
@endif
