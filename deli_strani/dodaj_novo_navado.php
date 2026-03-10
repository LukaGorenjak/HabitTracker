<style>
    .add-habit-form {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #1a1a1a;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.6);
        padding: 0;
        z-index: 10;
        width: 90%;
        max-width: 700px;
        max-height: 85vh;
        overflow-y: auto;
        color: #f5f3e7;
        border: 1px solid #333;
    }

    .add-habit-form.active {
        display: block;
    }
    
    .form-tab {
        background: #2b3a2f;
        color: #f5f3e7;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.2s;
    }

    .form-tab:hover {
        background: #3b4d3f;
    }

    .form-content {
        padding: 24px;
    }

    .form-section {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        gap: 16px;
    }

    .section-icon {
        font-size: 20px;
        width: 30px;
        flex-shrink: 0;
    }

    .section-label {
        font-size: 16px;
        font-weight: bold;
        color: #f5f3e7;
        min-width: 120px;
    }

    .section-controls {
        display: flex;
        gap: 12px;
        flex: 1;
        align-items: center;
        flex-wrap: wrap;
    }

    .form-input {
        background: #2b3a2f;
        color: #f5f3e7;
        border: 1px solid #3b4d3f;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 16px;
    }

    .form-input::placeholder {
        color: #666;
    }

    .form-input:focus {
        outline: none;
        border-color: #4a9d6f;
        box-shadow: 0 0 0 2px rgba(74, 157, 111, 0.2);
    }

    .form-select {
        background: #2b3a2f;
        color: #f5f3e7;
        border: 1px solid #3b4d3f;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
    }

    .form-select:focus {
        outline: none;
        border-color: #4a9d6f;
    }

    .checkbox-group {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #4a9d6f;
    }

    .checkbox-item label {
        cursor: pointer;
        font-size: 16px;
        color: #f5f3e7;
        margin: 0;
    }

    .reminder-pill {
        background: #2b3a2f;
        color: #f5f3e7;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .reminder-pill button {
        background: none;
        border: none;
        color: #f5f3e7;
        cursor: pointer;
        font-size: 16px;
        padding: 0;
    }

    .add-button {
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        font-size: 14px;
        padding: 0;
    }

    .magic-fill {
        background: none;
        border: none;
        color: #c47c9f;
        cursor: pointer;
        font-size: 16px;
        padding: 0;
    }

    .form-footer {
        display: flex;
        gap: 12px;
        padding: 20px 24px;
        border-top: 1px solid #333;
        background: #1a1a1a;
        justify-content: flex-end;
    }

    .form-button {
        padding: 10px 24px;
        border-radius: 6px;
        border: none;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-cancel {
        background: none;
        color: #f5f3e7;
        border: 1px solid #3b4d3f;
    }

    .btn-cancel:hover {
        background: #2b3a2f;
    }

    .btn-save {
        background: #2b5a4a;
        color: #f5f3e7;
        border: 2px solid #4a9d6f;
    }

    .btn-save:hover {
        background: #4a9d6f;
    }

    .form-input-number {
        width: 80px;
    }

    .date-input {
        background: #2b3a2f;
        color: #f5f3e7;
        border: 1px solid #3b4d3f;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
    }

    .custom-dropdown {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .dropdown-button {
        background: #2b3a2f;
        color: #f5f3e7;
        border: 1px solid #3b4d3f;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .dropdown-button:hover {
        border-color: #4a9d6f;
    }

    .dropdown-button.active {
        border-color: #4a9d6f;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background: #2b3a2f;
        color: #f5f3e7;
        border: 1px solid #3b4d3f;
        border-top: none;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        z-index: 1;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        top: 100%;
    }

    .dropdown-content.active {
        display: block;
    }

    .dropdown-item {
        padding: 10px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #333;
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }

    .dropdown-item:hover {
        background: #3b4d3f;
    }

    .dropdown-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #4a9d6f;
    }

    .dropdown-item label {
        cursor: pointer;
        margin: 0;
        flex: 1;
        color: #f5f3e7;
    }
</style>

<div class="overlay" id="overlay"></div>
<div class="add-habit-form" id="addHabitForm">
    <div class="form-content">
        <form id="habitForm">
            <!-- Habit Name -->
            <div class="form-section">
                <div class="section-icon">❓</div>
                <div class="section-controls" style="flex: 1;">
                    <input type="text" id="habitName" class="form-input" placeholder="Enter Habit Name"
                        style="flex: 1;">
                </div>
            </div>

            <!-- Repeat -->
            <div class="form-section">
                <div class="section-icon">🔄</div>
                <div class="section-label">Repeat</div>
                <div class="section-controls">
                    <select class="form-select" id="frequencySelect" style="border: 2px solid #4a90e2;">
                        <option>Daily</option>
                        <option>Weekly</option>
                        <option>Monthly</option>
                    </select>
                    <div class="custom-dropdown" id="daysDropdown" style="display: none; flex: 1;">
                        <button type="button" class="dropdown-button" id="daysButton">Every Day ▼</button>
                        <div class="dropdown-content" id="daysContent">
                            <div class="dropdown-item">
                                <input type="checkbox" id="everyDay" checked>
                                <label for="everyDay">Every Day</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="monday" checked>
                                <label for="monday">Monday</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="tuesday" checked>
                                <label for="tuesday">Tuesday</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="wednesday" checked>
                                <label for="wednesday">Wednesday</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="thursday" checked>
                                <label for="thursday">Thursday</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="friday" checked>
                                <label for="friday">Friday</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="saturday" checked>
                                <label for="saturday">Saturday</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="sunday" checked>
                                <label for="sunday">Sunday</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goal -->
            <div class="form-section">
                <div class="section-icon">🎯</div>
                <div class="section-label">Goal</div>
                <div class="section-controls">
                    <input type="number" class="form-input form-input-number" value="1" min="1">
                    <select class="form-select">
                        <option>times</option>
                        <option>liters</option>
                        <option>kilometers</option>
                        <option>calories</option>
                        <option>kilograms</option>
                        <option>minutes</option>
                        <option>hours</option>
                    </select>
                    <select class="form-select">
                        <option>per day</option>
                        <option>per week</option>
                        <option>per month</option>
                    </select>
                </div>
            </div>

            <!-- Time of Day -->
            <div class="form-section">
                <div class="section-icon">☀️</div>
                <div class="section-label">Time of Day</div>
                <div class="section-controls">
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="morning" checked>
                            <label for="morning">Morning</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="afternoon" checked>
                            <label for="afternoon">Afternoon</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="evening" checked>
                            <label for="evening">Evening</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Start Date -->
            <div class="form-section">
                <div class="section-icon">📅</div>
                <div class="section-label">Start Date</div>
                <div class="section-controls">
                    <input type="date" id="startDate" class="date-input">
                </div>
            </div>

            <!-- End Condition -->
            <div class="form-section">
                <div class="section-icon">⏹️</div>
                <div class="section-label">End Condition</div>
                <div class="section-controls">
                    <select class="form-select">
                        <option>Never</option>
                        <option>Specific Date</option>
                        <option>After X Days</option>
                    </select>
                </div>
            </div>

            <!-- Reminders -->
            <div class="form-section">
                <div class="section-icon">🔔</div>
                <div class="section-label">Reminders</div>
                <div class="section-controls" style="flex-direction: column; align-items: flex-start;">
                    <div class="reminder-pill">
                        <span>09:00</span>
                        <button type="button">✕</button>
                    </div>
                    <button type="button" class="add-button">+ Add New Reminder</button>
                </div>
            </div>

            <!-- Area -->
            <div class="form-section">
                <div class="section-icon">📁</div>
                <div class="section-label">Area</div>
                <div class="section-controls">
                    <select class="form-select">
                        <option>Select areas</option>
                        <option>Health</option>
                        <option>Work</option>
                        <option>Personal</option>
                    </select>
                </div>
            </div>

            <!-- Checklist -->
            <div class="form-section">
                <div class="section-icon">✓</div>
                <div class="section-label">Checklist</div>
                <div class="section-controls">
                    <button type="button" class="add-button">+ Add New Checklist</button>
                </div>
            </div>
        </form>
    </div>
    <div class="form-footer">
        <button type="button" class="form-button btn-cancel" id="cancelBtn">Cancel</button>
        <button type="submit" form="habitForm" class="form-button btn-save">Save</button>
    </div>
</div>

<script>
    // Custom days dropdown functionality
    const frequencySelect = document.getElementById('frequencySelect');
    const daysDropdown = document.getElementById('daysDropdown');
    const daysButton = document.getElementById('daysButton');
    const daysContent = document.getElementById('daysContent');
    const dayCheckboxes = daysContent.querySelectorAll('input[type="checkbox"]');

    // Show/hide days dropdown based on frequency selection
    frequencySelect.addEventListener('change', function() {
        if (this.value === 'Weekly') {
            daysDropdown.style.display = 'block';
        } else {
            daysDropdown.style.display = 'none';
        }
    });

    // Toggle dropdown on button click
    daysButton.addEventListener('click', function(e) {
        e.preventDefault();
        daysContent.classList.toggle('active');
        daysButton.classList.toggle('active');
    });

    // Update dropdown button text based on selected days
    function updateDaysButtonText() {
        const checkedDays = Array.from(dayCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.id)
            .filter(id => id !== 'everyDay');

        if (document.getElementById('everyDay').checked) {
            daysButton.textContent = 'Every Day ▼';
        } else if (checkedDays.length === 0) {
            daysButton.textContent = 'Select days ▼';
        } else if (checkedDays.length <= 2) {
            daysButton.textContent = checkedDays.map(id => {
                const labels = {
                    'monday': 'Mon',
                    'tuesday': 'Tue',
                    'wednesday': 'Wed',
                    'thursday': 'Thu',
                    'friday': 'Fri',
                    'saturday': 'Sat',
                    'sunday': 'Sun'
                };
                return labels[id] || id;
            }).join(', ') + ' ▼';
        } else {
            daysButton.textContent = checkedDays.length + ' days ▼';
        }
    }

    // Handle checkbox changes
    dayCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateDaysButtonText);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!daysDropdown.contains(e.target)) {
            daysContent.classList.remove('active');
            daysButton.classList.remove('active');
        }
    });

    // Initialize button text
    updateDaysButtonText();
</script>