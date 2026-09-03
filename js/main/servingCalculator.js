/**
 * Dynamic Serving Size Calculator
 * Calculates ingredient quantities based on serving size changes
 */

class ServingCalculator {
    constructor() {
        this.originalServings = 1;
        this.originalQuantities = {};
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupCalculator());
        } else {
            this.setupCalculator();
        }
    }

    setupCalculator() {
        // Find serving controls and ingredients
        this.servingDisplay = document.getElementById('serving-display');
        this.servingDecrease = document.getElementById('serving-decrease');
        this.servingIncrease = document.getElementById('serving-increase');
        this.ingredientElements = document.querySelectorAll('.ingredient-quantity');

        if (!this.servingDisplay) {
            console.log('Serving calculator not needed on this page');
            return;
        }

        // Store original serving count and quantities
        this.originalServings = parseInt(this.servingDisplay.textContent) || 1;
        this.storeOriginalQuantities();

        // Add event listeners
        this.servingDecrease?.addEventListener('click', () => this.changeServings(-1));
        this.servingIncrease?.addEventListener('click', () => this.changeServings(1));

        console.log('Serving calculator initialized with', this.originalServings, 'servings');
    }

    storeOriginalQuantities() {
        this.ingredientElements.forEach((element, index) => {
            const quantity = this.parseQuantity(element.textContent);
            this.originalQuantities[index] = quantity;
        });
    }

    parseQuantity(text) {
        // Extract numeric value from text like "2 kg" or "1.5 liter"
        const match = text.match(/(\d+(?:\.\d+)?)/);
        return match ? parseFloat(match[1]) : 0;
    }

    formatQuantity(originalText, newQuantity) {
        // Replace the numeric part while keeping the unit
        return originalText.replace(/(\d+(?:\.\d+)?)/, newQuantity.toString());
    }

    changeServings(change) {
        const currentServings = parseInt(this.servingDisplay.textContent);
        const newServings = Math.max(1, Math.min(50, currentServings + change));

        if (newServings === currentServings) {
            return; // No change needed
        }

        // Update serving display
        this.servingDisplay.textContent = newServings;

        // Calculate multiplier
        const multiplier = newServings / this.originalServings;

        // Update all ingredient quantities
        this.ingredientElements.forEach((element, index) => {
            const originalQuantity = this.originalQuantities[index];
            const newQuantity = (originalQuantity * multiplier).toFixed(1);
            const newText = this.formatQuantity(element.dataset.originalText || element.textContent, newQuantity);
            
            // Store original text on first change
            if (!element.dataset.originalText) {
                element.dataset.originalText = element.textContent;
            }
            
            element.textContent = newText;
        });

        console.log(`Servings changed to ${newServings} (${multiplier.toFixed(2)}x multiplier)`);
    }
}

// Initialize when script loads
new ServingCalculator();