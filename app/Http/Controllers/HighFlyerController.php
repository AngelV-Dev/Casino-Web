// Agrega estas variables
const currentGameId = ref(null);

// startGame actualizado
const startGame = async () => {
  if (isFlying.value || isProcessing.value) return;
  
  if (betAmount.value <= 0) {
    showNotification("⚠️ Monto inválido", "warning");
    return;
  }

  if (betAmount.value > balance.value) {
    showNotification("⚠️ Fondos insuficientes", "error");
    return;
  }

  isProcessing.value = true;

  try {
    const response = await axios.post('/api/high-flyer/start', {
      bet_amount: betAmount.value
    });

    if (response.data.success) {
      // ✅ GUARDAR ID DEL JUEGO
      currentGameId.value = response.data.game_id;
      
      // ✅ ACTUALIZAR BALANCE
      authStore.setBalance(response.data.new_balance);
      
      // Iniciar juego
      isFlying.value = true;
      crashed.value = false;
      multiplierStatus.value = "🛫 Despegando...";
      startMultiplier();
      
      showNotification("🚀 ¡Vuelo iniciado!", "success");
    } else {
      showNotification(response.data.message, "error");
    }
  } catch (error) {
    console.error('Error:', error);
    showNotification("Error de conexión", "error");
  } finally {
    isProcessing.value = false;
  }
};

// cashOut actualizado
const cashOut = async () => {
  if (!isFlying.value || isProcessing.value) return;

  isProcessing.value = true;
  stopMultiplier();

  try {
    const response = await axios.post('/api/high-flyer/cashout', {
      game_id: currentGameId.value,
      multiplier: multiplier.value.toFixed(2)
    });

    if (response.data.success) {
      // ✅ ACTUALIZAR BALANCE CON GANANCIAS
      authStore.setBalance(response.data.new_balance);
      
      // Agregar al historial local
      history.value.unshift({
        value: multiplier.value.toFixed(2),
        crash: false,
        win: true,
        profit: response.data.payout
      });
      
      multiplierStatus.value = `💰 Retirado a ${multiplier.value.toFixed(2)}x!`;
      showNotification(`🎉 ¡Ganaste S/ ${response.data.payout}!`, "success");
      
      // Resetear
      setTimeout(() => {
        resetGame();
        currentGameId.value = null;
      }, 2000);
    }
  } catch (error) {
    console.error('Error:', error);
    showNotification("Error al retirar", "error");
  } finally {
    isProcessing.value = false;
    isFlying.value = false;
  }
};

// gameCrashed actualizado
const gameCrashed = async () => {
  stopMultiplier();
  crashed.value = true;
  isFlying.value = false;
  multiplierStatus.value = `💥 Crash en ${multiplier.value.toFixed(2)}x`;

  try {
    // Notificar al backend del crash
    await axios.post('/api/high-flyer/crash', {
      game_id: currentGameId.value,
      crash_multiplier: multiplier.value.toFixed(2)
    });
    
    history.value.unshift({
      value: multiplier.value.toFixed(2),
      crash: true,
      win: false,
      profit: -betAmount.value
    });
    
    showNotification(`💥 Crash en ${multiplier.value.toFixed(2)}x`, "error");
    
    setTimeout(() => {
      resetGame();
      currentGameId.value = null;
    }, 3000);
  } catch (error) {
    console.error('Error:', error);
  }
};