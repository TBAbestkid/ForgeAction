window.DeathManager = (() => {
    const frames = [
        '/assets/images/death/death-skull-01-fire-start.png',
        '/assets/images/death/death-skull-02-eyes.png',
        '/assets/images/death/death-skull-03-emerge.png',
        '/assets/images/death/death-skull-04-laugh-start.png',
        '/assets/images/death/death-skull-05-laugh-open.png',
        '/assets/images/death/death-skull-06-laugh-close.png',
        '/assets/images/death/death-skull-07-final-laugh.png'
    ];

    let running = false;

    function wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function createAudio(src, loop = false, volume = 0.8) {
        const audio = new Audio(src);
        audio.loop = loop;
        audio.volume = volume;
        return audio;
    }

    function stopAudio(audio) {
        if (!audio) return;

        audio.pause();
        audio.currentTime = 0;
    }

    function setFrame(index) {
        const image = document.getElementById('deathSequenceImage');
        if (!image || !frames[index]) return;

        image.src = frames[index];
    }

    async function play() {
        const overlay = document.getElementById('deathSequenceOverlay');
        const image = document.getElementById('deathSequenceImage');

        if (!overlay || !image || running) return;

        running = true;
        overlay.classList.add('is-active');

        const fireSound = createAudio('/assets/audio/death/fireSound.mp3', true, 0.55);
        const evilLaugh = createAudio('/assets/audio/death/evilLaugh.mp3', false, 0.9);

        try {
            await fireSound.play().catch(() => null);

            for (const frame of [0, 1, 2, 3]) {
                setFrame(frame);
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
                    setFrame(frame);
                    await wait(115);
                }
            }

            clearTimeout(fallbackTimer);

            for (const frame of [2, 1, 0]) {
                setFrame(frame);
                await wait(100);
            }
        } finally {
            stopAudio(fireSound);
            stopAudio(evilLaugh);
            overlay.classList.remove('is-active');
            image.removeAttribute('src');
            running = false;
        }
    }

    return {
        play
    };
})();
