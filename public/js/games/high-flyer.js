class HighFlyerGame {
    constructor() {
        this.balance = 1000;
        this.bet = 0;
        this.multiplier = 1.0;
        this.isFlying = false;
        this.interval = null;
        this.flightTime = 0;
        this.maxFlightTime = 8000; // 8 segundos máximo
        this.crashProbability = 0.08; // 8% base de crash
    }

    startFlight(betAmount) {
        if (this.isFlying) {
            this.showMessage('⚠️ ¡Vuelo en progreso!', 'warning');
            return false;
        }

        if (betAmount > this.balance) {
            this.showMessage('❌ Fondos insuficientes', 'danger');
            return false;
        }

        if (betAmount <= 0) {
            this.showMessage('⚠️ Apuesta mínima: $1', 'warning');
            return false;
        }

        // Iniciar juego
        this.bet = betAmount;
        this.balance -= betAmount;
        this.multiplier = 1.0;
        this.isFlying = true;
        this.flightTime = 0;

        this.updateUI();
        this.toggleButtons(true);
        this.updateMultiplierStatus('🛫 Despegando...');

        // Efectos visuales
        this.animateMultiplier();
        document.getElementById('plane').style.animation = 'none';

        this.interval = setInterval(() => this.updateFlight(), 100);
        return true;
    }

    updateFlight() {
        if (!this.isFlying) return;

        this.flightTime += 100;
        const progress = this.flightTime / this.maxFlightTime;
        
        // Incrementar multiplicador (más rápido al inicio)
        const speedFactor = 1.5 - (progress * 0.8); // Se hace más lento con el tiempo
        const increment = (speedFactor * Math.random() * 0.4);
        this.multiplier += increment;

        // Actualizar progreso visual
        const progressPercent = Math.min((this.flightTime / this.maxFlightTime) * 100, 100);
        document.getElementById('flightProgress').style.width = progressPercent + '%';

        // Mover avión
        const plane = document.getElementById('plane');
        plane.style.left = `calc(${progressPercent}% - 2rem)`;

        // Probabilidad de crash aumenta con el tiempo
        const currentCrashProbability = this.crashProbability + (progress * 0.3);
        
        if (Math.random() < currentCrashProbability || this.flightTime >= this.maxFlightTime) {
            this.crash();
            return;
        }

        // Actualizar estado
        if (this.multiplier < 2.0) {
            this.updateMultiplierStatus('🛫 Ascendiendo...');
        } else if (this.multiplier < 5.0) {
            this.updateMultiplierStatus('✈️ Crucero...');
        } else {
            this.updateMultiplierStatus('🚀 ¡A toda velocidad!');
        }

        this.updateUI();
    }

    cashOut() {
        if (!this.isFlying) {
            this.showMessage('⚠️ No hay vuelo activo', 'warning');
            return 0;
        }

        clearInterval(this.interval);
        const winnings = Math.floor(this.bet * this.multiplier);
        this.balance += winnings;
        this.isFlying = false;

        // Efectos visuales
        this.animateMultiplier('success');
        document.getElementById('multiplier').classList.add('text-success');

        this.addToHistory(`✅ Retirado en ${this.multiplier.toFixed(2)}x`, `+$${winnings}`, 'success');
        this.showMessage(`🎉 ¡Retirado! Ganaste $${winnings}`, 'success');
        
        this.updateMultiplierStatus('💰 Retiro exitoso');
        this.updateUI();
        this.toggleButtons(false);
        
        // Reset visual después de 1 segundo
        setTimeout(() => {
            document.getElementById('multiplier').classList.remove('text-success');
        }, 1000);

        return winnings;
    }

    crash() {
        clearInterval(this.interval);
        this.isFlying = false;
        
        // Efectos visuales
        const plane = document.getElementById('plane');
        plane.classList.add('crash-animation');
        document.getElementById('multiplier').classList.add('text-danger');

        this.addToHistory(`💥 Crash en ${this.multiplier.toFixed(2)}x`, `-$${this.bet}`, 'crash');
        this.showMessage(`💥 ¡El avión se estrelló en ${this.multiplier.toFixed(2)}x!`, 'danger');
        this.updateMultiplierStatus('💥 ¡Crash!');

        this.updateUI();
        this.toggleButtons(false);

        // Reset visual después de 1.5 segundos
        setTimeout(() => {
            plane.classList.remove('crash-animation');
            plane.style.left = '0';
            document.getElementById('flightProgress').style.width = '0%';
            document.getElementById('multiplier').classList.remove('text-danger');
        }, 1500);
    }

    updateUI() {
        document.getElementById('multiplier').textContent = this.multiplier.toFixed(2) + 'x';
        document.getElementById('balance').textContent = this.balance;
    }

    toggleButtons(flying) {
        document.getElementById('startBtn').disabled = flying;
        document.getElementById('cashoutBtn').disabled = !flying;
        document.getElementById('betAmount').disabled = flying;
        
        if (flying) {
            document.getElementById('startBtn').innerHTML = '<i class="fas fa-pause me-2"></i>VOLANDO...';
        } else {
            document.getElementById('startBtn').innerHTML = '<i class="fas fa-rocket me-2"></i>DESPEGAR';
        }
    }

    addToHistory(title, amount, type = 'success') {
        const historyLog = document.getElementById('historyLog');
        
        // Remover placeholder si existe
        if (historyLog.children.length === 1 && historyLog.children[0].querySelector('.fa-plane-departure')) {
            historyLog.innerHTML = '';
        }

        const entry = document.createElement('div');
        entry.className = `history-entry ${type === 'crash' ? 'crash' : ''}`;
        entry.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${title}</strong>
                    <br><small class="text-light-emphasis">${new Date().toLocaleTimeString()}</small>
                </div>
                <div class="text-${type === 'crash' ? 'danger' : 'success'} fw-bold">
                    ${amount}
                </div>
            </div>
        `;
        
        historyLog.prepend(entry);
        
        // Mantener máximo 8 entradas
        if (historyLog.children.length > 8) {
            historyLog.removeChild(historyLog.lastChild);
        }
    }

    updateMultiplierStatus(status) {
        document.getElementById('multiplierStatus').textContent = status;
    }

    animateMultiplier(type = 'normal') {
        const multiplierElement = document.getElementById('multiplier');
        multiplierElement.classList.add('pulse');
        
        setTimeout(() => {
            multiplierElement.classList.remove('pulse');
        }, 500);
    }

    showMessage(message, type = 'info') {
        // Crear notificación temporal
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3`;
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close btn-close-white ms-2" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remover después de 3 segundos
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 3000);
    }
}

// Instancia global del juego
const highFlyerGame = new HighFlyerGame();

// Funciones globales para los botones
function startGame() {
    const betAmount = parseInt(document.getElementById('betAmount').value) || 10;
    highFlyerGame.startFlight(betAmount);
}

function cashOut() {
    highFlyerGame.cashOut();
}

function setBet(amount) {
    document.getElementById('betAmount').value = amount;
}

function updateBalance(change) {
    highFlyerGame.balance += change;
    highFlyerGame.updateUI();
}

// Efectos de sonido básicos (opcional)
function playSound(type) {
    // Puedes agregar sonidos aquí más tarde
    console.log(`Play sound: ${type}`);
}