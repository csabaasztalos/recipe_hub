document.addEventListener('DOMContentLoaded', function () {
    const lastSentInput = document.getElementById('lastSent');
    const cooldownInput = document.getElementById('cooldown');
    const resendBtn = document.getElementById('resendBtn');
    const resendtText = document.getElementById('resendtText');

    if (!lastSentInput || !cooldownInput || !resendBtn) return;

    const lastSent = parseInt(lastSentInput.value, 10);
    const cooldown = parseInt(cooldownInput.value, 10);

    function updateCountdown() {
        const now = Math.floor(Date.now() / 1000);
        const elapsed = now - lastSent;
        const remaining = cooldown - elapsed;

        if (remaining > 0) {
            resendBtn.disabled = true;
            resendtText.innerText = `Ennyi idő múlva tudsz új emailt kérni: ${remaining} másodperc`;
        } else {
            resendBtn.disabled = false;
            resendtText.innerText = "A visszaigazoló újraküldéséhez kattints a gombra.";
        }
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
});