
<div class="overlay" id="overlay"></div>
<div class="add-habit-form" id="addHabitForm">
    <div class="form-header">
        <div id="formTitle" class="form-header-title">Nova navada</div>
    </div>
    <div class="form-content">
        <form id="habitForm" action="logika/shrani_navado.php" method="POST">
            <input type="hidden" id="editHabitId" name="id_navade" value="">

            <div class="form-section">
                <div class="section-icon">❓</div>
                <div class="section-controls" style="flex: 1;">
                    <input type="text" id="habitName" name="ime_navade" class="form-input"
                        placeholder="Vnesite ime navade" required style="flex: 1;">
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">🔄</div>
                <div class="section-label">Ponavljanje</div>
                <div class="section-controls">
                    <select class="form-select" id="frequencySelect" name="ponavljanje"
                        style="border: 2px solid #4a90e2;">
                        <option value="dnevno">Dnevno</option>
                        <option value="tedensko">Tedensko</option>
                        <option value="mesecno">Mesečno</option>
                    </select>

                    <div class="custom-dropdown" id="daysDropdown" style="display: none; flex: 1;">
                        <button type="button" class="dropdown-button" id="daysButton">Vsak dan ▼</button>
                        <div class="dropdown-content" id="daysContent">
                            <div class="dropdown-item">
                                <input type="checkbox" id="monday" name="dnevi[]" value="ponedeljek" checked>
                                <label for="monday">Ponedeljek</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="tuesday" name="dnevi[]" value="torek" checked>
                                <label for="tuesday">Torek</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="wednesday" name="dnevi[]" value="sreda" checked>
                                <label for="wednesday">Sreda</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="thursday" name="dnevi[]" value="cetrtek" checked>
                                <label for="thursday">Četrtek</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="friday" name="dnevi[]" value="petek" checked>
                                <label for="friday">Petek</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="saturday" name="dnevi[]" value="sobota" checked>
                                <label for="saturday">Sobota</label>
                            </div>
                            <div class="dropdown-item">
                                <input type="checkbox" id="sunday" name="dnevi[]" value="nedelja" checked>
                                <label for="sunday">Nedelja</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">🎯</div>
                <div class="section-label">Cilj</div>
                <div class="section-controls">
                    <input type="number" name="cilj_kolicina" class="form-input form-input-number" value="1" min="1">
                    <select name="cilj_enota" class="form-select">
                        <option value="krat">krat</option>
                        <option value="litrov">litrov</option>
                        <option value="kilometrov">kilometrov</option>
                        <option value="minut">minut</option>
                    </select>
                    <select name="cilj_obdobje" class="form-select">
                        <option value="na_dan">na dan</option>
                        <option value="na_teden">na teden</option>
                        <option value="na_mesec">na mesec</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">🏁</div>
                <div class="section-label">Streak cilj</div>
                <div class="section-controls">
                    <input type="number" id="ciljDniInput" name="cilj_dni" class="form-input form-input-number" min="1" placeholder="npr. 30">
                    <span style="color:#aaa; font-size:13px;">dni (neobvezno)</span>
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">☀️</div>
                <div class="section-label">Del dneva</div>
                <div class="section-controls">
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="morning" name="del_dneva[]" value="zjutraj" checked>
                            <label for="morning">Zjutraj</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="afternoon" name="del_dneva[]" value="popoldne" checked>
                            <label for="afternoon">Popoldne</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="evening" name="del_dneva[]" value="zvecer" checked>
                            <label for="evening">Zvečer</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">📁</div>
                <div class="section-label">Kategorija</div>
                <div class="section-controls">
                    <select name="kategorija" id="habitKategorijaSelect" class="form-select">
                        <?php if (!empty($kategorijeList)): ?>
                            <?php foreach ($kategorijeList as $kat): ?>
                                <option value="<?php echo htmlspecialchars($kat['ime']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($kat['ime'])); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="zdravje">Zdravje</option>
                            <option value="delo">Delo</option>
                            <option value="osebno">Osebno</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">📄</div>
                <div class="section-label">Opis</div>
                <div class="section-controls" style="flex: 1;">
                    <input type="text" id="habitDescription" name="opis" class="form-input"
                        placeholder="Vnesite opis navade" style="flex: 1;">
                </div>
            </div>

            <div class="form-footer">
                <button type="button" class="form-button btn-cancel" id="cancelBtn">Prekliči</button>
                <button type="submit" class="form-button btn-save">Shrani</button>
            </div>

        </form>
    </div>
</div>
<script>
    // ---------------------------------------------
    // LOGIKA ZA DROPDOWN DNEVOV
    // ---------------------------------------------
    const frequencySelect = document.getElementById('frequencySelect');
    const daysDropdown = document.getElementById('daysDropdown');
    const daysButton = document.getElementById('daysButton');
    const daysContent = document.getElementById('daysContent');
    const dayCheckboxes = daysContent.querySelectorAll('input[type="checkbox"]');

    frequencySelect.addEventListener('change', function () {
        if (this.value === 'tedensko') {
            daysDropdown.style.display = 'block';
        } else {
            daysDropdown.style.display = 'none';
        }
    });

    daysButton.addEventListener('click', function (e) {
        e.preventDefault();
        daysContent.classList.toggle('active');
        daysButton.classList.toggle('active');
    });

    function updateDaysButtonText() {
        const checkedDays = Array.from(dayCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.id);

        if (checkedDays.length === 7) {
            daysButton.textContent = 'Vsak dan ▼';
        } else if (checkedDays.length === 0) {
            daysButton.textContent = 'Izberi dneve ▼';
        } else if (checkedDays.length <= 2) {
            daysButton.textContent = checkedDays.map(id => {
                const labels = {
                    'monday': 'Pon',
                    'tuesday': 'Tor',
                    'wednesday': 'Sre',
                    'thursday': 'Čet',
                    'friday': 'Pet',
                    'saturday': 'Sob',
                    'sunday': 'Ned'
                };
                return labels[id] || id;
            }).join(', ') + ' ▼';
        } else {
            daysButton.textContent = checkedDays.length + ' dni ▼';
        }
    }

    dayCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateDaysButtonText);
    });

    document.addEventListener('click', function (e) {
        if (!daysDropdown.contains(e.target)) {
            daysContent.classList.remove('active');
            daysButton.classList.remove('active');
        }
    });

    updateDaysButtonText();

</script>