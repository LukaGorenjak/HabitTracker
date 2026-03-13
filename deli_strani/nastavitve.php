<div class="add-habit-form" id="nastavitveModal">
    <div class="form-header">
        <div class="form-header-title">Nastavitve profila</div>
        <button type="button" class="close-btn" id="cancelNastavitveBtn">&times;</button>
    </div>
    <div class="form-content">
        <form id="nastavitveFormEl" enctype="multipart/form-data">

            <!-- Profile picture -->
            <div class="profil-slika-section">
                <div class="profil-slika-wrap">
                    <img id="profilPreview" src="" alt="Profil" class="profil-preview-img">
                    <label for="profilnaSlika" class="profil-slika-overlay">&#128247;</label>
                    <input type="file" id="profilnaSlika" name="profilna_slika" accept="image/*">
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">👤</div>
                <div class="section-controls" style="flex:1;">
                    <input type="text" id="nastavitveIme" name="ime" class="form-input"
                        placeholder="Uporabniško ime" required>
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">✉️</div>
                <div class="section-controls" style="flex:1;">
                    <input type="email" id="nastavitveEmail" name="email" class="form-input"
                        placeholder="E-pošta" required>
                </div>
            </div>

            <div class="form-section-divider">Sprememba gesla <span>(neobvezno)</span></div>

            <div class="form-section">
                <div class="section-icon">🔒</div>
                <div class="section-controls" style="flex:1;">
                    <input type="password" id="trenutnoGeslo" name="trenutno_geslo" class="form-input"
                        placeholder="Trenutno geslo">
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">🔑</div>
                <div class="section-controls" style="flex:1;">
                    <input type="password" id="novoGeslo" name="novo_geslo" class="form-input"
                        placeholder="Novo geslo (min. 6 znakov)">
                </div>
            </div>

            <div class="form-section">
                <div class="section-icon">🔑</div>
                <div class="section-controls" style="flex:1;">
                    <input type="password" id="potrdiGeslo" name="potrdi_geslo" class="form-input"
                        placeholder="Potrdi novo geslo">
                </div>
            </div>

            <div class="nastavitve-msg" id="nastavitveError" style="display:none;"></div>
            <div class="nastavitve-msg nastavitve-success" id="nastavitveSuccess" style="display:none;"></div>

            <div class="form-section" style="justify-content:flex-end; gap:10px; border:none; padding-top:8px;">
                <button type="button" class="btn-cancel" id="cancelNastavitveBtn2">Prekliči</button>
                <button type="submit" class="btn-save">Shrani</button>
            </div>

        </form>
    </div>
</div>
