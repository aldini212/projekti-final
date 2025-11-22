<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reaction Time Challenge - GameHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../shared.css" rel="stylesheet">
    <style>
        body {
            background: #f3f4f6;
            color: #111827;
        }

        .reaction-wrapper {
            position: relative;
            width: 100%;
            height: 420px;
            border-radius: 18px;
            overflow: hidden;
            /* Put your own background image here */
            background-image: url('../assets/images/reaction-bg.jpg');
            background-size: cover;
            background-position: center;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.35);
        }

        #reactionArea {
            position: absolute;
            inset: 0;
        }

        .reaction-block {
            position: absolute;
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.55);
            cursor: pointer;
        }

        .reaction-block.phase-2 {
            background: linear-gradient(135deg, #eab308, #f97316);
        }

        .reaction-block.phase-3 {
            background: linear-gradient(135deg, #f97316, #ef4444);
        }

        .hud-small {
            font-size: 0.8rem;
            color: #111827;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-header">
            <h1 class="game-title text-dark">Reaction Time Challenge</h1>
            <p class="game-subtitle text-dark">Sa shpejt mund të reagosh?</p>
        </div>

        <div class="card p-3 mb-3 hud-small d-flex justify-content-between">
            <div>Faza: <span id="phaseLabel">-</span></div>
            <div>Raundi: <span id="roundLabel">0</span>/5</div>
            <div>Reagimi i fundit: <span id="lastTime">-</span> ms</div>
            <div>Mesatarja: <span id="avgTime">-</span> ms</div>
        </div>

        <div class="reaction-wrapper mb-3">
            <canvas id="reactionArea"></canvas>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-between align-items-center">
            <div class="small text-dark">
                Faza 1: blloqe statike • Faza 2: blloqe lëvizin më ngadalë • Faza 3: teleportim i rastësishëm
            </div>
            <button id="btnStartReaction" class="btn btn-primary">
                <i class="bi bi-lightning-charge-fill me-1"></i>Start Test
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="game.js"></script>
</body>
</html>