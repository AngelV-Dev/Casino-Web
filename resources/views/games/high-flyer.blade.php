<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Flyer | Casino</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1e293b;
            --darker: #0f172a;
        }
        
        body {
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 100%);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .game-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .multiplier-display {
            background: linear-gradient(135deg, #1e40af, #3730a3);
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            border: 2px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .multiplier-display::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .plane-container {
            position: relative;
            height: 120px;
            margin: 30px 0;
        }
        
        .plane {
            font-size: 4rem;
            position: absolute;
            left: 0;
            transition: left 0.1s ease-out;
            filter: drop-shadow(0 5px 15px rgba(255, 255, 255, 0.3));
        }
        
        .flight-path {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
        }
        
        .flight-progress {
            height: 100%;
            background: linear-gradient(90deg, var(--warning), var(--success));
            border-radius: 2px;
            width: 0%;
            transition: width 0.1s ease;
            box-shadow: 0 0 20px var(--warning);
        }
        
        .btn-glow {
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }
        
        .btn-glow:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.6);
        }
        
        .btn-glow:active {
            transform: translateY(0);
        }
        
        .btn-cashout {
            background: linear-gradient(135deg, var(--warning), #f97316);
            box-shadow: 0 5px 15px rgba(245, 158, 11, 0.4);
        }
        
        .btn-cashout:hover {
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.6);
        }
        
        .bet-controls .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .history-entry {
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--success);
            animation: slideIn 0.3s ease;
        }
        
        .history-entry.crash {
            border-left-color: var(--danger);
            background: rgba(239, 68, 68, 0.1);
        }
        
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .pulse {
            animation: pulse 0.5s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .crash-animation {
            animation: crash 1s ease-out forwards;
        }
        
        @keyframes crash {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(100px) rotate(45deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="game-card p-4">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <h1 class="display-5 fw-bold">🚀 HIGH FLYER</h1>
                        <p class="text-light-emphasis">Vuela alto, retira a tiempo</p>
                    </div>

                    <!-- Balance -->
                    <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded" style="background: rgba(255,255,255,0.1);">
                        <div>
                            <small class="text-light-emphasis">BALANCE</small>
                            <h3 class="mb-0 text-success">$<span id="balance">1000</span></h3>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline-light btn-sm" onclick="updateBalance(-100)">-100</button>
                            <button class="btn btn-outline-light btn-sm" onclick="updateBalance(-10)">-10</button>
                            <button class="btn btn-outline-success btn-sm" onclick="updateBalance(10)">+10</button>
                            <button class="btn btn-outline-success btn-sm" onclick="updateBalance(100)">+100</button>
                        </div>
                    </div>

                    <!-- Game Area -->
                    <div class="multiplier-display text-center">
                        <small class="text-light-emphasis">MULTIPLICADOR ACTUAL</small>
                        <h1 class="display-2 fw-bold my-2" id="multiplier">1.00x</h1>
                        <small class="text-warning" id="multiplierStatus">Listo para despegar</small>
                    </div>

                    <!-- Plane Animation -->
                    <div class="plane-container">
                        <div class="plane" id="plane">✈️</div>
                        <div class="flight-path">
                            <div class="flight-progress" id="flightProgress"></div>
                        </div>
                    </div>

                    <!-- Bet Controls -->
                    <div class="bet-controls mb-4">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-dark text-white border-dark">$</span>
                            <input type="number" id="betAmount" class="form-control bg-dark text-white border-dark" 
                                   placeholder="Cantidad a apostar" min="1" value="10">
                            <div class="input-group-append">
                                <button class="btn btn-outline-warning" onclick="setBet(10)">10</button>
                                <button class="btn btn-outline-warning" onclick="setBet(50)">50</button>
                                <button class="btn btn-outline-warning" onclick="setBet(100)">100</button>
                            </div>
                        </div>
                    </div>

                    <!-- Game Controls -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <button id="startBtn" class="btn btn-glow w-100 h-100" onclick="startGame()">
                                <i class="fas fa-rocket me-2"></i>DESPEGAR
                            </button>
                        </div>
                        <div class="col-6">
                            <button id="cashoutBtn" class="btn btn-cashout w-100 h-100" onclick="cashOut()" disabled>
                                <i class="fas fa-money-bill-wave me-2"></i>RETIRAR
                            </button>
                        </div>
                    </div>

                    <!-- History -->
                    <div class="history">
                        <h6 class="text-light-emphasis mb-3"><i class="fas fa-history me-2"></i>HISTORIAL DE VUELOS</h6>
                        <div id="historyLog" style="max-height: 200px; overflow-y: auto;">
                            <div class="text-center text-light-emphasis py-3">
                                <i class="fas fa-plane-departure fa-2x mb-2"></i>
                                <p>Los vuelos aparecerán aquí</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/games/high-flyer.js') }}"></script>
</body>
</html>