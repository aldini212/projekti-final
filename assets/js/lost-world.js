// The Lost World - RPG (Phaser.js MVP)
// Advanced animations: parallax background, animated hero, simple enemy encounters

let lostWorldGame = null;

// Game state
let gameOver = false;
let gameTime = 0;
let deathStats = {
    level: 1,
    enemies: 0,
    time: '0:00'
};

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('lostWorldCanvas');
    const overlay = document.getElementById('lostWorldOverlay');
    const btnStart = document.getElementById('btnStartRpg');
    const btnFullscreen = document.getElementById('btnFullscreen');
    const gameCard = document.getElementById('lostWorldGameCard');

    if (!canvas || !overlay || !btnStart) {
        console.warn('[LostWorld] Missing elements:', {
            canvas: !!canvas,
            overlay: !!overlay,
            btnStart: !!btnStart,
        });
        return;
    }

    // Resize canvas to fit wrapper
    function resizeCanvas() {
        const wrapper = canvas.parentElement;
        const rect = wrapper.getBoundingClientRect();
        const width = rect.width;
        const height = rect.height || (width * 9) / 16;

        canvas.width = width;
        canvas.height = height;
    }

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // Fullscreen toggle
    if (btnFullscreen && gameCard) {
        btnFullscreen.addEventListener('click', () => {
            const doc = document;
            const isFs = doc.fullscreenElement || doc.webkitFullscreenElement || doc.msFullscreenElement;

            if (!isFs) {
                const target = gameCard;
                if (target.requestFullscreen) target.requestFullscreen();
                else if (target.webkitRequestFullscreen) target.webkitRequestFullscreen();
                else if (target.msRequestFullscreen) target.msRequestFullscreen();
                else target.classList.toggle('lostworld-fullscreen');
            } else {
                if (doc.exitFullscreen) doc.exitFullscreen();
                else if (doc.webkitExitFullscreen) doc.webkitExitFullscreen();
                else if (doc.msExitFullscreen) doc.msExitFullscreen();
                else gameCard.classList.toggle('lostworld-fullscreen');
            }

            // Recompute canvas size shortly after mode change
            setTimeout(resizeCanvas, 150);
        });
    }

    // Difficulty (affects enemy HP and damage)
    let difficulty = 'normal'; // 'easy' | 'normal' | 'hard'

    // Basic state (we'll later swap this to backend + game-service.js)
    let state = {
        hp: 100,
        mana: 50,
        xp: 0,
        level: 1,
        gold: 0,
    };

    const hpBar = document.getElementById('rpgHpBar');
    const manaBar = document.getElementById('rpgManaBar');
    const xpBar = document.getElementById('rpgXpBar');
    const levelEl = document.getElementById('rpgLevel');
    const goldEl = document.getElementById('rpgGold');
    const questEl = document.getElementById('rpgCurrentQuest');
    const invEl = document.getElementById('rpgInventory');
    const questLogEl = document.getElementById('rpgQuestLog');
    const playerHpText = document.getElementById('playerHpValue');
    const strengthEl = document.getElementById('rpgStrength');
    const waveEl = document.getElementById('rpgWave');

    // Enemy state (single enemy for now)
    let enemy = {
        x: 0,
        y: 0,
        baseX: 0,
        baseY: 0,
        hp: 80,
        maxHp: 80,
        alive: true,
        attackCooldown: 0,
    };

    // Damage text popups
    const damageTexts = []; // {x,y,text,color,life,vy}

    // Simple quest state
    let quests = {
        introDone: false,
        enemyDefeated: false,
    };

    // Simple equipment and stats
    let strength = 10;
    let swordDamage = 8;
    let bowDamage = 6;
    let wave = 1;

    // Chests on the map (Y will be aligned to ground in draw)
    const chests = [
        { x: 140, y: 0, opened: false, label: 'Shpatë Bronze (+Dmg)', swordBonus: 8, gold: 15 },
        { x: 320, y: 0, opened: false, label: 'Thesar i Vogël (Gold)', swordBonus: 0, gold: 40 },
    ];

    function resetEnemyPosition() {
        const w = canvas.width || 600;
        const h = canvas.height || 340;
        const floorY = h - 32; // bottom of ground band
        const enemyHalf = 13; // visual half-size of enemy sprite
        enemy.baseX = w * 0.75;
        enemy.baseY = floorY - enemyHalf;
        enemy.x = enemy.baseX;
        enemy.y = enemy.baseY;
    }

    function spawnEnemyForWave() {
        resetEnemyPosition();
        enemy.alive = true;
        enemy.attackCooldown = 0;

        // Base HP grows with wave
        let baseHp = 60 + wave * 15;
        if (difficulty === 'easy') baseHp = Math.floor(baseHp * 0.8);
        if (difficulty === 'hard') baseHp = Math.floor(baseHp * 1.2);
        enemy.maxHp = baseHp;
        enemy.hp = enemy.maxHp;

        // Slightly boost strength each few waves
        if (wave > 1 && wave % 3 === 0) {
            strength += 2;
            swordDamage += 1;
            bowDamage += 1;
        }
        updateHud();
        gameOver = false;
    }

    resetEnemyPosition();

    function pushDamageText(x, y, text, color) {
        damageTexts.push({
            x,
            y,
            text,
            color,
            life: 900,
            vy: -0.03,
        });
    }

    function addInventoryItem(label) {
        if (!invEl) return;
        const item = document.createElement('div');
        item.textContent = `• ${label}`;
        invEl.appendChild(item);
    }

    function updateQuestLog() {
        if (!questLogEl) return;
        questLogEl.innerHTML = '';
        const lines = [];
        if (!quests.introDone) {
            lines.push('- [Aktiv] Hapi i parë: Lëviz nëpër pyll për të gjetur një kamp të braktisur.');
        } else if (!quests.enemyDefeated) {
            lines.push('- [Aktiv] Përballu me krijesën mistike pranë kampit.');
        } else {
            lines.push('- [Kryer] Ke mposhtur krijesën e pyllit dhe ke gjetur thesar.');
        }
        questLogEl.innerHTML = lines.map(l => `<div>${l}</div>`).join('');
    }

    function updateHud() {
        if (hpBar) hpBar.style.width = Math.max(0, Math.min(100, state.hp)) + '%';
        if (manaBar) manaBar.style.width = Math.max(0, Math.min(100, (state.mana / 100) * 100)) + '%';
        if (xpBar) xpBar.style.width = Math.max(0, Math.min(100, (state.xp / 100) * 100)) + '%';
        if (levelEl) levelEl.textContent = state.level;
        if (goldEl) goldEl.textContent = state.gold;
        if (playerHpText) playerHpText.textContent = Math.max(0, Math.floor(state.hp));
        if (strengthEl) strengthEl.textContent = strength;
        if (waveEl) waveEl.textContent = wave;
    }

    updateHud();
    updateQuestLog();

    function addXp(amount) {
        state.xp += amount;
        if (state.xp >= 100) {
            state.xp -= 100;
            state.level++;
        }
        updateHud();
    }

    function resetGameState() {
        // Reset core player stats
        state.hp = 100;
        state.mana = 50;
        state.xp = 0;
        state.level = 1;
        state.gold = 0;

        // Reset progression
        strength = 10;
        swordDamage = 8;
        bowDamage = 6;
        wave = 1;

        // Reset world/enemy
        spawnEnemyForWave();
        quests.enemyDefeated = false;
        quests.introDone = false;
        questEl.textContent = 'Gjej kampin e braktisur në pyll.';
        updateQuestLog();
        updateHud();
    }

    function showXpPopup(amount) {
        const popup = document.createElement('div');
        popup.className = 'xp-popup-center';
        popup.textContent = `+${amount} XP`;
        document.body.appendChild(popup);
        setTimeout(() => popup.remove(), 1300);
    }

    function fakeBattleReward() {
        addXp(20 + Math.floor(Math.random() * 15));
        state.gold += 5 + Math.floor(Math.random() * 10);
        state.hp = Math.max(20, state.hp - (10 + Math.floor(Math.random() * 15)));
        updateHud();
    }

    // For now, create a custom animation loop on the canvas (no Phaser yet) so you see motion
    const ctx = canvas.getContext('2d');
    let t = 0;
    let heroX = 120;
    let heroY = 0; // will be snapped to ground on first update
    let heroDir = 'down'; // 'up','down','left','right'

    // Physics / movement
    let heroVy = 0;
    let onGround = true;
    const GRAVITY = 0.0014;
    const JUMP_VELOCITY = -0.5;

    // Combat state
    let swordSwingTime = 0; // ms remaining of swing animation
    const SWORD_SWING_DURATION = 220;
    let swordHitApplied = false;
    const arrows = []; // {x,y,vx,vy,life}

    const keys = {};
    let interactPressed = false; // for E (chest open)

    window.addEventListener('keydown', (e) => { 
        const k = e.key.toLowerCase();
        keys[k] = true; 
        // Prevent page scroll on space when in game
        if (k === ' ' || k === 'spacebar') {
            e.preventDefault();
        }
        if (k === 'e') {
            interactPressed = true;
        }
    });
    window.addEventListener('keyup', (e) => { keys[e.key.toLowerCase()] = false; });

    function handleMovement(delta) {
        const baseSpeed = 0.16;
        const sprintMult = (keys['shift'] || keys['shiftleft'] || keys['shiftright']) ? 1.7 : 1.0;
        const speed = baseSpeed * sprintMult * delta;

        if (keys['w'] || keys['arrowup']) { heroY -= speed; heroDir = 'up'; }
        if (keys['s'] || keys['arrowdown']) { heroY += speed; heroDir = 'down'; }
        if (keys['a'] || keys['arrowleft']) { heroX -= speed; heroDir = 'left'; }
        if (keys['d'] || keys['arrowright']) { heroX += speed; heroDir = 'right'; }

        // Simple jump on J key
        if (keys['j'] && onGround) {
            heroVy = JUMP_VELOCITY;
            onGround = false;
        }

        // Apply gravity
        if (!onGround) {
            heroVy += GRAVITY * delta;
            heroY += heroVy * delta;
        }

        const floorY = canvas.height - 32; // same as draw ground bottom
        const heroHalf = 12; // heroSize / 2
        const heroGroundY = floorY - heroHalf;
        if (heroY >= heroGroundY) {
            heroY = heroGroundY;
            heroVy = 0;
            onGround = true;
        }

        // Looping map horizontally
        const margin = 40;
        const maxX = canvas.width - margin;
        const minX = margin;
        if (heroX > maxX) heroX = minX;
        if (heroX < minX) heroX = maxX;
    }

    function handleAttacks(delta) {
        // Sword: Space = melee swing
        if ((keys[' '] || keys['space']) && swordSwingTime <= 0) {
            swordSwingTime = SWORD_SWING_DURATION;
            swordHitApplied = false;
        }

        if (swordSwingTime > 0) {
            swordSwingTime -= delta;
            if (swordSwingTime < 0) swordSwingTime = 0;
        }

        // Bow: F = fire glowing arrow
        if (keys['f']) {
            // Fire only when key is first pressed (we'll clear it immediately)
            keys['f'] = false;
            const speed = 0.4; // px/ms
            let vx = 0, vy = 0;
            if (heroDir === 'up') vy = -speed;
            else if (heroDir === 'down') vy = speed;
            else if (heroDir === 'left') vx = -speed;
            else if (heroDir === 'right') vx = speed;
            // Default forward if standing
            if (vx === 0 && vy === 0) vy = -speed;

            arrows.push({
                x: heroX,
                y: heroY,
                vx,
                vy,
                life: 1200 // ms
            });
        }

        // Update arrows
        for (let i = arrows.length - 1; i >= 0; i--) {
            const a = arrows[i];
            a.x += a.vx * delta;
            a.y += a.vy * delta;
            a.life -= delta;
            if (a.life <= 0) arrows.splice(i, 1);
        }
    }

    function update(delta) {
        if (gameOver) return false;

        handleMovement(delta);
        handleAttacks(delta);
        
        // Enemy simple AI: small horizontal chase
        if (enemy.alive) {
            const chaseSpeed = difficulty === 'hard' ? 0.08 : difficulty === 'easy' ? 0.04 : 0.06;
            const dx = heroX - enemy.baseX;
            enemy.baseX += Math.sign(dx) * chaseSpeed * delta;
            enemy.x = enemy.baseX;
            enemy.y = enemy.baseY;

            // Combat vs hero
            const ex = enemy.x;
            const ey = enemy.y;
            const dxHero = heroX - ex;
            const dyHero = heroY - ey;
            const distHero = Math.sqrt(dxHero * dxHero + dyHero * dyHero);

            // Sword hit (once per swing) when close
            if (!swordHitApplied && swordSwingTime > 0 && distHero < 42) {
                swordHitApplied = true;
                let dmg = swordDamage + Math.floor(Math.random() * 4);
                if (difficulty === 'easy') dmg = Math.floor(dmg * 1.2);
                if (difficulty === 'hard') dmg = Math.floor(dmg * 0.9);

                enemy.hp -= dmg;
                pushDamageText(ex, ey - 32, `-${dmg}`, '#f97316');
                if (enemy.hp <= 0) {
                    enemy.hp = 0;
                    enemy.alive = false;

                    // XP tiers based on wave
                    let xpReward = 50;
                    if (wave >= 4 && wave <= 7) xpReward = 100;
                    else if (wave >= 8) xpReward = 150;
                    addXp(xpReward);
                    showXpPopup(xpReward);

                    state.gold += 15 + wave * 5;
                    addInventoryItem(`Thesar i valës ${wave}`);
                    quests.enemyDefeated = true;
                    questEl.textContent = 'Ke mposhtur krijesën e pyllit!';
                    updateQuestLog();

                    // Next wave
                    wave++;
                    spawnEnemyForWave();
                }
                updateHud();
            }

            // Arrow hits
            for (let i = arrows.length - 1; i >= 0; i--) {
                const a = arrows[i];
                const dax = a.x - ex;
                const day = a.y - ey;
                const ad = Math.sqrt(dax * dax + day * day);
                if (ad < 30) {
                    arrows.splice(i, 1);
                    let dmg = bowDamage + Math.floor(Math.random() * 4);
                    if (difficulty === 'easy') dmg = Math.floor(dmg * 1.1);
                    if (difficulty === 'hard') dmg = Math.floor(dmg * 0.9);

                    enemy.hp -= dmg;
                    pushDamageText(ex, ey - 32, `-${dmg}`, '#bfdbfe');
                    if (enemy.hp <= 0) {
                        enemy.hp = 0;
                        enemy.alive = false;

                        // XP tiers based on wave
                        let xpReward = 50;
                        if (wave >= 4 && wave <= 7) xpReward = 100;
                        else if (wave >= 8) xpReward = 150;
                        addXp(xpReward);
                        showXpPopup(xpReward);

                        state.gold += 15 + wave * 5;
                        addInventoryItem(`Hark i vjetër i valës ${wave}`);
                        quests.enemyDefeated = true;
                        questEl.textContent = 'Ke mposhtur krijesën e pyllit!';
                        updateQuestLog();

                        // Next wave
                        wave++;
                        spawnEnemyForWave();
                    }
                    updateHud();
                }
            }

            // Enemy attack with cooldown
            if (distHero < 38 && enemy.attackCooldown <= 0 && state.hp > 0) {
                let edmg = 6 + Math.floor(Math.random() * 5);
                if (difficulty === 'hard') edmg = Math.floor(edmg * 1.4);
                if (difficulty === 'easy') edmg = Math.floor(edmg * 0.7);

                state.hp = Math.max(0, state.hp - edmg);
                pushDamageText(heroX, heroY - 28, `-${edmg}`, '#fca5a5');
                updateHud();

                // Check for player death
                if (state.hp <= 0 && !gameOver) {
                    gameOver = true;
                    running = false;
                    setTimeout(() => {
                        if (confirm('You died. Retry?')) {
                            resetGameState();
                            running = true;
                            last = performance.now();
                            requestAnimationFrame(loop);
                        }
                    }, 100);
                    return;
                }

                enemy.attackCooldown = 800; // ms
            }
        }

        // Reduce enemy attack cooldown
        if (enemy.attackCooldown > 0) {
            enemy.attackCooldown -= delta;
            if (enemy.attackCooldown < 0) enemy.attackCooldown = 0;
        }

        // Align chests to floor band and handle interaction
        const floorY = canvas.height - 32;
        const chestHalf = 7; // approx half height of chest sprite
        chests.forEach(chest => {
            chest.y = floorY - chestHalf; // snap visually to ground
        });

        if (interactPressed) {
            interactPressed = false;
            chests.forEach(chest => {
                if (chest.opened) return;
                const dx = heroX - chest.x;
                const dy = heroY - chest.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 40) {
                    chest.opened = true;
                    if (chest.swordBonus && chest.swordBonus > 0) {
                        swordDamage += chest.swordBonus;
                    }
                    state.gold += chest.gold;
                    addInventoryItem(chest.label);
                    pushDamageText(chest.x, chest.y - 20, `+${chest.gold}g`, '#bbf7d0');
                    updateHud();
                }
            });
        }

        // Damage texts (float up & fade)
        for (let i = damageTexts.length - 1; i >= 0; i--) {
            const dt = damageTexts[i];
            dt.life -= delta;
            dt.y += dt.vy * delta;
            if (dt.life <= 0) damageTexts.splice(i, 1);
        }
    }

    function draw() {
        const w = canvas.width;
        const h = canvas.height;

        // Clear
        ctx.clearRect(0, 0, w, h);

        // Blue sky gradient (most of the screen)
        const skyGrad = ctx.createLinearGradient(0, 0, 0, h);
        skyGrad.addColorStop(0, '#60a5fa');
        skyGrad.addColorStop(0.6, '#1d4ed8');
        skyGrad.addColorStop(1, '#0f172a');
        ctx.fillStyle = skyGrad;
        ctx.fillRect(0, 0, w, h);

        // Simple flat ground strip at bottom: grass top + dirt below
        const floorY = h - 32; // bottom of ground band
        const grassHeight = 12;
        const dirtHeight = h - floorY;

        // Dirt
        ctx.fillStyle = '#3f2f1a';
        ctx.fillRect(0, floorY, w, dirtHeight);

        // Grass edge on top of dirt
        ctx.fillStyle = '#22c55e';
        ctx.fillRect(0, floorY - grassHeight, w, grassHeight);

        // Pixel-art hero (16x16 sprite scaled up)
        const bob = Math.sin(t * 0.005) * 2;
        const heroSize = 24; // visual size
        ctx.save();
        ctx.translate(Math.round(heroX), Math.round(heroY + bob));

        // Body
        ctx.fillStyle = '#1f2933';
        ctx.fillRect(-heroSize / 2, -heroSize / 2, heroSize, heroSize);

        // Head
        ctx.fillStyle = '#f4f1de';
        ctx.fillRect(-8, -heroSize / 2 - 8, 16, 10);

        // Eyes depending on direction
        ctx.fillStyle = '#111827';
        if (heroDir === 'up') {
            ctx.fillRect(-5, -heroSize / 2 - 6, 3, 2);
            ctx.fillRect(2, -heroSize / 2 - 6, 3, 2);
        } else if (heroDir === 'down') {
            ctx.fillRect(-5, -heroSize / 2 - 1, 3, 2);
            ctx.fillRect(2, -heroSize / 2 - 1, 3, 2);
        } else if (heroDir === 'left') {
            ctx.fillRect(-6, -heroSize / 2 - 3, 3, 2);
        } else {
            ctx.fillRect(3, -heroSize / 2 - 3, 3, 2);
        }

        // Tunic stripe
        ctx.fillStyle = '#22c55e';
        ctx.fillRect(-heroSize / 2, 0, heroSize, 4);

        // Sword swing (simple pixel arc)
        if (swordSwingTime > 0) {
            const p = 1 - swordSwingTime / SWORD_SWING_DURATION; // 0..1
            ctx.fillStyle = '#e5e7eb';
            const reach = heroSize + 6;
            if (heroDir === 'up') {
                ctx.fillRect(-2 - p * 4, -reach, 8, 10);
            } else if (heroDir === 'down') {
                ctx.fillRect(-2 - p * 4, reach - 10, 8, 10);
            } else if (heroDir === 'left') {
                ctx.fillRect(-reach, -4 - p * 4, 10, 8);
            } else {
                ctx.fillRect(reach - 10, -4 - p * 4, 10, 8);
            }
        }

        ctx.restore();

        // Enemy: pixelated creature with HP bar (if alive)
        if (enemy.alive) {
            const ex = enemy.x;
            const ey = enemy.y;
            const eSize = 26;
            ctx.save();
            ctx.translate(Math.round(ex), Math.round(ey));

            // Body
            ctx.fillStyle = '#7f1d1d';
            ctx.fillRect(-eSize / 2, -eSize / 2, eSize, eSize);

            // Eyes
            ctx.fillStyle = '#fecaca';
            ctx.fillRect(-6, -8, 4, 4);
            ctx.fillRect(2, -8, 4, 4);

            // Mouth
            ctx.fillStyle = '#111827';
            ctx.fillRect(-6, 0, 12, 4);

            // HP bar above
            const hpWidth = 30;
            const hpRatio = Math.max(0, enemy.hp / enemy.maxHp);
            ctx.fillStyle = '#111827';
            ctx.fillRect(-hpWidth / 2, -eSize / 2 - 10, hpWidth, 4);
            ctx.fillStyle = '#f97316';
            ctx.fillRect(-hpWidth / 2, -eSize / 2 - 10, hpWidth * hpRatio, 4);

            ctx.restore();
        }

        // Chests
        chests.forEach(chest => {
            ctx.save();
            ctx.translate(Math.round(chest.x), Math.round(chest.y));
            if (!chest.opened) {
                // Closed chest
                ctx.fillStyle = '#92400e';
                ctx.fillRect(-10, -8, 20, 14);
                ctx.fillStyle = '#facc15';
                ctx.fillRect(-8, -10, 16, 4);
                ctx.fillRect(-2, -4, 4, 4); // lock
            } else {
                // Open chest
                ctx.fillStyle = '#78350f';
                ctx.fillRect(-10, -4, 20, 10);
                ctx.fillStyle = '#facc15';
                ctx.fillRect(-10, -12, 20, 6);
            }
            ctx.restore();
        });

        // Arrows (pixel bolts)
        ctx.fillStyle = '#e5e7eb';
        arrows.forEach(a => {
            ctx.save();
            ctx.translate(Math.round(a.x), Math.round(a.y));
            if (Math.abs(a.vx) > Math.abs(a.vy)) {
                // Horizontal arrow
                const dir = a.vx > 0 ? 1 : -1;
                ctx.fillRect(-6 * dir, -2, 8 * dir, 4);
            } else {
                // Vertical arrow
                const dir = a.vy > 0 ? 1 : -1;
                ctx.fillRect(-2, -6 * dir, 4, 8 * dir);
            }
            ctx.restore();
        });

        // Damage texts
        damageTexts.forEach(dt => {
            ctx.fillStyle = dt.color;
            ctx.font = '12px monospace';
            ctx.fillText(dt.text, dt.x, dt.y);
        });

        // Simple hint text
        ctx.fillStyle = 'rgba(255,255,255,0.9)';
        ctx.font = '11px monospace';
        ctx.fillText('WASD lëvizje • Shift sprint • J kërcim • Space sword • F bow • E chest', 16, h - 10);
    }

    let last = performance.now();
    let running = false;

    function loop(now) {
        if (!running) return;
        const delta = now - last;
        last = now;
        t += delta;
        update(delta);
        draw();
        requestAnimationFrame(loop);
    }

    console.log('[LostWorld] RPG script initialized, waiting for Start Adventure click');

    btnStart.addEventListener('click', () => {
        if (!running) {
            // Simple difficulty selector
            const choice = (prompt('Zgjidh vështirësinë: 1 = Easy, 2 = Normal, 3 = Hard', '2') || '2').trim();
            if (choice === '1') difficulty = 'easy';
            else if (choice === '3') difficulty = 'hard';
            else difficulty = 'normal';

            // Scale enemy stats by difficulty
            if (difficulty === 'easy') {
                enemy.maxHp = 60;
            } else if (difficulty === 'hard') {
                enemy.maxHp = 110;
            } else {
                enemy.maxHp = 80;
            }
            enemy.hp = enemy.maxHp;

            running = true;
            last = performance.now();
            questEl.textContent = 'Gjej kampin e braktisur në pyll.';
            requestAnimationFrame(loop);
        }
    });

    // Per-frame combat & chest interactions are handled in update()
});
