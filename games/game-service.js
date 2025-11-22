class GameService {
    constructor(gameName) {
        this.gameName = gameName;
        this.storageKey = `gamehub_${gameName}`;
        this.loadProgress();
    }

    loadProgress() {
        const savedData = localStorage.getItem(this.storageKey);
        if (savedData) {
            const { xp = 0, level = 1 } = JSON.parse(savedData);
            this.xp = xp;
            this.level = level;
            this.xpToNextLevel = this.calculateXpToNextLevel(level);
        } else {
            this.xp = 0;
            this.level = 1;
            this.xpToNextLevel = 100;
            this.saveProgress();
        }
    }

    saveProgress() {
        const gameData = {
            xp: this.xp,
            level: this.level,
            lastPlayed: new Date().toISOString()
        };
        localStorage.setItem(this.storageKey, JSON.stringify(gameData));
        this.updateGlobalProgress();
    }

    updateGlobalProgress() {
        // Update the global progress that will be shown in the profile
        let allGamesProgress = JSON.parse(localStorage.getItem('gamehub_all_games') || '{}');
        allGamesProgress[this.gameName] = {
            xp: this.xp,
            level: this.level,
            lastPlayed: new Date().toISOString()
        };
        localStorage.setItem('gamehub_all_games', JSON.stringify(allGamesProgress));
    }

    addXp(amount) {
        const oldXp = this.xp;
        this.xp += amount;
        let levelsGained = 0;

        // Check for level up
        while (this.xp >= this.xpToNextLevel) {
            this.xp -= this.xpToNextLevel;
            this.level++;
            levelsGained++;
            this.xpToNextLevel = this.calculateXpToNextLevel(this.level);
        }

        this.saveProgress();
        return { xpGained: amount, levelsGained };
    }

    calculateXpToNextLevel(level) {
        // Scale XP needed for next level
        return Math.floor(100 * Math.pow(1.2, level - 1));
    }

    getProgress() {
        return {
            xp: this.xp,
            level: this.level,
            xpToNextLevel: this.xpToNextLevel,
            progressPercentage: Math.floor((this.xp / this.xpToNextLevel) * 100)
        };
    }

    static getAllGamesProgress() {
        return JSON.parse(localStorage.getItem('gamehub_all_games') || '{}');
    }
}

// XP gain animations
function showXpGain(amount, levelsGained = 0) {
    const xpGainEl = document.createElement('div');
    xpGainEl.className = 'xp-gain-notification';
    
    let html = `<i class="bi bi-star-fill"></i> +${amount} XP`;
    if (levelsGained > 0) {
        html += ` <span class="level-up-badge">Level Up! x${levelsGained}</span>`;
    }
    
    xpGainEl.innerHTML = html;
    document.body.appendChild(xpGainEl);
    
    // Trigger animation
    setTimeout(() => {
        xpGainEl.classList.add('show');
    }, 10);
    
    // Remove after animation
    setTimeout(() => {
        xpGainEl.remove();
    }, 3000);
}

// Add styles for XP notifications
const xpStyles = document.createElement('style');
xpStyles.textContent = `
    .xp-gain-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.8);
        color: #FFD700;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 1000;
        transform: translateX(120%);
        transition: transform 0.3s ease-out;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    .xp-gain-notification.show {
        transform: translateX(0);
    }
    
    .xp-gain-notification i {
        color: #FFD700;
    }
    
    .level-up-badge {
        background: linear-gradient(45deg, #FFD700, #FFA500);
        color: #000;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.9em;
        margin-left: 8px;
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
`;
document.head.appendChild(xpStyles);
