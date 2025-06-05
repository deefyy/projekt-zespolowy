import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import './disableSubmit';

let currentFontSize = parseInt(localStorage.getItem('fontSize')) || 16;
let isHighContrast = localStorage.getItem('highContrast') === 'true';

// Zastosuj zapisane ustawienia przy załadowaniu strony
document.documentElement.style.fontSize = `${currentFontSize}px`;
if (isHighContrast) {
    document.body.classList.add('high-contrast');
}

// Funkcja do zmiany rozmiaru czcionki
function adjustFontSize(action) {
    if (action === 'increase' && currentFontSize < 24) {
        currentFontSize += 2;
    } else if (action === 'decrease' && currentFontSize > 12) {
        currentFontSize -= 2;
    }
    document.documentElement.style.fontSize = `${currentFontSize}px`;
    localStorage.setItem('fontSize', currentFontSize); // Zapisz do localStorage
}


// Funkcja do przełączania wysokiego kontrastu
function toggleContrast() {
    isHighContrast = !isHighContrast;
    if (isHighContrast) {
        document.body.classList.add('high-contrast');
    } else {
        document.body.classList.remove('high-contrast');
    }
    localStorage.setItem('highContrast', isHighContrast); // Zapisz do localStorage
}

// Funkcja do resetowania ustawień WCAG
function resetWCAGSettings() {
    // Przywróć ustawienia domyślne
    currentFontSize = 16;
    isHighContrast = false;

    // Zastosuj domyślne ustawienia
    document.documentElement.style.fontSize = `${currentFontSize}px`;
    document.body.classList.remove('high-contrast');

    // Usuń ustawienia z localStorage
    localStorage.removeItem('fontSize');
    localStorage.removeItem('lineHeight');
    localStorage.removeItem('highContrast');
}

// Dodaj funkcje do obiektu globalnego
window.adjustFontSize = adjustFontSize;
window.toggleContrast = toggleContrast;
window.resetWCAGSettings = resetWCAGSettings;