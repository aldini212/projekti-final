// DOM Elements
const startScreen = document.getElementById('start-screen');
const gameScreen = document.getElementById('game-screen');
const endScreen = document.getElementById('end-screen');
const questionCountInput = document.getElementById('question-count');
const categorySelect = document.getElementById('category');
const difficultyButtons = document.querySelectorAll('.difficulty-btn');
const startBtn = document.getElementById('start-btn');
const playAgainBtn = document.getElementById('play-again-btn');
const backToMenuBtn = document.getElementById('back-to-menu-btn');
const questionText = document.getElementById('question-text');
const optionsContainer = document.getElementById('options');
const scoreElement = document.getElementById('score');
const timeElement = document.getElementById('time');
const questionNumberElement = document.getElementById('question-number');
const categoryDisplay = document.getElementById('category-display');
const finalScoreElement = document.getElementById('final-score');
const correctAnswersElement = document.getElementById('correct-answers');
const totalQuestionsElement = document.getElementById('total-questions');
const accuracyElement = document.getElementById('accuracy');
const loading = document.getElementById('loading');

// Game state
let currentQuestionIndex = 0;
let score = 0;
let timeLeft = 30;
let timer;
let questions = [];
let totalQuestions = 10;
let selectedCategory = '9'; // Default: General Knowledge
let selectedDifficulty = 'easy';
let correctAnswers = 0;
let answeredQuestions = 0;

// Category ID to name mapping
const categoryMap = {
    '9': 'General Knowledge',
    '21': 'Sports',
    '22': 'Geography',
    '23': 'History',
    '17': 'Science & Nature',
    '18': 'Computers',
    '11': 'Movies',
    '12': 'Music',
    '15': 'Video Games'
};

// Difficulty mapping for display
const difficultyMap = {
    'easy': 'Easy',
    'medium': 'Medium',
    'hard': 'Hard'
};

// Fallback questions in case API fails
const fallbackQuestions = {
    '9': [
        {
            question: "What is the capital of France?",
            correct_answer: "Paris",
            incorrect_answers: ["London", "Berlin", "Madrid"],
            category: "General Knowledge"
        },
        {
            question: "Which planet is known as the Red Planet?",
            correct_answer: "Mars",
            incorrect_answers: ["Venus", "Jupiter", "Saturn"],
            category: "General Knowledge"
        }
    ],
    '15': [
        {
            question: "Which company developed the game 'Minecraft'?",
            correct_answer: "Mojang",
            incorrect_answers: ["Valve", "Ubisoft", "Electronic Arts"],
            category: "Video Games"
        },
        {
            question: "In 'The Legend of Zelda' series, what is the name of Link's sword?",
            correct_answer: "Master Sword",
            incorrect_answers: ["Biggoron's Sword", "Fierce Deity's Sword", "Goddess Sword"],
            category: "Video Games"
        }
    ]
};

// Initialize the game
function init() {
    console.log('Initializing game...');
    // Set initial active difficulty button
    document.querySelector('.difficulty-btn[data-difficulty="easy"]').classList.add('active');
    
    // Add event listeners
    startBtn.addEventListener('click', startGame);
    playAgainBtn.addEventListener('click', resetGame);
    backToMenuBtn.addEventListener('click', showStartScreen);
    
    // Add category change listener
    categorySelect.addEventListener('change', (e) => {
        selectedCategory = e.target.value;
    });
    
    // Add difficulty button listeners
    difficultyButtons.forEach(button => {
        button.addEventListener('click', () => {
            difficultyButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            selectedDifficulty = button.dataset.difficulty;
        });
    });
    
    // Show start screen
    showStartScreen();
    
    console.log('Game initialized!');
}

// Show start screen
function showStartScreen() {
    startScreen.classList.add('active');
    gameScreen.classList.remove('active');
    endScreen.classList.remove('active');
    resetGame();
}

// Start the game
async function startGame() {
    const count = parseInt(questionCountInput.value);
    totalQuestions = isNaN(count) || count < 5 || count > 20 ? 10 : count;
    
    showLoading(true);
    
    try {
        // Try to fetch questions from the API with selected category and difficulty
        const apiUrl = `https://opentdb.com/api.php?amount=${totalQuestions}&category=${selectedCategory}&difficulty=${selectedDifficulty}&type=multiple`;
        console.log('Fetching questions from:', apiUrl);
        
        const response = await fetch(apiUrl);
        const data = await response.json();
        
        if (data.response_code === 0 && data.results.length > 0) {
            // Add category name to each question
            questions = data.results.map(q => ({
                ...q,
                category_name: categoryMap[selectedCategory] || 'General Knowledge'
            }));
        } else {
            // If API fails, use fallback questions for the selected category
            const fallback = fallbackQuestions[selectedCategory] || fallbackQuestions['9'];
            questions = fallback.slice(0, Math.min(totalQuestions, fallback.length));
            
            // If we don't have enough fallback questions, fill with general knowledge
            if (questions.length < totalQuestions) {
                const remaining = totalQuestions - questions.length;
                questions = [...questions, ...fallbackQuestions['9'].slice(0, remaining)];
            }
        }
    } catch (error) {
        console.error('Error fetching questions:', error);
        // Use fallback questions if there's an error
        const fallback = fallbackQuestions[selectedCategory] || fallbackQuestions['9'];
        questions = fallback.slice(0, Math.min(totalQuestions, fallback.length));
    }
    
    showLoading(false);
    
    if (questions.length === 0) {
        alert('Failed to load questions. Please try again later.');
        return;
    }
    
    startScreen.classList.remove('active');
    gameScreen.classList.add('active');
    
    score = 0;
    correctAnswers = 0;
    answeredQuestions = 0;
    currentQuestionIndex = 0;
    updateScore();
    
    showQuestion();
    startTimer();
}

// Show current question
function showQuestion() {
    if (currentQuestionIndex >= questions.length) {
        endGame();
        return;
    }
    
    const question = questions[currentQuestionIndex];
    
    // Update question counter
    questionNumberElement.textContent = `${currentQuestionIndex + 1}/${totalQuestions}`;
    
    // Update category display
    categoryDisplay.textContent = question.category_name || categoryMap[selectedCategory] || 'General Knowledge';
    
    // Decode HTML entities in question and answers
    questionText.textContent = decodeHtml(question.question);
    
    // Combine correct and incorrect answers
    const answers = [...question.incorrect_answers];
    const correctIndex = Math.floor(Math.random() * (answers.length + 1));
    answers.splice(correctIndex, 0, question.correct_answer);
    
    // Clear previous options with fade out effect
    optionsContainer.style.opacity = '0';
    setTimeout(() => {
        optionsContainer.innerHTML = '';
        
        // Create option buttons
        answers.forEach((answer, index) => {
            const button = document.createElement('button');
            button.className = 'option';
            button.textContent = decodeHtml(answer);
            button.style.animationDelay = `${index * 0.1}s`;
            button.addEventListener('click', () => selectAnswer(button, answer === question.correct_answer));
            optionsContainer.appendChild(button);
        });
        
        // Fade in options
        setTimeout(() => {
            optionsContainer.style.opacity = '1';
        }, 50);
    }, 300);
    
    // Reset timer for new question
    resetTimer();
}

// Handle answer selection
function selectAnswer(selectedButton, isCorrect) {
    // Disable all options
    const options = document.querySelectorAll('.option');
    options.forEach(option => {
        option.style.pointerEvents = 'none';
    });
    
    // Increment answered questions counter
    answeredQuestions++;
    
    // Highlight correct and incorrect answers
    if (isCorrect) {
        selectedButton.classList.add('correct');
        score += calculatePoints();
        correctAnswers++;
        updateScore();
    } else {
        selectedButton.classList.add('incorrect');
        // Find and highlight the correct answer
        options.forEach(option => {
            if (option.textContent === decodeHtml(questions[currentQuestionIndex].correct_answer)) {
                option.classList.add('correct');
            }
        });
    }
    
    // Move to next question after a delay
    clearInterval(timer);
    setTimeout(() => {
        currentQuestionIndex++;
        showQuestion();
    }, 1500);
}

// Calculate points based on time left and difficulty
function calculatePoints() {
    let basePoints = 100;
    let timeBonus = Math.floor((timeLeft / 30) * 50); // Up to 50 bonus points for quick answers
    let difficultyMultiplier = {
        'easy': 1,
        'medium': 1.5,
        'hard': 2
    }[selectedDifficulty] || 1;
    
    return Math.floor((basePoints + timeBonus) * difficultyMultiplier);
}

// Update score display                        
function updateScore() {
      scoreElement.textContent = score;
}

// Save score to server
async function saveScore() {
    try {
        const response = await fetch('/api/save_score.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                game: 'trivia',
                score: score,
                correct: correctAnswers,
                total: totalQuestions,
                time: (30 - timeLeft) * 1000, // Convert to milliseconds
                attempts: 1
            })
        });
        
        const result = await response.json();
        console.log('Score saved:', result);
        
        // Show XP gained if available
        if (result.xpEarned) {
            showXpGain(result.xpEarned, result.levelUp ? 1 : 0);
        }
        
        return result;
    } catch (error) {
        console.error('Error saving score:', error);
        return { success: false };
    }
}

// Show XP gain notification
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

// Timer functions
function startTimer() {
    resetTimer();
    updateTimerDisplay();
    timer = setInterval(() => {
        timeLeft--;
        updateTimerDisplay();
        
        // Flash red when time is running out
        if (timeLeft <= 5) {
            timeElement.style.color = '#ff6b6b';
            timeElement.style.animation = timeLeft % 2 === 0 ? 'pulse 0.5s' : 'none';
        }
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            timeOut();
        }
    }, 1000);
}

function timeOut() {
    const options = document.querySelectorAll('.option');
    options.forEach(option => {
        option.style.pointerEvents = 'none';
        if (option.textContent === decodeHtml(questions[currentQuestionIndex].correct_answer)) {
            option.classList.add('correct');
        }
    });
    
    // Increment answered questions counter
    answeredQuestions++;
    
    // Removed duplicate code here
}

// End the game
async function endGame() {
    clearInterval(timer);
    gameScreen.classList.remove('active');
    endScreen.classList.add('active');
    
    const accuracy = Math.round((correctAnswers / answeredQuestions) * 100) || 0;
    
    // Animate the score counter
    animateValue('final-score', 0, score, 1000);
    animateValue('correct-answers', 0, correctAnswers, 1000);
    totalQuestionsElement.textContent = totalQuestions;
    
    // Animate accuracy
    let currentAccuracy = 0;
    const accuracyInterval = setInterval(() => {
        if (currentAccuracy >= accuracy) {
            clearInterval(accuracyInterval);
            accuracyElement.textContent = `${accuracy}%`;
            
            // Save score after animations complete
            saveScore().catch(console.error);
        } else {
            currentAccuracy++;
            accuracyElement.textContent = `${currentAccuracy}%`;
        }
    }, 20);
}

// Animate number counter
function animateValue(id, start, end, duration) {
    const obj = document.getElementById(id);
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Reset the game
function resetGame() {
    score = 0;
    correctAnswers = 0;
    answeredQuestions = 0;
    currentQuestionIndex = 0;
    timeLeft = 30;
    updateScore();
    clearInterval(timer);
    
    // Reset timer display
    timeElement.style.color = '';
    timeElement.style.animation = '';
    
    // Reset any active states
    document.querySelectorAll('.option').forEach(option => {
        option.classList.remove('correct', 'incorrect');
        option.style.pointerEvents = 'auto';
    });
    
    // Reset difficulty buttons
    difficultyButtons.forEach(btn => {
        if (btn.dataset.difficulty === 'easy') {
            btn.classList.add('active');
            selectedDifficulty = 'easy';
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Reset category select
    categorySelect.value = '9';
    selectedCategory = '9';
    
    // Go back to start screen if not already there
    if (!startScreen.classList.contains('active')) {
        showStartScreen();
    } else {
        // If already on start screen, just update the UI
        updateScore();
    }
}

// Show/hide loading spinner
function showLoading(show) {
    if (show) {
        loading.classList.add('active');
    } else {
        loading.classList.remove('active');
    }
}

// Helper function to decode HTML entities
function decodeHtml(html) {
    if (!html) return '';
    const txt = document.createElement('textarea');
    txt.innerHTML = html;
    return txt.value;
}

// Add CSS for animations and notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    /* XP Notification */
    .xp-gain-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.9);
        color: #FFD700;
        padding: 15px 25px;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 1000;
        transform: translateX(120%);
        transition: transform 0.3s ease-out;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 215, 0, 0.2);
    }
    
    .xp-gain-notification.show {
        transform: translateX(0);
    }
    
    .xp-gain-notification i {
        color: #FFD700;
        font-size: 1.2rem;
    }
    
    .level-up-badge {
        background: linear-gradient(45deg, #FFD700, #FFA500);
        color: #000;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        margin-left: 8px;
        font-weight: bold;
        animation: pulse 1s infinite;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    @media (max-width: 768px) {
        .xp-gain-notification {
            top: 10px;
            right: 10px;
            left: 10px;
            text-align: center;
            justify-content: center;
        }
    }
`;
document.head.appendChild(style);

// Initialize the game when the script loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    // DOMContentLoaded has already fired
    init();
}