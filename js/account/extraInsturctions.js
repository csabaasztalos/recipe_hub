document.addEventListener('DOMContentLoaded', () => {
  const addInsturctionBtn = document.getElementById('addInsturction');

  function updateInstructionNumbers() {
    const allInstructions = document.querySelectorAll('.instruction');
    allInstructions.forEach((instruction, index) => {
      const label = instruction.querySelector('label');
      if (label) {
        label.textContent = `${index + 1}. Lépés `;
      }
    });
  }

  function getNextInstructionId() {
    const allInstructions = document.querySelectorAll('.instruction');
    let maxId = 0;
    allInstructions.forEach(instruction => {
      const id = instruction.id.replace('instruction', '');
      maxId = Math.max(maxId, parseInt(id) || 0);
    });
    return maxId + 1;
  }

  addInsturctionBtn.addEventListener('click', () => {
    const nextId = getNextInstructionId();

    const newInsturction = document.createElement('div');
    newInsturction.className = 'instruction';
    newInsturction.id = `instruction${nextId}`;
    newInsturction.innerHTML = `
      <button type="button" class="instructionDeleteBtn" title="Törlés">&times;</button>
      <label for="instructionText${nextId}">. Lépés </label>
      <input id="instructionText${nextId}" name="instructions[]" class="form-control" required type="text" minlength = "10">
    `;

    const allInstructions = document.querySelectorAll('.instruction');
    const lastInstruction = allInstructions[allInstructions.length - 1];
    lastInstruction.after(newInsturction);

    // Update all instruction numbers
    updateInstructionNumbers();

    // Add delete event listener
    newInsturction.querySelector('.instructionDeleteBtn').addEventListener('click', () => {
      newInsturction.remove();
      updateInstructionNumbers(); // Update numbers after deletion
    });
  });

  // Also add delete listeners to existing instructions
  document.querySelectorAll('.instructionDeleteBtn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.target.closest('.instruction').remove();
      updateInstructionNumbers();
    });
  });
});