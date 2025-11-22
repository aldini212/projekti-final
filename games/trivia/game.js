document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('reactionArea');
    const ctx = canvas.getContext('2d');
    const btnStart = document.getElementById('btnStartReaction');
    const phaseLabel = document.getElementById('phaseLabel');
    const roundLabel = document.getElementById('roundLabel');
    const lastTimeEl = document.getElementById('lastTime');
    const avgTimeEl = document.getElementById('avgTime');

    function resizeCanvas() {
        const wrapper = canvas.parentElement;
        const rect = wrapper.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    let phase = 1;           // 1 static, 2 moving, 3 teleport
    let round = 0;
    const totalRounds = 5;
    let times = [];
    let block = null;
    let showing = false;
    let showTime = 0;
    let animId = null;

    function resetState() {
        phase = 1;
        round = 0;
        times = [];
        block = null;
        showing = false;
        cancelAnimationFrame(animId);
        clearCanvas();
        updateHud();
    }

    function updateHud(lastTime) {
        const phaseNames = {
            1: 'Faza 1 - Blloqe statike',
            2: 'Faza 2 - Blloqe lëvizin',
            3: 'Faza 3 - Blloqe teleportuese'
        };
        phaseLabel.textContent = phaseNames[phase] || '-';
        roundLabel.textContent = round;
        if (lastTime != null) {
            lastTimeEl.textContent = Math.round(lastTime);
        }
        if (times.length) {
            const avg = times.reduce((a, b) => a + b, 0) / times.length;
            avgTimeEl.textContent = Math.round(avg);
        } else {
            avgTimeEl.textContent = '-';
        }
    }

    function clearCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function spawnBlock() {
        const size = 50;
        const margin = 20;
        const x = margin + Math.random() * (canvas.width - size - margin * 2);
        const y = margin + Math.random() * (canvas.height - size - margin * 2);

        block = {
            x, y,
            size,
            // Phase 2: slower movement for easier tracking
            vx: (phase === 2 ? (Math.random() * 0.08 + 0.03) * (Math.random() < 0.5 ? -1 : 1) : 0),
            vy: (phase === 2 ? (Math.random() * 0.08 + 0.03) * (Math.random() < 0.5 ? -1 : 1) : 0),
            nextTeleport: performance.now() + 700 + Math.random() * 500
        };
        showing = true;
        showTime = performance.now();
    }

    function drawBlock() {
        if (!block) return;
        ctx.save();
        ctx.fillStyle = phase === 1 ? '#22c55e' : phase === 2 ? '#eab308' : '#ef4444';
        ctx.shadowColor = 'rgba(0,0,0,0.5)';
        ctx.shadowBlur = 12;
        ctx.fillRect(block.x, block.y, block.size, block.size);
        ctx.restore();
    }

    function updateBlock(delta, now) {
        if (!block) return;
        if (phase === 2) {
            block.x += block.vx * delta;
            block.y += block.vy * delta;
            if (block.x <= 0 || block.x + block.size >= canvas.width) block.vx *= -1;
            if (block.y <= 0 || block.y + block.size >= canvas.height) block.vy *= -1;
        } else if (phase === 3 && now >= block.nextTeleport) {
            const size = block.size;
            const margin = 20;
            block.x = margin + Math.random() * (canvas.width - size - margin * 2);
            block.y = margin + Math.random() * (canvas.height - size - margin * 2);
            block.nextTeleport = now + 600 + Math.random() * 600;
        }
    }

    function loop(now) {
        clearCanvas();
        const delta = now - showTime;
        updateBlock(delta, now);
        drawBlock();
        animId = requestAnimationFrame(loop);
    }

    function handleClick(e) {
        if (!showing || !block) return;
        const rect = canvas.getBoundingClientRect();
        const mx = e.clientX - rect.left;
        const my = e.clientY - rect.top;

        if (mx >= block.x && mx <= block.x + block.size &&
            my >= block.y && my <= block.y + block.size) {
            const reaction = performance.now() - showTime;
            times.push(reaction);
            round++;
            updateHud(reaction);

            if (round >= totalRounds) {
                // Move to next phase or end
                if (phase < 3) {
                    phase++;
                    round = 0;
                    alert('Kalojmë në fazën tjetër!');
                } else {
                    const avg = times.reduce((a, b) => a + b, 0) / times.length;
                    alert('Testi përfundoi! Mesatarja juaj: ' + Math.round(avg) + ' ms');
                    canvas.removeEventListener('click', handleClick);
                    btnStart.disabled = false;
                    showing = false;
                    block = null;
                    cancelAnimationFrame(animId);
                    return;
                }
            }

            // Spawn next block
            block = null;
            showing = false;
            setTimeout(() => {
                spawnBlock();
                showTime = performance.now();
            }, 500 + Math.random() * 700);
        }
    }

    btnStart.addEventListener('click', () => {
        resetState();
        updateHud();
        canvas.addEventListener('click', handleClick);
        btnStart.disabled = true;

        setTimeout(() => {
            phase = 1;
            updateHud();
            spawnBlock();
            showTime = performance.now();
            animId = requestAnimationFrame(loop);
        }, 800);
    });
});